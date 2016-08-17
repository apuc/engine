<?php
namespace posts_admin;
use module,db,url;
#подключаем функции
require_once(module::$path.'/posts/admin/func.php');
#Используем сторонние модули
require_once(module::$path.'/category/handler.php');
require_once(module::$path.'/posts/handler.php');
require_once(module::$path.'/user/handler.php');
require_once(module::$path.'/admin/themes/handler.php');

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
	function edit($id,$prfxtbl,$cat){
		$tbls=\posts\tables::init($prfxtbl);
		$post=new \StdClass;
		#получаем данные поста, если есть ID
		if($id){
			$post=db::qfetch(
				"SELECT post.*,GROUP_CONCAT(rel.cid) AS catConcat
				FROM `{$tbls->post}` post
					LEFT OUTER JOIN `".PREFIX_SPEC."{$tbls->category2post}` rel ON rel.`pid`=post.url
				WHERE post.`id`='$id' GROUP BY post.`id`");
			$cat=explode(',', $post->catConcat);
			#Проверяем разрешение на редактирование новости
			if(!$rbac=$this->uhandler->rbac('editNews')){
				if($this->user->id==$post->user) $rbac=$this->uhandler->rbac('editNewsMy');
			}
			# получаем кейворды
			$post->keywords=getKeywords($post);
			$post->keyword=isset($post->keywords[$post->kid])?$post->keywords[$post->kid]:false;
			# преобразование в мнемоники для совместимости с tinyMCE
			$post->txt=htmlspecialchars($post->txt);
		}else{
			#Проверяем разрешение на cоздание новости
			$rbac=$this->uhandler->rbac("addNews");
		}
		if(@!$rbac){
			$this->headers->location=url::userLogin(); return;
		}
		# получаем данные по категории
		$category=array();
		if(!empty($cat)){
			foreach ($cat as $val) {
				if(empty($cat)) continue;
				$sqlCats[]=db::escape($val);
			}
			if(!empty($sqlCats)){
				db::query("SELECT * FROM `{$tbls->category}` WHERE `url` IN('".implode("','",$sqlCats)."')");
				while ($d=db::fetch()) {
					$category[$d->url]=$d;
				}
			}
		}
		# получаем список используемых сайтов
		$siteList=array();
		db::query("SELECT `site` FROM `{$tbls->post}` WHERE site!='' GROUP BY `site`");
		while ($d=db::fetch()) {
			$siteList[]=$d->site;
		}

		paramsPrettyPrint($post->data);

		return (object)array(
			'post'=>$post,
			'tbl'=>$tbls->post,
			'prfxtbl'=>$tbls->prfx,
			'category'=>$category,
			'siteList'=>$siteList,
			'themes'=>$this->uhandler->rbac('themesSet')?\admin_themes\themes():array(),
			'accessPostAdd'=>$this->uhandler->rbac('addNews'),
			'accessSaveNoText'=>$this->uhandler->rbac('saveNoTextPost'),
			'accessPublish'=>$this->uhandler->rbac('publishPost'),
			'accessSetUser'=>$this->uhandler->rbac('userSetPost'),
			'userList'=>getAuthors($this->uhandler->rbacByUT('addNews')),
			'imagesForm'=>module::exec('images/admin',array('act'=>'edit','pid'=>$id,'tbl'=>$tbls->post,'key'=>@$post->keyword->title),1)->str,
			'access'=>$this->uhandler->access(),
			'accessPinpost'=>$this->uhandler->rbac('pinpost'),
			'accessEditKeyword'=>$this->uhandler->rbac('editKeyword'),
		);
	}
	/*
		1. сохраняет пост
		2. поключает форму редактирования поста
	*/
	function save($id,$prfxtbl,$title,$text,$params,$cats,$pincid,$sources,$foruser,$images,$site,$formAction,$keyword,$keywords,$new_keywords,$theme,$editorlinks){
		$tbls=\posts\tables::init($prfxtbl);
		$post=new \StdClass;
		$error=$message='';
		$published='';
		$access=$this->uhandler->access();
		# Получаем ID всех родительских категорий
		$cats=getCatsParent($cats);
		if($id){# post update
			$post=db::qfetch("SELECT * FROM `{$tbls->post}` WHERE id='{$id}' LIMIT 1");
			#Проверяем разрешение на редактирование
			$rbac=false;
			$published=$post->published;
			if(!$rbac=$access->editNews){
				if($this->user->id==$post->user && $published != 'published') 
					$rbac=$access->editNewsMy;
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
		if(!@$access->pinpost){
			$pincid=-1;
		}
		# права для назначения темы поста
		if(!@$access->themesSet)
			$theme='';

		$sv=new Sv();
		$sv->stripText($title,$text,$editorlinks,$access);
		$sv->checkParams($params);
		if($params===false)
			$error='ParseParamsFail';

		if(@$access->saveNoTextPost)
			$sv->requireFields=array('title');
		else{
			$sv->requireFields=array('title','txt','sources');
		}
		# определяем site поста
		if(is_array($site)){
			$site=($site[0]==''&&!empty($site[1]))?$site[1]:$site[0];
			if($site!=''&&!preg_match('!^https?://!is', $site)){
				$site='http://'.$site;
			}
		}
		# сохраняем
		$result=$sv->save(
			$tbls->post,
			$post,
			$title,
			$text,
			$params,
			$cats,
			$pincid,
			$sources,
			$site,
			($foruser=='none')?$this->user:$foruser,
			$published,
			$keyword,
			$keywords,
			prepareKeywordsFromText($new_keywords),
			$images,
			$theme
		);
		if(!$result)
			$error='UnknownError';
		else{
			if(!$id) $id=$result;
			$message='saved';
			# обновляем количество постов в затронутых категориях
			module::exec('category',array('act'=>'updateCount','cats'=>$catsForRefresh),'data');
		}
		# сохраняем изменения в историю
		saveInHistory($id, $this->uhandler->user->id);

		# записываем картинки
		if($id&&!empty($images)){
			$moduleImagesData=module::exec('images/admin',array('act'=>'save','pid'=>$id,'title'=>$title,'tbl'=>$tbls->post,'images'=>$images),'data')->data;
			if(!empty($moduleImagesData->error)) $error=$moduleImagesData->error;
			# сохраняем связь с кейвордами от картинок
			saveRelKeyword($moduleImagesData->kids,$id);
		}
		
		return (object)array(
			'html'=>module::exec('posts/admin',array('act'=>'edit','pid'=>$id),1)->str,
			'message'=>$message,
			'error'=>$error,
			'id'=>$id,
		);
	}
	/*
		Загрузка картинок к посту с локали
		- запрос приходит из JS
	*/
	function saveImagesMultiUpload($id,$tbl,$title,$images){
		if(!$id||empty($images)) return;
		$moduleImagesData=module::exec('images/admin',array('act'=>'save','pid'=>$id,'title'=>$title,'tbl'=>$tbl,'images'=>$images),'data')->data;
		# сохраняем связь с кейвордами от картинок
		saveRelKeyword($moduleImagesData->kids,$id);
		if(!empty($moduleImagesData->error)) 
			echo $moduleImagesData->error;
		else echo 'done';
		die;
	}
	/*
		1. удаляет пост
		2. подключает вывод списка постов
	*/
	function del($pid,$prfxtbl){
		$tbls=\posts\tables::init($prfxtbl);
		# Проверяем наличие прав delNews
		if(!$this->uhandler->rbac('delNews')){
			if($this->uhandler->rbac('delMyUnpublishedNews')){
				#Проверяем разрешение на удаление для данного пользователя
				$user=false;
				list($user)=db::qrow("SELECT `user` FROM `{$tbls->post}` WHERE id='$pid' && `published`!='published'");
				$rbac=($this->user->id===$user);
			}else $rbac=0;
		}else
			$rbac=1;
		
		if(!$rbac){
			return array('error'=>1);
		}
		
		# refresh cats count
		list($url)=db::qrow("SELECT `url` FROM `{$tbls->post}` WHERE `id`='$pid' LIMIT 1");
		$cats=getCatsByPost($url);

		#Удаляем пост
		db::query("DELETE FROM `{$tbls->post}` WHERE id='$pid'");
		if(db::affected()){
			$message=(object)array('type'=>'success','txt'=>'done');
		}
		# удаляем предыдущие записи о категориях
		db::query("DELETE FROM `".PREFIX_SPEC."{$tbls->category2post}` WHERE `pid`='{$url}'");

		# обновляем количество постов в затронутых категориях
		module::exec('category',array('act'=>'updateCount','cats'=>$cats,'tbl'=>$tbls->post),'data');

		# удаляем картинки
		module::exec('images/admin',array('act'=>'delByPid','pid'=>$pid,'tbl'=>'{$tbls->post}'),1);

		# удаляем связь с кейвордами
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2post` WHERE `pid`='{$pid}' && `tbl`='{$tbls->post}'");

		return (object)array(
			'message'=>@$message,
		);
	}
	/*
		массовое удаление постов
	*/
	function delPosts($ids,$prfxtbl){
		# Проверяем наличие прав delNews
		if(!$this->uhandler->rbac('delNews')){
			echo 'forbidden'; return;
		}
		if(empty($ids)) return;
		$tbls=\posts\tables::init($prfxtbl);
		$this->template='';
		foreach($ids as $i=>$id){
			$ids[$i]=(int)$id;
			if($ids[$i]<1)unset($ids[$i]);
		}
		
		# refresh cats count
		list($url)=db::qrow("SELECT `url` FROM `{$tbls->post}` WHERE `id` in ('".implode("','",$ids)."') LIMIT 1");
		$cats=getCatsByPost($url);

		#Удаляем пост
		db::query("DELETE FROM `{$tbls->post}` WHERE `id` IN ('".implode("','",$ids)."')");
		if(db::affected()){
			echo 'done';
		}
		# удаляем предыдущие записи о категориях
		db::query("DELETE FROM `".PREFIX_SPEC."{$tbls->category2post}` WHERE `pid` in ('".implode("','",$ids)."')");

		# обновляем количество постов в затронутых категориях
		module::exec('category',array('act'=>'updateCount','cats'=>$cats,'tbl'=>$tbls->post),'data');

		# удаляем картинки
		foreach($ids as $pid){
			module::exec('images/admin',array('act'=>'delByPid','pid'=>$pid,'tbl'=>$tbls->post),1);
		}
		# удаляем связь с кейвордами
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2post` WHERE `pid` IN ('".implode("','",$ids)."') && `tbl`='{$tbls->post}'");
	}
	function install(){
		$tbl=\posts\tables::init();
		db::query("CREATE TABLE IF NOT EXISTS `{$tbl->post}` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`date` datetime NOT NULL,
			`datePublish` datetime DEFAULT NULL,
			`url` varchar(125) NOT NULL,
			`title` varchar(255) NOT NULL,
			`txt` TEXT NOT NULL,
			`data` TEXT NOT NULL,
			`sources` text NOT NULL,
			`user` int(11) NOT NULL,
			`published` enum('unpublished','published','readytopublish','researchdone','remakesearch','remakecontent','waitcopywrite','researchchecked','autoposting') NOT NULL,
			`site` varchar(255) NOT NULL,
			`statViews` int(11) NOT NULL,
			`statViewsShort` int(11) NOT NULL,
			`statShortFlag` date NOT NULL,
			`countPhoto` int(11) NOT NULL,
			`FBpublished` enum('ban','done','proc') NOT NULL DEFAULT 'proc',
			`like` int(11) NOT NULL DEFAULT '0',
			`dislike` int(11) NOT NULL DEFAULT '0',
			`pincid` VARCHAR(255) NOT NULL DEFAULT '',
			`kid` int(11) NOT NULL,
			`theme` VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `url` (`url`),
			KEY `published` (`published`),
			FULLTEXT KEY `title_txt` (`title`,`txt`),
			FULLTEXT KEY `txt` (`txt`),
			KEY `kid` (`kid`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",1);

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."{$tbl->postHistory}` (
			`indexId` int(11) NOT NULL AUTO_INCREMENT,
			`id` int(11) NOT NULL,
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
			PRIMARY KEY (`indexId`),
			FULLTEXT KEY `txt` (`txt`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",1);

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."user2vote` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`userID` int(10) unsigned NOT NULL,
			`postID` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `vote` (`userID`,`postID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;",1);

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."keyword2post` (
			`kid` int(11) unsigned NOT NULL,
			`pid` int(11) unsigned NOT NULL,
			`tbl` varchar(255) NOT NULL, 
			UNIQUE KEY `kid2pid` (`kid`,`pid`,`tbl`),
			KEY `pid` (`pid`,`tbl`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		",1);

		db::query("CREATE TABLE IF NOT EXISTS `keyword` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `title` (`title`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		",1);
		return array('instaled');
	}
	/*
		1. список статей по пользователю
	*/
	function listByUser($page,$num,$user,$type){
		#Редиректим если нет прав на просмотр этой страницы
		if(!$this->uhandler->rbac(array('searcherList','authorList','editorList'))){
			$this->headers->location=HREF; return;
		}
		$tbls=new \posts\tables;
		#Данные пользователя
		$userData=db::qfetch("SELECT * FROM `".PREFIX_SPEC."users` WHERE `id`='{$user}' LIMIT 1");
		if(!empty($userData))
			$userData->longName=$userData->mail.($userData->name!=''?"&nbsp;({$userData->name})":'');

		$authorList=$this->uhandler->rbac('authorList');
		$searcherList=$this->uhandler->rbac('searcherList');
		# для админа и редактора
		if($editorList=$this->uhandler->rbac('editorList')){
			$sqlWhere='';
			if(!$type) $type='text';
			if($type=='text')
				$sqlWhere=" && `txt`!=''";
			elseif($type=='notext')
				$sqlWhere=" && `txt`=''";
			elseif($type=='pub_no_without_user')
				$sqlWhere=" && published='unpublished' && user = '0'";	
			elseif($type=='unpublished')
				$sqlWhere=" && published='unpublished'";
			elseif($type=='autoposting')
				$sqlWhere=" && published='autoposting'";
			elseif($type=='researchdone')
				$sqlWhere=" && published='researchdone'";
			elseif($type=='researchchecked')
				$sqlWhere=" && published='researchchecked'";
			elseif($type=='waitcopywrite')
				$sqlWhere=" && published='waitcopywrite'";
			elseif($type=='remakesearch')
				$sqlWhere=" && published='remakesearch'";
			elseif($type=='remakecontent')
				$sqlWhere=" && published='remakecontent'";
			elseif($type=='readytopublish')
				$sqlWhere=" && published='readytopublish'";
			elseif($type=='published')
				$sqlWhere=" && published='published'";
			elseif($type=='free_posts')
				$sqlWhere=" && `published` = 'published' && site = ''";
			$sqlWhere.=$sqlWhereCount=$user!==false?" && `user`='{$user}'":'';
		}
		# для серчера
		elseif($searcherList){
			if(!$type) $type='remake';
			if($type=='todo')
				$sqlWhere=" && published='waitcopywrite'";
			elseif($type=='remake')
				$sqlWhere=" && published='remakesearch'";
			elseif($type=='moderate')
				$sqlWhere=" && published='researchdone'";
			$sqlWhere.=$sqlWhereCount="&& user={$this->user->id}";
		}
		# для автора
		elseif($authorList){
			if(!$type) $type='todo';
			if($type=='todo')
				$sqlWhere=" && published='waitcopywrite'";
			elseif($type=='remake')
				$sqlWhere=" && published='remakecontent'";
			elseif($type=='moderate')
				$sqlWhere=" && published='readytopublish'";
			$sqlWhere.=$sqlWhereCount="&& user={$this->user->id}";
		}
		
		$q="SELECT * FROM `{$tbls->post}` post WHERE 1 ".$sqlWhere;
		
		$history = PREFIX_SPEC."postHistory";
		// если тип - история, то выборка из другой таблицы
		if ($type=='history') {
			$q="SELECT * FROM `{$history}` WHERE 1 ".$sqlWhereCount;	
			list($count)=db::qrow(str_replace(" * "," COUNT(*) ",$q));
			$start=($page-1)*$num;
			db::query("
				SELECT post.*, user.name AS authorName, user.mail AS authorMail, IF(COUNT(post.id)>1,1,0) AS has_diff
				FROM ({$q}) post
				/* присоединяем данные пользователя */
				LEFT JOIN `".PREFIX_SPEC."users` user ON user.id=post.user 
				/* присоединяем таблицу постов */
				LEFT JOIN `{$tbls->post}` p ON post.id=p.id 
				/* уникальность по id и user */
				GROUP BY post.id
				ORDER BY post.`datePublish` DESC,post.`date` DESC LIMIT $start,$num
			");
		}else{
			list($count)=db::qrow(str_replace(" * "," COUNT(*) ",$q));
			$start=($page-1)*$num;
			db::query("
				SELECT post.*, user.name AS authorName, user.mail AS authorMail
				FROM ({$q}) post
				/* присоединяем данные пользователя */
				LEFT JOIN `".PREFIX_SPEC."users` user ON user.id=post.user
				GROUP BY post.id
				ORDER BY post.`datePublish` DESC,post.`date` DESC LIMIT $start,$num
			");
		}
		
		$posts=array();
		while($d=db::fetch()){
			$qNewsIds[]=$d->id;
			$d->txt=\posts\cutText(strip_tags($d->txt));
			$d->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			$d->authorName=\posts\setAuthorName($d);
			$posts[$d->id]=$d;
		}

		return array(
			'posts'=>$posts,
			'page'=>$page,
			'num'=>$num,
			'count'=>$count,
			'userData'=>@$userData,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::post_byUser(@$userData->id,$type,'%d'),
					true,
				)
			,1)->str,
			'usersList'=>getAuthors($this->uhandler->rbacByUT('addNews')),
			'authors'=>getAuthors(array(2)),
			'postsCounter'=>countPosts($sqlWhereCount,$tbls->post,$this->uhandler),
			'type'=>$type,
			'accessPublished'=>$this->uhandler->rbac('publishPost'),
			'accessHistory'=>$this->uhandler->rbac('viewHistory'),
			'accessAutoPosting'=>$this->uhandler->rbac('autoPosting'),
			'editorList'=>$editorList,
			'authorList'=>$authorList,
			'searcherList'=>$searcherList,
		);
	}
	/*
		Изменение статуса публикации поста
	*/
	function published_update($pid,$prfxtbl,$published){
		$tbls=\posts\tables::init($prfxtbl);
		$this->template='';
		if(!$pid) return;
		if(in_array($published, array('published','unpublished'))){
			db::query("UPDATE `{$tbls->post}` SET published='".$published."'".($published=='published'?',datePublish=NOW()':'')." 
				WHERE id = '".$pid."' LIMIT 1");
			# refresh cats count
			$catsForRefresh=getCatsByWhere("id='{$pid}'");
			# обновляем количество постов в затронутых категориях
			module::exec('category',array('act'=>'updateCount','cats'=>$catsForRefresh,'tbl'=>'{$tbls->post}'),'data');
		}
	}
	/*
		1. статистика по добавленным постам
	*/
	function stat($user,$start,$stop){
		if(!$this->uhandler->rbac('statPost')){
			$this->headers->location=HREF; 
			return;
		}
		if($user)
			$userData=db::qfetch("SELECT * FROM `".PREFIX_SPEC."users` WHERE `id`='{$user}' LIMIT 1");

		$sqlWhere=$user?" && `user`='{$user}'":'';
		$join=$user?"JOIN ".PREFIX_SPEC."users ON ".PREFIX_SPEC."users.id = post.user":'';
		#Получаем данные о постах по датам старта написания поста
		db::query("SELECT 
			COUNT(*) cn, 
			DATE_FORMAT(`date`, '%Y-%m-%d') AS `reformdate`,
			COUNT(IF(published IN('researchchecked','researchdone','remakesearch'), 1, NULL)) as search,
			COUNT(IF(published = 'waitcopywrite' OR published = 'remakecontent', 1, NULL)) as copywriting,
			COUNT(IF(published = 'readytopublish', 1, NULL)) as ready,
			COUNT(IF(published = 'published', 1, NULL)) as published
		FROM
			`post`
		JOIN ".PREFIX_SPEC."users ON ".PREFIX_SPEC."users.id = post.user
		WHERE
			`date` != '0000-00-00' {$sqlWhere}
				&& `date` >= '{$start}'
				&& `date` < '".date("Y-m-d",strtotime($stop)+24*3600)."'
				&& `txt` != ''
		GROUP BY `reformdate`
		ORDER BY `reformdate` DESC");
		$data=array();
		$total=(object)array('search'=>0,'copywriting'=>0,'ready'=>0,'published'=>0,'sums'=>0);
		while($d=db::fetch()){
			$d->sum = $d->search+$d->copywriting+$d->ready+$d->published;	
			$d->pubReal=0;
			$total->search+=$d->search;
			$total->copywriting+=$d->copywriting;
			$total->ready+=$d->ready;
			$total->published+=$d->published;
			$total->sums+=$d->sum;
			$data[$d->reformdate]=$d;
		}
		#Получаем Данные о дне реальной публикации
		db::query("SELECT 
			COUNT(*) cn, 
			DATE_FORMAT(`datePublish`, '%Y-%m-%d') AS `rdate`
		FROM `post` {$join}
		WHERE
			`datePublish` != '0000-00-00' {$sqlWhere}
				&& `datePublish` >= '{$start}'
				&& `datePublish` < '".date("Y-m-d",strtotime($stop)+24*3600)."'
		GROUP BY `rdate` ORDER BY `rdate` DESC");
		$fields=array('published','ready','copywriting','search','reformdate','sum');
		while($d=db::fetch()){
			if(empty($data[$d->rdate])){
				foreach($fields as $v){
					@$data[$d->rdate]->$v=0;
				}
				$data[$d->rdate]->reformdate=$d->rdate;
			}
			@$data[$d->rdate]->pubReal=$d->cn;
			$total->pubReal+=$d->cn;
		}
		$data=array_values($data);
		return array(
			'userData'=>@$userData,
			'stat'=>$data,
			'total'=>$total,
			'usersList'=>getAuthors($this->uhandler->rbacByUT('addNews')),
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'posts/admin',
						'act'=>'stat',
						'user'=>$user,
					)
				)
			,1)->str,
		);
	}
	
	/*
	 * Просмотр изменений в постах
	 */
	function diff($id, $from = 0, $to = 0) {
		if(!$this->uhandler->rbac('viewHistory')){
			$this->headers->location=HREF; 
			return;
		}
		
		$this->template = '';	
		$history = PREFIX_SPEC."postHistory";
		
		if($from && $to && $from != $to){
			$sqlWhere="h.`indexId` IN ({$from}, {$to})";
		}else{
			$sqlWhere="h.`id`='{$id}'";
		}
		db::query(
			"SELECT h.date, h.title, h.txt, u.name AS authorName, u.mail AS authorMail FROM `{$history}` h
				LEFT JOIN ".PREFIX_SPEC."users u ON u.id = h.user
			WHERE {$sqlWhere} ORDER BY h.indexId DESC LIMIT 2");	
				
		$h = array();
		while($row = db::fetch()) {
			$row->name=\posts\setAuthorName($row);
			$h[] = $row;
		}
		
		if(!$h) exit;
		
		# Библиотека PHP-FineDiff
		require_once(PATH.'modules/posts/admin/finediff.php');

		$opcodes=\FineDiff::getDiffOpcodes($h[1]->txt, $h[0]->txt);
		$diff=\FineDiff::renderDiffToHTMLFromOpcodes($h[1]->txt, $opcodes);
		$diff=html_entity_decode($diff);
		
		return array(
			'history'=>$h,
			'diff'=>$diff,
		);
	}
	
	/*
	 * Просмотр истории
	 */
	function archive($id) {
		if(!$this->uhandler->rbac('viewHistory')){
			$this->headers->location=HREF; 
			return;
		}
		
		$history = PREFIX_SPEC."postHistory";
		db::query(
			"SELECT indexId, h.date, h.title, h.txt, u.name AS authorName, u.mail AS authorMail FROM `{$history}` h
				LEFT JOIN ".PREFIX_SPEC."users u ON u.id = h.user
			WHERE h.`id`='{$id}' ORDER BY h.indexId DESC");
		
		$h = array();
		while($row = db::fetch()) {
			$row->name=\posts\setAuthorName($row);
			$h[] = $row;
		}
		
		return array(
			'history' => $h,
		);
	}
	
	/*
	 * Установка авторов для выбранных статей
	 */
	function setAuthors($ids, $author_id) {
		if (!empty($ids)) {
			db::query('UPDATE post SET user = '.$author_id.' WHERE id IN ('.implode(',', $ids).')');
		}
		$this->headers->location=url::post_byUser();
		return;
	}
	function imgRecount($pid,$tbl){
		if(!empty($tbl)){
			db::query("UPDATE `{$tbl}` SET `countPhoto`=(SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` 
			WHERE `pid`=`{$tbl}`.`id` AND `tbl`='{$tbl}') WHERE id='{$pid}' LIMIT 1");
		}
		return;
	}
	/*
		пересчитывает количество картинок у всех постов
	*/
	function imgRecountAll($tbl){
		if($this->user->rbac!=1) return;
		db::query("UPDATE `post` SET `countPhoto`=(SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` 
			WHERE `pid`=`post`.`id` AND `tbl`='post')");
		foreach (getPrefixList() as $prfx) {
			$tbl="{$prfx}_post";
			db::query("UPDATE `{$tbl}` SET `countPhoto`=(SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` 
				WHERE `pid`=`{$tbl}`.`id` AND `tbl`='{$tbl}')");
		}
		print "recounting images Done";
		die;
	}
	
	/**
	 * Вывод статистики по соц кнопкам
	 */
	function socStat($start,$stop) {
		if(!$this->uhandler->rbac('statPost')){
			$this->headers->location=HREF; 
			return;
		}
		db::query("SELECT * FROM ".PREFIX_SPEC."socStat WHERE `date`>='{$start}' && `date`<'".date("Y-m-d",strtotime($stop)+24*3600)."'	ORDER BY date DESC");
		$data=array();
		$total=array();
		while($d=db::fetch()){
			$data[$d->date][$d->buttonID]=$d;
			if(!isset($total[$d->buttonID]))
				$total[$d->buttonID]=(object)array();
			$total[$d->buttonID]->like=isset($total[$d->buttonID]->like)?$total[$d->buttonID]->like+$d->like:$d->like;
			$total[$d->buttonID]->unlike=isset($total[$d->buttonID]->unlike)?$total[$d->buttonID]->unlike+$d->unlike:$d->unlike;
		}
		return array(
			'stat'=>$data,
			'total'=>$total,
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'posts/admin',
						'act'=>'socStat',
					)
				)
			)->str,
		);
	}
	/*
		Like/Dislike
	*/
	function updateLike($pid,$isLike){
		if(!$pid) return;
		if(!$this->uhandler->rbac('voteAccess')){
			return;
		}
		$count=(int)db::qfetch("SELECT COUNT(*) as cnt FROM `".self::tbl."` WHERE id={$pid} AND user={$this->user->id}")->cnt;
		if($count){
			return;
		}
		db::query("INSERT IGNORE ".PREFIX_SPEC."user2vote (userID,postID) VALUES ({$this->user->id},{$pid})");
		if($isLike){
			db::query("UPDATE `".self::tbl."` SET `like`=`like`+1 WHERE id={$pid}");
		}else{
			db::query("UPDATE `".self::tbl."` SET `dislike`=`dislike`+1 WHERE id={$pid}");
		}
		exit;
	}
	function updateFBpublish($pid,$isDone){
		if(!$pid) return;
		if(!$this->uhandler->rbac('FBpublish')){
			return;
		}
		if($isDone){
			db::query("UPDATE `".self::tbl."` SET `FBpublished`='done' WHERE id={$pid}");
		}else{
			db::query("UPDATE `".self::tbl."` SET `FBpublished`='ban' WHERE id={$pid}");
		}
		db::query("DELETE FROM ".PREFIX_SPEC."user2vote WHERE postID='{$pid}'");
		exit;
	}
	function updateDates(){
		if(!$this->uhandler->rbac('editNews')) die('forbidden');
		updateDates();
		exit;
	}
	/*
		возвращает форму редактирования кейвордов
		вызов происходит из редактирования поста с помощью JS
	*/
	function editKeywords($pid,$prfxtbl){
		$tbls=\posts\tables::init($prfxtbl);
		$this->template='';
		$post=db::qfetch("SELECT id,kid FROM `{$tbls->post}` WHERE `id`='{$pid}' LIMIT 1");
		$post->keywords=array();
		#Проверяем разрешение на редактирование кейвордов
		if($accessEditKeyword=$this->uhandler->rbac('editKeyword')){
			# получаем кейворды
			$post->keywords=getKeywords($post);
			unset($post->keywords[$post->kid]);
		}
		return (object)array('post'=>$post);
	}
}
