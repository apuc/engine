<?php
namespace plugins_paginator;
use module,db;

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		#$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object) array();
		$this->userControl=module::exec('user', array(), 1)->handler;
		$this->user=$this->userControl->user;
	}
	function index($page,$num,$allItem,$uri='',$showPagen=false,$setnum){#пагинация на странице
		if($setnum)
			@$this->headers->cookie->num=array($setnum,"+5 years");
		if($page<1)$page=1;
		if(strpos($uri,"%d")===false){$uri="$uri%d.html";}
		#$firstPage=(substr($uri,-3)=='%d/')?substr($uri,0,-3):$uri;
		$firstPage=str_replace(array('main/%d/','%d/','%d.html','%d'),array('','','','1'),$uri);
		$pages=array();
		$allPage=ceil($allItem/$num);#Всего страниц
		if($num<$allItem){
			$pageCn=15;
			$pageCnHalf=floor($pageCn/2);
			$from=($from=$page-$pageCnHalf)<1?1:$from;
			$plusend=0;
			if(($minus=$page-$from)<$pageCnHalf){
				$plusend=$pageCnHalf-$minus;
			}
			if(($end=$page+$pageCnHalf+$plusend)>$allPage){
				$minusfrom=($plus=$end-$allPage)>$pageCnHalf?$pageCnHalf:$plus;
				if(($res=$from-$minusfrom)>0) $from=$res;
				$end=$allPage;
			}
			for($i=$from;$i<=$end;$i++){
				$url=sprintf($i==1?$firstPage:$uri,$i);
				$pages[$i]=$url;
			}
		}
		if($showPagen||@$this->user->rbac==1){
			$showPagen=true;
		}
		return (object)array(
			'page'=>$page,
			'num'=>$num,
			'pages'=>$pages,
			'showPagen'=>$showPagen,
			'firstPage'=>$firstPage,
			'allPage'=>$allPage,
			'nav'=>(object)array(
				'first'=>sprintf($firstPage,1),
				'prev'=>sprintf($page-1==1?$firstPage:$uri,$page-1),
				'next'=>sprintf($uri,$page+1),
				'end'=>sprintf($uri,$allPage),
			),
		);
	}
	function typesmall($page,$num,$allItem,$uri='',$showPagen=false,$setnum){
		return module::exec('plugins/paginator',
			array(
				'act'=>'index',
				'page'=>$page,
				'num'=>$num,
				'count'=>$allItem,
				'uri'=>$uri,
				'showPagen'=>$showPagen,
				'setnum'=>$setnum,
			),'data')
		->data;
	}
}
