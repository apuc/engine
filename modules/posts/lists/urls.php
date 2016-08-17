<?php
route::set('^/user/([0-9]+)/([0-9]+)?/?',array('module'=>'posts/lists','act'=>'listByUser','uid','page'),2);
route::set('^/([^/]+?)/([0-9]+)?/?',array('module'=>'posts/lists','act'=>'mainList','cat','page'));
route::set('^/random/',array('module'=>'posts/lists','act'=>'random'),2);
route::set('^/top/',array('module'=>'posts/lists','act'=>'top'),2);
route::set('^/details/([^/]+?)/',array('module'=>'posts/lists','act'=>'subCatList','cat'),2);

$urls->category='';
$urls->author=HREF.'/user/$id/';
$urls->listRandom=HREF.'/random/';
$urls->listTop=HREF.'/top/';

$urls->listVote=HREF.'/?module=posts/lists&act=listVote';
$urls->listMyVote=HREF.'/?module=posts/lists&act=listMyVote';

/*
 	определяет вид url для категории
 	url::category()
*/
function url_category($url,$prfxtbl=''){
	if(empty($prfxtbl)){
		$default=$url==''?'':'/%s/';
		$defaultDetails='/details/%s/';	
	}else{
		$default='/?module=posts/lists&act=mainList&cat=%s&prfxtbl=%s';
		$defaultDetails='/?module=posts/lists&act=subCatList&cat=%s&prfxtbl=%s';
	}
	if(is_object($url)){
		if(!empty($url->subCatList))
			$str=sprintf($defaultDetails,$url->url,$prfxtbl);
		else
			$str=sprintf($default,$url->url,$prfxtbl);
	}else
		$str=sprintf($default,$url,$prfxtbl);
	return HREF.$str;
}
