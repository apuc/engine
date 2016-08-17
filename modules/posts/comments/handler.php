<?php
namespace posts_comments;
use module,db,url;

#Используем собственные функции
require_once(PATH."modules/category/handler.php");
require_once(PATH."modules/posts/handler.php");

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),'data')->handler;
		$this->user=$this->uhandler->user;
		$this->tbl=PREFIX_SPEC.'comments';
	}
	function index($pid){
		$tbls=\posts\tables::init();
		return (object)array(
			'comments'=>module::exec('posts/comments',array('act'=>'comments','pid'=>$pid))->str,
			'form'=>module::exec('posts/comments',array('act'=>'commentform'),1)->str,
			'pid'=>$pid,
			'prfxtbl'=>$tbls->prfx,
		);
	}
	/* 
		Форма коментариев и список коментов. 
		Используем для вставки в страницу с основным контентом
	*/ 
	function comments($pid){
		$tbls=\posts\tables::init();
		# получаем неотклоненные комментарии комментарии
		$comments=array();
		$ummodcomments=array();
		list($count)=db::qrow("SELECT COUNT(id) FROM `$this->tbl` WHERE `pid`='$pid' && `tbl`='{$tbls->post}'");
		db::query(
			"SELECT comment.*,u.name AS authorName,u.mail AS authorMail FROM `$this->tbl` comment 
				LEFT JOIN `".PREFIX_SPEC."users` u 
				ON u.id=comment.uid
			WHERE comment.moderate NOT IN('4') && comment.pid='$pid' && `tbl`='{$tbls->post}' 
			GROUP BY comment.id ORDER BY comment.`date` DESC LIMIT 50");
		while($d=db::fetch()){
			$d->name=\posts\setAuthorName($d);
			$d->self=($this->user->id==$d->uid)?true:false;
			#разделяем проверенные и непроверенные
			if($d->moderate==3)
				$d->approve=true;
			else{
				$d->text='';
				$d->approve=false;
			}
			$comments[]=$d;
		}

		$post=db::qfetch(
			"SELECT post.url,GROUP_CONCAT(rel.cid) AS cids FROM `{$tbls->post}` post
				LEFT JOIN `".PREFIX_SPEC."{$tbls->category2post}` AS rel ON rel.pid=post.url
			WHERE post.`id`='{$pid}' GROUP BY post.id LIMIT 1");

		return (object)array(
			'comments'=>$comments,
			'otherComments'=>otherComments(getBaseCat($post,$tbls),$pid),
			'countComments'=>$count,
			'pid'=>$pid,
			'tbl'=>$tbls->post,
			'url'=>$post->url,
		);
	}
	#Страница со списком непромодерированных коментариев
	function commentsPage($url,$page,$num){
		$tbls=\posts\tables::init();
		$url=urldecode($url);
		if(empty($url)) return;
		$post=db::qfetch(
			"SELECT post.id,post.title,post.url,GROUP_CONCAT(rel.cid) AS cids FROM `{$tbls->post}` post 
				LEFT JOIN `".PREFIX_SPEC."{$tbls->category2post}` AS rel ON rel.pid=post.url
			WHERE post.`url`='{$url}' GROUP BY post.id LIMIT 1");
		if(empty($post->id)) return;
		$this->template='template'; # определяем шаблон вывода
		# получаем количество комментариев
		$start=($page-1)*$num;
		list($count)=db::qrow("SELECT COUNT(`id`) FROM `$this->tbl` WHERE pid='{$post->id}' && tbl='{$tbls->post}'");
		# получаем комментарии
		db::query(
		"SELECT comment.*,u.name AS authorName,u.mail AS authorMail FROM `$this->tbl` comment 
			LEFT JOIN `".PREFIX_SPEC."users` u ON u.id=comment.uid
		WHERE comment.pid='{$post->id}' && comment.tbl='{$tbls->post}' 
		GROUP BY comment.id ORDER BY comment.`date` DESC LIMIT $start,$num");
		while($d=db::fetch()){
			$d->name=\posts\setAuthorName($d);
			$d->text=stripslashes($d->text);
			$comments[]=$d;
		}
		if(empty($comments)) { $this->headers->location=HREF; return; }
		return (object)array(
			'comments'=>$comments,
			'otherComments'=>otherComments(getBaseCat($post),$post->id),
			'countComments'=>$count,
			'post'=>$post,
			'paginator'=>module::exec('plugins/paginator',array('page'=>$page,'num'=>$num,'count'=>$count,'uri'=>url::commentsPage($url).'?page=%d'),1)->str,
		);
	}
	#Форма для коментариев
	function commentform($text){
		return (object)array(
			'text'=>$text,
			'user'=>$this->user,
			'auth'=>\user\getAuthButtonsUrls(),
		);
	}
	#Сохраняем коментарий и регаем нового пользователя если надо
	function save($pid,$prfxtbl,$mail,$text){
		$tbls=\posts\tables::init($prfxtbl);
		#Регистрируем нового пользователя если указал мыло
		if(!$this->user->id){
			if(preg_match("!^[^@]+@[^@.]+\.[^@]+$!",$mail)){
				$pas=mt_rand(5000,10000000);
				$res=module::exec('user',array('act'=>'register','mail'=>$mail,'pas'=>$pas,'submit'=>1),'data')->data;
				if(!empty($res->err[0]))
					if($res->err[0]=='mailExists')
						$err='this user already exists, please sign in';
				if(!isset($err)){
					$this->uhandler=module::exec('user',array('act'=>'login','mail'=>$mail,'pas'=>$pas,'submit'=>1,'remember'=>1),'data')->handler;
					$this->user=$this->uhandler->user;
					if(!$this->user->id) $err='signInError';
				}
			}else
				$err='badMail';
		}
		#проверяем наличие текста
		if(!$text) $err='textEmpty';
		if(empty($err)){
			$text=strip_tags($text,'<strong><b><i><br><ul><li><ol><p><span>');
			#устанавливаем доверие к коментарию
			$moderate=0;
			if($this->user){
				if(@$this->uhandler->rbac($this->user->rbac,'commentWithoutModer')) $moderate=3;
			}
			#Сохраняем
			db::query("INSERT INTO `$this->tbl` (uid,date,text,pid,tbl,`moderate`,`cid`) 
					VALUES ('{$this->user->id}',NOW(),'$text','$pid','{$tbls->post}','{$moderate}','".getBaseCat($pid,$tbls)."')");
			$id=db::insert();
			return (object)array(
				'saved'=>$id?'saved':'',
				'error'=>$id?'':'mysql insert error',
				'form'=>module::exec('posts/comments',array('act'=>'commentform'),1)->str,
				'comments'=>module::exec('posts/comments',array('act'=>'comments','pid'=>$pid))->str,
			);
		}else
			return (object)array(
				'error'=>$err,
				'form'=>module::exec('posts/comments',array('act'=>'commentform','text'=>$text),1)->str,
				'comments'=>module::exec('posts/comments',array('act'=>'comments','pid'=>$pid))->str,
			);
	}
	/*
		удаление комментария
	*/
	function del($id){
		if(!$id) return;
		list($owner)=db::qrow("SELECT uid FROM `$this->tbl` WHERE `id`='".db::escape($id)."' LIMIT 1");
		if($owner==$this->user->id){
			db::query("DELETE FROM `$this->tbl` WHERE `id`='".db::escape($id)."' LIMIT 1");
			echo 'done';
		}
		die;
	}
}

/*
	получает последнюю из сортировки категорию поста
*/
function getBaseCat($pid,$tbls){
	if(!is_object($pid)){
		$post=db::qfetch(
		"SELECT post.*,GROUP_CONCAT(rel.cid) AS cids FROM `{$tbls->post}` post
			LEFT JOIN `".PREFIX_SPEC."{$tbls->category2post}` AS rel ON rel.pid=post.url
		WHERE post.`id`='{$pid}' GROUP BY post.id LIMIT 1");	
	}else
		$post=$pid;
	$post->cats=!empty($post->cids)?\category\getCategoryData(explode(',', $post->cids),0):array();
	$post->cats=\posts\sortCats($post->cats);
	return !empty($post->cats)?end($post->cats)->url:false;
}
/*
	получает комментарии к постам такой же категории
*/
function otherComments($cid,$exclude){
	$tbl=\posts\tables::init()->post;
	$comments=array();
	if(empty($cid)) return $comments;
	db::query(
		"SELECT post.url,post.title,comment.*,u.name AS authorName,u.mail AS authorMail FROM `".PREFIX_SPEC."comments` comment 
			LEFT JOIN `".PREFIX_SPEC."users` u 
				ON u.id=comment.uid
			INNER JOIN `{$tbl}` post
				ON post.id=comment.pid
		WHERE comment.moderate NOT IN('4') && comment.pid!='{$exclude}' && `tbl`='{$tbl}' && comment.cid='{$cid}'
		GROUP BY comment.id 
		ORDER BY comment.`date` DESC 
		LIMIT 5");
	while($d=db::fetch()){
		$d->name=\posts\setAuthorName($d);
		#разделяем проверенные и непроверенные
		if($d->moderate==3)
			$d->approve=true;
		else{
			$d->text='';
			$d->approve=false;
		}
		$comments[]=$d;
	}
	return $comments;
}
