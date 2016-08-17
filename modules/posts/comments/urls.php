<?php
route::set('^/comments/([^/]+)\.html(?:[?&]page=(\d+))?',
	array('module'=>'posts/comments','act'=>'commentsPage','url','page'),4);

#url модуля
$GLOBALS['urls']->commentSave=HREF.'?module=posts/comments&act=save';
$GLOBALS['urls']->commentsPage=HREF.'/comments/$url.html';
?>