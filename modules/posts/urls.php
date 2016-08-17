<?php
route::set('^/([^/]+)\.html',array('module'=>'posts','act'=>'post','url'));
route::set('^/rss/?$',array('module'=>'posts','act'=>'rss'),999);

#url модуля
$GLOBALS['urls']->post='';

function url_post($url,$prfxtbl=''){
	$str=empty($prfxtbl)?"/$url.html":"/?module=posts&act=post&url=$url&prfxtbl=$prfxtbl";
	return HREF.$str;
}
