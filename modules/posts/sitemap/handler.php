<?php
namespace posts_sitemap;
use module,db,url,cache;
require_once(module::$path."/posts/handler.php");
class handler{
	function __construct(){
		/*
			set to config.php
			define('CACHE_DEBUG',0);
		*/
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->perpage=10000;#количество URL в одном sitemap
	}
	function index(){
		header("Content-type: text/xml");		
		return (object)array('maxpage'=>cache::get(__NAMESPACE__.'\getSitemapIndex',$this->perpage,false,'1 week'));
	}
	function sitemap($page){#Делаем все обработки для вывода данных
		if(!$page) die;
		$posts=cache::get(__NAMESPACE__.'\getSitemapPosts',array($page,$this->perpage),$page,'1 week');
		if(empty($posts)) die;
		else header("Content-type: text/xml");
		return (object)array(
			'posts'=>$posts,
		);
	}
}

function getSitemapIndex($perpage){
	list($count)=db::qrow("SELECT COUNT(id) FROM `post` WHERE `published`='published' && `pincid`=''");
	$maxpage=ceil($count/$perpage);
	if($maxpage<1) $maxpage=1;
	return $maxpage;
}
function getSitemapPosts($page,$perpage){
	$posts=array();
	$limit=($page-1)*$perpage;
	db::query("SELECT `url`,`datePublish` FROM `post` 
		WHERE `published`='published' && `pincid`='' 
		LIMIT $limit,$perpage");
	while ($d=db::fetch()) {
		$posts[]=$d;
	}
	\posts\getImages2list($posts);
	return $posts;
}
