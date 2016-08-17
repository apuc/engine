<?php
namespace shop_posts_admin;
use module,db,url;
#подключаем функции
require_once(__DIR__.'/func.php');
#Используем сторонние модули
require_once(__DIR__.'/../../category/handler.php');
require_once(__DIR__.'/../handler.php');
require_once(module::$path.'/user/handler.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array('easy'=>1),1)->handler;
		$this->user=$this->uhandler->user;
	}
	/*
		1. данные для вывода формы редактирования поста
	*/
	function edit($id,$cat){
		$post=new \StdClass;
		#получаем данные поста, если есть ID
		if($id){
			$post=db::qfetch(
				"SELECT post.*,GROUP_CONCAT(rel.cid) AS catConcat
				FROM `shop_post` post
					LEFT OUTER JOIN `".PREFIX_SPEC."category2shop_post` rel ON rel.`pid`=post.url
				WHERE post.`id`='$id' GROUP BY post.`id`");
			$cat=explode(',', $post->catConcat);
			#Проверяем разрешение на редактирование новости
			if(!$rbac=$this->uhandler->rbac('editNews')){
				if($this->user->id==$post->user) $rbac=$this->uhandler->rbac('editNewsMy');
			}
		}else{
			#Проверяем разрешение на cоздание новости
			$rbac=$this->uhandler->rbac("addNews");
		}
		if(@!$rbac){
			$this->headers->location=HREF; return;
		}
		# получаем данные по категории
		$category=array();
		if(!empty($cat)){
			foreach ($cat as $val) {
				if(empty($cat)) continue;
				$sqlCats[]=db::escape($val);
			}
			if(!empty($sqlCats)){
				db::query("SELECT * FROM `shop_category` WHERE `url` IN('".implode("','",$sqlCats)."')");
				while ($d=db::fetch()) {
					$category[$d->url]=$d;
				}
			}
		}
		# получаем список используемых сайтов
		$siteList=array();
		db::query("SELECT `site` FROM `shop_post` WHERE site!='' GROUP BY `site`");
		while ($d=db::fetch()) {
			$siteList[]=$d->site;
		}

		return (object)array(
			'post'=>$post,
			'category'=>$category,
			'siteList'=>$siteList,
			'accessPostAdd'=>$this->uhandler->rbac('addNews'),
			'accessSaveNoText'=>$this->uhandler->rbac('saveNoTextPost'),
			'accessPublish'=>$this->uhandler->rbac('publishPost'),
			'accessSetUser'=>$this->uhandler->rbac('userSetPost'),
			'accessSetSite'=>$accessSetSite=$this->uhandler->rbac('setSite'),
			'accessChooseSite'=>$accessSetSite?false:$this->uhandler->rbac('chooseSite'),
			'userList'=>getAuthors($this->uhandler->rbacByUT('addNews')),
			'imagesForm'=>module::exec('images/admin',array('act'=>'edit','pid'=>$id,'tbl'=>'shop_post'),1)->str,
			'accessPinpost'=>$this->uhandler->rbac('pinpost'),
			'accessEditKeyword'=>$this->uhandler->rbac('editKeyword'),
		);
	}
	/*
		1. сохраняет пост
		2. поключает форму редактирования поста
	*/
	function save($id,$title,$price,$text,$cats,$pincid,$sources,$foruser,$images,$site,$formAction,$keyword,$keywords){
		$post=new \StdClass;
		$error=$message='';
		$published='';
		# Получаем ID всех родительских категорий
		$cats=getCatsParent($cats);
		if($id){# post update
			$post=db::qfetch("SELECT * FROM `shop_post` WHERE id='{$id}' LIMIT 1");
			#Проверяем разрешение на редактирование
			$rbac=false;
			$published=$post->published;
			if(!$rbac=$this->uhandler->rbac('editNews')){
				if($this->user->id==$post->user && $published != 'published') 
					$rbac=$this->uhandler->rbac('editNewsMy');
			}
			if(!$rbac) return array('error'=>'forbidden');
			# refresh cats count
			$catsForRefresh=getCatsByPost($post->url);
		}else{# new post
			# refresh cats count
			$catsForRefresh=$cats;
		}
		if(!$foruser) $foruser=$this->user->id;
		if($status=calcStatus($foruser,$this->uhandler,$published,$formAction))
			$published=$status;
		# проверяем права
		if(!$this->uhandler->rbac('pinpost')){
			$pincid=-1;
		}

		$sv=new Sv();
		if($this->uhandler->rbac('saveNoTextPost'))
			$sv->requireFields=array('title','price');
		else{
			$sv->requireFields=array('title','txt','sources','price');
		}
		# определяем site поста
		if(is_array($site)){
			$site=($site[0]==''&&!empty($site[1]))?$site[1]:$site[0];
			if($site!=''&&!preg_match('!^https?://!is', $site)){
				$site='http://'.$site;
			}
		}
		# сохраняем
		$result=$sv->save($post,$title,$price,$text,$cats,$pincid,$sources,$site,($foruser=='none')?$this->user:$foruser,$published,$keyword,$keywords,$images);
		if(!$result)
			$error='UnknownError';
		else{
			if(!$id) $id=$result;
			$message='saved';
			# обновляем количество постов в затронутых категориях
			module::exec('shop/category',array('act'=>'updateCount','cats'=>$catsForRefresh,'tbl'=>'shop_post'),'data');
		}

		# записываем картинки
		if($id&&!empty($images)){
			$moduleImagesData=module::exec('images/admin',array('act'=>'save','pid'=>$id,'title'=>$title,'tbl'=>'shop_post','images'=>$images),'data')->data;
			if(!empty($moduleImagesData->error)) $error=$moduleImagesData->error;
		}
		
		return (object)array(
			'html'=>module::exec('shop/posts/admin',array('act'=>'edit','pid'=>$id,),1)->str,
			'message'=>$message,
			'error'=>$error,
			'id'=>$id,
		);
	}
	/*
		1. удаляет пост
		2. подключает вывод списка постов
	*/
	function del($pid){
		# Проверяем наличие прав delNews
		if(!$this->uhandler->rbac('delNews')){
			if($this->uhandler->rbac('delMyUnpublishedNews')){
				#Проверяем разрешение на удаление для данного пользователя
				$user=false;
				list($user)=db::qrow("SELECT `user` FROM `shop_post` WHERE id='$pid' && `published`!='published'");
				$rbac=($this->user->id===$user);
			}else $rbac=0;
		}else
			$rbac=1;
		
		if(!$rbac){
			return array('error'=>1);
		}
		
		# refresh cats count
		list($url)=db::qrow("SELECT `url` FROM `shop_post` WHERE `id`='$pid' LIMIT 1");
		$cats=getCatsByPost($url);

		#Удаляем пост
		db::query("DELETE FROM `shop_post` WHERE id='$pid'");
		if(mysql_affected_rows()){
			$message=(object)array('type'=>'success','txt'=>'done');
		}
		# удаляем предыдущие записи о категориях
		db::query("DELETE FROM `".PREFIX_SPEC."category2shop_post` WHERE `pid`='{$url}'");

		# обновляем количество постов в затронутых категориях
		module::exec('category',array('act'=>'updateCount','cats'=>$cats,'tbl'=>'shop_post'),'data');

		# удаляем картинки
		module::exec('images/admin',array('act'=>'delByPid','pid'=>$pid,'tbl'=>'post'),1);

		return (object)array(
			'message'=>@$message,
		);
	}
	function dellPosts($ids){
		$this->template='';
		# Проверяем наличие прав delNews
		if(!$this->uhandler->rbac('delNews')){
			if($this->uhandler->rbac('delMyUnpublishedNews')){
				#Проверяем разрешение на удаление для данного пользователя
				$user=false;
				list($user)=db::qrow("SELECT `user` FROM `shop_post` WHERE id='$pid' && `published`!='published'");
				$rbac=($this->user->id===$user);
			}else $rbac=0;
		}else
			$rbac=1;
		if(!$rbac){
			return array('error'=>1);
		}
		foreach($ids as $i=>$id){
			$ids[$i]=(int)$id;
			if($ids[$i]<1)unset($ids[$i]);
		}
		
		# refresh cats count
		list($url)=db::qrow("SELECT `url` FROM `shop_post` WHERE `id` in ('".implode("','",$ids)."') LIMIT 1");
		$cats=getCatsByPost($url);

		#Удаляем пост
		db::query("DELETE FROM `shop_post` WHERE `id` in ('".implode("','",$ids)."')");
		if(mysql_affected_rows()){
			$message=(object)array('type'=>'success','txt'=>'done');
		}
		# удаляем предыдущие записи о категориях
		db::query("DELETE FROM `".PREFIX_SPEC."category2shop_post` WHERE `pid` in ('".implode("','",$ids)."')");

		# обновляем количество постов в затронутых категориях
		module::exec('category',array('act'=>'updateCount','cats'=>$cats,'tbl'=>'shop_post'),'data');

		# удаляем картинки
		foreach($ids as $pid){
			module::exec('images/admin',array('act'=>'delByPid','pid'=>$pid),1);
		}
		# удаляем связь с кейвордами
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2shop_post` WHERE `pid` in ('".implode("','",$ids)."')");

		return (object)array(
			'message'=>@$message,
		);

	}
	function install(){
		db::query("CREATE TABLE IF NOT EXISTS `shop_post` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`date` datetime NOT NULL,
			`url` varchar(125) NOT NULL,
			`title` varchar(255) NOT NULL,
			`txt` text NOT NULL,
			`sources` text NOT NULL,
			`user` int(11) NOT NULL,
			`published` enum('unpublished','published','readytopublish','researchdone','remakesearch','remakecontent','waitcopywrite','researchchecked','autoposting') NOT NULL,
			`site` varchar(255) NOT NULL,
			`statViews` int(11) NOT NULL,
			`statViewsShort` int(11) NOT NULL,
			`statShortFlag` date NOT NULL,
			`datePublish` datetime DEFAULT NULL,
			`countPhoto` int(11) NOT NULL,
			`FBpublished` enum('ban','done','proc') NOT NULL DEFAULT 'proc',
			`like` int(11) NOT NULL DEFAULT '0',
			`dislike` int(11) NOT NULL DEFAULT '0',
			`pincid` VARCHAR(255) NOT NULL DEFAULT '',
			`kid` int(11) NOT NULL,
			`price` FLOAT NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `url` (`url`),
			KEY `published` (`published`),
			FULLTEXT KEY `title_txt` (`title`,`txt`),
			FULLTEXT KEY `txt` (`txt`),
			KEY `kid` (`kid`),
			KEY `price` (`price`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8",1);

		return;
	}
}
/*
 * Класс сохранения новости
 * requireFields - поля без которых нельзя сохранить новость
 * error - last error
 */
class Sv{
	public $requireFields;
	public $error;
	function __construct(){
		$this->error=false;
	}
	#Сохраняем новую новость или редактируем существующую
	function save($post,$title,$price,$text,$cats,$pincid,$sources,$site,$foruser,$published,$keyword,$keywords,$images){
		$id=isset($post->id)?$post->id:false;
		#Удаляем теги из текста
		$title=strip_tags($title);
		$text=stripText($text,HREF);
			
		# все поля которые надо записать, кроме категорий
		$set=array(
			'title'=>db::escape(stripcslashes(html_entity_decode($title))),
			'price'=>(int)$price,
			'txt'=>db::escape($text),
			'sources'=>$sources,
			'published'=>$published,
			'user'=>$foruser,
			'site'=>db::escape($site),
			'pincid'=>($pincid!==-1)?db::escape($pincid):'',
		);
		# обязательные поля
		foreach($this->requireFields as $rf){
			if(empty($set[$rf])) {$this->error="Require: {$rf}"; return false;}
			else{
				$sqlSet[]="`{$rf}`='{$set[$rf]}'";
				unset($set[$rf]);
			}
		}
		# поля не требующие проверки
		foreach ($set as $key => $val) {
			$sqlSet[]="`{$key}`='{$val}'";
		}
		if($published=='published') $sqlSet[]="`datePublish`=NOW()";
		elseif($published=='autoposting'){
			list($maxdate)=db::qrow("SELECT max(datePublish) from `shop_post`");
			list($per_day)=$qq=db::qrow("SELECT `value` FROM `".PREFIX_SPEC."config` where `key`='autopost_per_day'");
			if(empty($per_day))$per_day=1;
			$sqlSet[]="`datePublish`='".getDatePublish($per_day,strtotime($maxdate))."'";
		}

		$tblRel=PREFIX_SPEC."category2shop_post";
		if($id){
			db::query("UPDATE `shop_post` SET ".implode(',',$sqlSet)." WHERE `id`='$id' LIMIT 1");
			# удаляем предыдущие записи о категориях
			list($url)=db::qrow("SELECT `url` FROM `shop_post` WHERE `id`='$id' LIMIT 1");
			db::query("DELETE FROM `{$tblRel}` WHERE `pid`='{$url}'");
		}else{
			$url=getUrl($title);
			$sqlSet[]="`url`='{$url}'";
			db::query("INSERT INTO `shop_post` SET ".implode(',',$sqlSet).", `date`=NOW()");
			$id=db::insert();
			$post->id=$id;
		}
		# записываем новые категории
		if(!empty($cats)&&!empty($id)){
			foreach ($cats as $val) {
				$sqlVal[]="('{$val}','{$url}')";
			}
			db::query("INSERT IGNORE INTO `{$tblRel}` (`cid`,`pid`) VALUES".implode(',',$sqlVal));
		}
		return $id;
	}
}
