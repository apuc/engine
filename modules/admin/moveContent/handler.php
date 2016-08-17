<?php
namespace admin_moveContent;
use module,db,url;

#собственные функции
require_once(__DIR__.'/func.php');

#Используем сторонние модули
require_once(module::$path.'/images/admin/sh/uploadImages.php');
require_once(module::$path.'/posts/admin/handler.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler {
	private $src_db;
	private $dest_db;

	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->userControl=module::exec('user',array(),1)->handler;
		$this->user=$this->userControl->user;
		$this->dest_db=$GLOBALS['db'];
	}

	function switch_db($link){
		if(!isset($this->src_db))
			$this->src_db=new db(SRC_DB_HOST,SRC_DB_USER,SRC_DB_PASS,SRC_DB_NAME);
		$GLOBALS['db']=$this->{$link};
	}

	function form($count,$site){#Делаем все обработки для вывода данных
		if(@!$this->userControl->rbac('moveContent')) die('forbidden');
		@$this->headers->cookie->count=array($count,'+1 year');
		$this->switch_db("src_db");
		$posts=db::qall("SELECT p.id, p.url, p.title, GROUP_CONCAT(c.title
				ORDER BY c.id SEPARATOR ', ') AS cats
			FROM post AS p
			LEFT JOIN ".PREFIX_SPEC."category2post AS c2p ON (c2p.pid = p.url)
			LEFT JOIN category AS c ON (c.url = c2p.cid)
			WHERE p.site='$site' AND (p.published='published' OR p.published='autoposting')
			GROUP BY p.id
			ORDER BY p.id
			LIMIT $count");
		$postIds=array(0);
		foreach ($posts as $post) $postIds[]=$post->id;
		$categories=db::qall("SELECT c.title, COUNT(*) AS count
			FROM category AS c
			INNER JOIN ".PREFIX_SPEC."category2post AS c2p ON (c2p.cid = c.url)
			INNER JOIN post AS p ON (p.url = c2p.pid)
			WHERE p.id IN (".join(', ', $postIds).")
			GROUP BY c.id
			ORDER BY c.title");
		$sitesWithCopiedContent=db::qall("SELECT p.site FROM post AS p WHERE p.site <> '' AND (p.published='published' OR p.published='autoposting') GROUP BY p.site");
		$data=(object)array(
			'count'=>$count,
			'posts'=>$posts,
			'categories'=>$categories,
			'totalFreePosts'=>db::qfetch("SELECT COUNT(*) AS total FROM post p WHERE p.site='' AND (p.published='published' OR p.published='autoposting')")->total,
			'customFreePosts'=>$site?db::qfetch("SELECT COUNT(*) AS total FROM post p WHERE p.site='{$site}' AND (p.published='published' OR p.published='autoposting')")->total:null,
			'allPosts'=>db::qfetch("SELECT COUNT(*) AS total FROM post p WHERE p.published='published' OR p.published='autoposting'")->total,
			'sitesWithCopiedContent'=>$sitesWithCopiedContent,
			'selectedSite'=>$site,
		);
		$this->switch_db("dest_db");
		return $data;
	}

	function process($pid){
		if(@!$this->userControl->rbac('moveContent')) die('forbidden');

		if(!$pid){
			return (object)array('countPosts'=>false);
		}

		set_time_limit(0);

		$postsFilter='('.join(', ', array_map('intval', $pid)).')';
		#определяем режим копирования
		$this->switch_db("dest_db");
		$type='copy';

		list($count)=db::qrow("SELECT COUNT(id) FROM `post`");
		
		if(intval($count)){
			$tp=db::qfetch("SELECT `id`,`title` FROM `post` ORDER BY `id` DESC LIMIT 1");
			$this->switch_db("src_db");
			db::query("SELECT `id`,`title` FROM `post` WHERE `id`='{$tp->id}' && `title`='".db::escape($tp->title)."'");
			if(!db::num_rows())
				$type='merge';
		}

		if($type=='copy'){
			$countPosts=copyPosts($this,$postsFilter);
			$cats2post=copyCategory2post($this,$postsFilter);
			copyCategory($this,$postsFilter,$cats2post);
			copyKeywords($this,$postsFilter);
			$imgs4dl=copyImages($this,$postsFilter);
		}elseif($type=='merge'){
			$old2newPids=mergePosts($this,$postsFilter);
			if($countPosts=count($old2newPids)){
				$old2newKids=mergeKeywords($this,$postsFilter,$old2newPids);
				#копируем категории как есть, связь зависит от url, а не от ID
				$cats2post=copyCategory2post($this,$postsFilter);
				copyCategory($this,$postsFilter,$cats2post);
				$imgs4dl=mergeImages($this,$postsFilter,$old2newPids,$old2newKids);
			}
		}

		#на удаленном сервере помечает скопированные посты
		$this->switch_db("src_db");
		db::query("UPDATE post SET site='".db::escape(HREF)."' WHERE id IN $postsFilter");

		$this->switch_db("dest_db");

		return (object)array(
			'countPosts'=>$countPosts,
			'type'=>$type,
			'imgs4dl'=>isset($imgs4dl)?$imgs4dl:array(),
		);
	}

	function downloadImage($url,$link){
		if(@!$this->userControl->rbac('moveContent')) die('forbidden');
		$imgDir=PATH.'modules/images/files/images/';
		@mkdirRec($imgDir);
		$imgFile=$imgDir.$url;
		$urlData=parse_url($link);
		$ch=curl_init($link);
		curl_setopt_array($ch,array(
				CURLOPT_REFERER=>$urlData['scheme'].'://'.$urlData['host'].'/',
				CURLOPT_FOLLOWLOCATION=>1,
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_HEADER=>0,
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
			)
		);
		$resp=array('result'=>(bool)file_put_contents($imgFile,curl_exec($ch)));
		die(json_encode($resp));
	}
	/*
		Страница изменения количесва постов добавляющихся на сайт по времени
	*/
	function autoPosting($submit=false, $per_day=5, $at_once=10){
		if(!$this->userControl->rbac('autoPosting')) die('forbidden');
		if($submit){
			db::query("REPLACE INTO ".PREFIX_SPEC."config (`key`, `value`) VALUES ('autopost_per_day', {$per_day})");

			$posts = db::qall("SELECT id,datePublish FROM post WHERE published = 'autoposting' ORDER BY datePublish ASC");
			if(count($posts)>0){
				if($at_once>0){
					foreach($posts as $k=>$post) {
						if($at_once-- <= 0){break;}
						if(strtotime($post->datePublish)>time()){
							$time=strtotime("today");
							$time+=mt_rand(0,time()-$time);
							$date=date("Y-m-d H:i:s",$time);
						}else{
							$date=$post->datePublish;
						}
						db::query("UPDATE post SET published='published',datePublish='$date' WHERE id = ".intval($post->id));
						unset($posts[$k]);
					}
				}
				foreach($posts as &$post){
					db::query("UPDATE post SET datePublish = '".getDatePublish($per_day)."' WHERE id = ".intval($post->id));
				}
			}
			#обновляем количество постов в категориях
			module::exec('category',array('act'=>'updateCount','cats'=>\posts_admin\getCatsByWhere("published='published'"),'tbl'=>'post'),'data');
		}else{
			list($per_day)=db::qrow("SELECT `value` FROM ".PREFIX_SPEC."config WHERE `key`='autopost_per_day'");
			if(is_null($per_day))$per_day =5;
		}
		# количество доступных постов
		list($count)=db::qrow("SELECT COUNT(*) FROM post WHERE published='autoposting'");
		return (object)array(
			'per_day'=>$per_day,
			'count'=>(int)$count,
		);
	}
	/*
		Функция обновления сайта. Выполняет вывод новых постов из автопостинга
	*/
	function autoPostingCheck(){
		static $checked=false;
		if(!$checked){
			db::query("UPDATE post SET published='published' WHERE datePublish<='".date('Y-m-d H:i:s')."' && published='autoposting'");
			if(db::affected()){
				db::query("SELECT url FROM post WHERE `published`='published'");
				while ($d=db::fetch()) {
					$ids[]=$d->url;
				}
				#обновляем количество постов в категориях
				if(!empty($ids))
					module::exec('category',array('act'=>'updateCount','cats'=>\posts_admin\getCatsByPost($ids),'tbl'=>'post'),'data');
			}
			$checked=true;
		}
	}
}
