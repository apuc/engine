<?php
namespace posts_lists;
use module,db,url,cache;

#функции
require_once module::$path."/posts/lists/func.php";
#сторонние модули
require_once module::$path."/posts/handler.php";

class handler{
	const tbl='post';
	function __construct(){
		$this->headers=(object)array();
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->userControl=module::exec('user',array(),1)->handler;
		$this->user=$this->userControl->user;
	}
	/*
		1. список постов по категории
		2. подключает данные категории (текущей и список подкатегорий)
	*/ 
	function mainList($category,$prfxtbl,$uid,$page,$num,$uri,$popularPostsCount,$excludeCat){
		$tbls=\posts\tables::init($prfxtbl);
		$baseCat=module::exec('category',array('url'=>$category),'data')->data->cat;
		if(!$this->user->id&&$baseCat->id&&$baseCat->count<3) {
			$this->headers->location=HREF; return;
		}

		$accessEditNewsMy=$this->userControl->rbac('editNewsMy');
		$accessPublish=$this->userControl->rbac('publishPost');

		$showRights=showRights($accessPublish,$accessEditNewsMy,$uid);

		$start=($page-1)*$num;
		$q="SELECT 
				SQL_CALC_FOUND_ROWS post.*, 
				user.name AS authorName, user.mail AS authorMail 
			FROM (
				".listPostsSubquery($category,$excludeCat,$showRights)."
			) post
			/* присоединяем данные пользователя */
			LEFT JOIN `".PREFIX_SPEC."users` user 
				ON user.id=post.user
			";
		db::query($q." ORDER BY `datePublish` DESC,date DESC LIMIT $start,$num");
		$posts=array();$keywords=array();
		while($d=db::fetch()){
			//Получаем список параметров для шаблона из текста
			$d->data=$d->data!=''?json_decode($d->data):'';
			$d->stxt=pageBreak($d->txt);
			$d->txt=\posts\cutText($d->txt);
			#set author's name
			$d->authorName=\posts\setAuthorName($d);
			$posts[$d->id]=$d;
			$posts[$d->id]->keyword=&$keywords[$d->kid];
			$posts[$d->id]->funcPanel='';
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');
		if(empty($posts)&&$category&&!$this->user->id){
			$this->headers->location=HREF; return;
		}
		if($this->user->id){
			db::query("SELECT * FROM `keyword` WHERE id IN (".implode(",",array_keys($keywords)).")");
			while($d=db::fetch()){
				$keywords[$d->id]=$d->title;
			}
			foreach($posts as $id=>$d){
				$posts[$id]->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			}
		}

		#получаем pin посты
		$pins=getPins($category,showRights($accessPublish,$accessEditNewsMy,false,true));

		#получаем картинки
		db::query(
			"SELECT img.*,keyword.title FROM (
				SELECT pid,kid,url,width,height,priority,text FROM `".PREFIX_SPEC."imgs` img
				WHERE `tbl`='{$tbls->post}' && `pid` IN(".
					implode(',',array_merge(
						array_keys($posts),
						array_keys($pins)
						)
					)
				.")
			) img 
			LEFT JOIN `keyword` ON img.kid=keyword.id 
			ORDER BY img.priority DESC");
		while ($d=db::fetch()) {
			$img=new classImg;
			if(isset($posts[$d->pid]))$posts[$d->pid]->imgs[]=add2obj($img,$d);
			if(isset($pins[$d->pid]))$pins[$d->pid]->imgs[]=add2obj($img,$d);
		}
		#Выводим снипеты картинок если у постов нет текстов
		foreach($posts as $k=>&$pp){
			if($pp->txt==''&&isset($pp->imgs[0]))
				$pp->txt=$pp->imgs[0]->text;
		}
				
		#собираем title постов (3 шт) для description страницы
		$forDesc=array();
		if(!empty($posts)){
			reset($posts);
			while (@++$i<=3) {
				$p=current($posts);
				if(!empty($p)){
					$forDesc[]=$p->title; next($posts);
				}
			}
			reset($posts);
		}
		return array(
			'posts'=>@$posts,
			'prfxtbl'=>$tbls->prfx,
			'pins'=>$pins,
			#получаем данные категорий
			'cat'=>$baseCat,
			'breadCrumbs'=>\posts\getCrumbs($baseCat),
			'subCats'=>module::exec('category',array('act'=>'subList','url'=>$category,'tbl'=>$tbls->post),1)->str,
			'topLevelCats'=>module::exec('category',array('act'=>'mlist','tbl'=>$tbls->post,'category'=>$category,'parentId'=>$baseCat->parentId),1)->str,
			'page'=>$page,
			'num'=>$num,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'act'=>((!$category&&$uri!='')||($category&&$uri==''))?'index':'typesmall',
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>$uri==''?url::category($category?$category:'main')."%d/":$uri,
					true,
				)
			)->str,
			'count'=>$count,
			'accessPublish'=>$accessPublish,
			'access'=>$this->userControl->access(),
			'accessEditNewsMy'=>$accessEditNewsMy,
			'forDesc'=>$forDesc,
		);
	}
	/*
		особый вывод списка
			- все подкатегории
			- и заданное количество постов в подкатегориях
	*/
	function subCatList($category,$prfxtbl,$num){
		$tbls=\posts\tables::init($prfxtbl);
		$baseCat=module::exec('category',array('url'=>$category),'data')->data->cat;
		if(!$this->user->id&&$baseCat->id&&$baseCat->count<3) {
			$this->headers->location=HREF; return;
		}
		$accessEditNewsMy=$this->userControl->rbac('editNewsMy');
		$accessPublish=$this->userControl->rbac('publishPost');
		$showRights=showRights($accessPublish,$accessEditNewsMy);
		
		#получаем список подкатегорий
		db::query(
			"SELECT cat.*,SUBSTRING_INDEX(GROUP_CONCAT(post.id SEPARATOR ' '),' ',$num) posts
			FROM `".PREFIX_SPEC."{$tbls->category2post}` cp
			INNER JOIN `{$tbls->category}` cat ON cat.url=cp.cid && cat.parentID='{$category}'
			INNER JOIN `{$tbls->post}` ON post.url=cp.pid{$showRights}
			GROUP BY cp.cid");
		$subCats=array();
		$pids=array();
		$posts=array();
		while ($d=db::fetch()) {
			$d->posts=explode(' ', $d->posts);
			foreach ($d->posts as $val) {
				$pids[]=$val;
			}
			$subCats[]=$d;
		}
		#получаем посты для подкатегорий
		$concatPids='';
		if(count($pids)){
			$concatPids=implode(",", $pids);
			$keywords=array();
			db::query("SELECT * FROM `{$tbls->post}` WHERE id IN({$concatPids})");
			while ($d=db::fetch()) {
				$d->stxt=pageBreak($d->txt);
				$d->txt=\posts\cutText($d->txt);
				$posts[$d->id]=$d;
				$posts[$d->id]->keyword=&$keywords[$d->kid];
			}
		}
		#Получаем панель админа и основной кей
		if($this->user->id&&!empty($keywords)){
			db::query("SELECT * FROM `keyword` WHERE id IN (".implode(",",array_keys($keywords)).")");
			while($d=db::fetch()){
				$keywords[$d->id]=$d->title;
			}
			foreach($posts as $id=>$d){
				$posts[$id]->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			}
		}
		#получаем картинки
		$posts=getPostsImages($concatPids,$posts);

		#получаем pin посты
		$pins=getPins($category,showRights($accessPublish,$accessEditNewsMy,false,true));

		return array(
			'posts'=>$posts,
			'prfxtbl'=>$tbls->prfx,
			'subCatsPosts'=>$subCats,
			#получаем данные категорий
			'cat'=>$baseCat,
			'subCats'=>module::exec('category',array('act'=>'subList','url'=>$category,'tbl'=>$tbls->post),1)->str,
			'pins'=>$pins,
			'breadCrumbs'=>\posts\getCrumbs($baseCat),
			'topLevelCats'=>module::exec('category',array('act'=>'mlist','tbl'=>$tbls->post,'category'=>$category,'parentId'=>$baseCat->parentId),1)->str,
			'access'=>$this->userControl->access(),
		);
	}
	/*
		список постов по автору
		получает ID автора
	*/
	function listByUser($page,$num,$user){
		if(empty($user)){
			$this->headers->location=HREF; return;
		}
		#данные пользователя
		$userData=db::qfetch("SELECT *, name AS authorName, mail AS authorMail FROM `".PREFIX_SPEC."users` WHERE `id`='{$user}' LIMIT 1");
		$userData->authorName=\posts\setAuthorName($userData);

		#получаем список постов по пользователю
		$postsList=module::exec('posts/lists',array('act'=>'mainList','uid'=>$user,'page'=>$page,'num'=>$num,'uri'=>url::author($user)."%d/"),'data')->data;
		return array(
			'userData'=>$userData,
			'posts'=>$postsList->posts,
			'forDesc'=>$postsList->forDesc,
			#получаем данные категорий
			'topLevelCats'=>$postsList->topLevelCats,
			'page'=>$page,
			'num'=>$num,
			'count'=>$postsList->count,
			'accessPostAdd'=>$this->userControl->rbac('addNews'),
			'paginator'=>$postsList->paginator,
			'accessEditNewsMy'=>$postsList->accessEditNewsMy,
			'accessPublish'=>$this->userControl->rbac('publishPost'),
		);
	}
	function random($num){
		$cat=(object)array('title'=>'random','url'=>'random');
		return array(
			'cat'=>$cat,
			'access'=>$this->userControl->access(),
			'posts'=>\posts\randomPosts($num),
			'breadCrumbs'=>array($cat),
		);
	}
	function top($page,$num){
		$top=array();
		$start=($page-1)*$num;
		$sql="SELECT * FROM post ORDER BY statViewsShort DESC limit $start,$num";
		db::query($sql);
		while ($d=db::fetch()) {
			$d->txt=\posts\cutText(strip_tags($d->txt),20);
			$top[$d->id]=$d;
		}
		\posts\getImages2list($top);
		$cat=(object)array('title'=>'Top','url'=>'top');
		return array(
			'cat'=>$cat,
			'access'=>$this->userControl->access(),
			'posts'=>$top,
			'breadCrumbs'=>array($cat),
		);
	}
	/**
	 * Список постов, за которые не голосовали
	 */
	function listVote($page,$num){
		#Проверяем права
		if(!$this->userControl->rbac('voteAccess')){
			$this->headers->location=HREF;
			return;
		}
		$tbls=new \posts\tables;
		$start=($page-1)*$num;
		db::query(
			"
				SELECT SQL_CALC_FOUND_ROWS post.*, user.name AS authorName, user.mail AS authorMail, (post.like-post.dislike) AS likeSum
				FROM `{$tbls->post}` post
				LEFT JOIN `".PREFIX_SPEC."user2vote` vote ON vote.postID=post.id AND vote.userID={$this->user->id}
				LEFT JOIN `".PREFIX_SPEC."users` user ON user.id=post.user 
				WHERE `user`!='{$this->user->id}' && vote.userID IS NULL && post.published='published' && post.FBpublished='proc'
				ORDER BY post.datePublish DESC
				LIMIT $start,$num
			"
		);
		$posts = array();
		while($d=db::fetch()){
			$d->txt=\posts\cutText($d->txt);
			$d->authorName=\posts\setAuthorName($d);
			$d->canVote=$d->user!=$this->user->id;
			$d->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			$posts[$d->id]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		if($page>1&&empty($posts)){
			$this->headers->location=HREF;
			return;
		}
		return array(
			'posts'=>$posts,
			#получаем категории
			'page'=>$page,
			'num'=>$num,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>'/?module=posts&act=listVote&page=%d',
					true,
				)
			,1)->str,
			'count'=>$count,
			'FBpublishAcceess'=>$this->userControl->rbac('FBpublish'),
		);
	}

	/**
	 * Список постов, за которые голосовали либо авторские
	 */
	function listMyVote($page,$num){
		#Проверяем права
		if(!$this->userControl->rbac('myVoteList')){
			$this->headers->location=HREF;
			return;
		}
		$tbls=new \posts\tables;
		$start=($page-1)*$num;
		db::query(
			"
				SELECT SQL_CALC_FOUND_ROWS post.*, user.name AS authorName, user.mail AS authorMail, (post.like-post.dislike) AS likeSum, FBpublished
				FROM `{$tbls->post}` post
				LEFT JOIN `".PREFIX_SPEC."user2vote` vote ON vote.postID=post.id AND vote.userID={$this->user->id}
				LEFT JOIN `".PREFIX_SPEC."users` user ON user.id=post.user 
				WHERE (`user`='{$this->user->id}' || vote.userID IS NOT NULL) && post.published='published' && post.FBpublished='proc'
				ORDER BY likeSum DESC
				LIMIT $start,$num
			"
		);
		$posts = array();
		while($d=db::fetch()){
			$d->txt=\posts\cutText($d->txt);
			$d->authorName=\posts\setAuthorName($d);
			$d->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			$posts[$d->id]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		if($page>1&&empty($posts)){
			$this->headers->location=HREF;
			return;
		}
		return array(
			'posts'=>$posts,
			#получаем категории
			'page'=>$page,
			'num'=>$num,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>'/?module=posts&act=listVote&page=%d',
					true,
				)
			,1)->str,
			'count'=>$count,
			'FBpublishAcceess'=>$this->userControl->rbac('FBpublish'),
		);
	}
}
