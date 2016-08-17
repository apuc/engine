<?php
#маршруты модуля
route::set('^/sitemap.xml$',array('module'=>'posts/sitemap','act'=>'index'),999);
route::set('^/sitemap(\d+).xml$',array('module'=>'posts/sitemap','act'=>'sitemap','page'),999);

#url модуля
$GLOBALS['urls']->postsSitemap=HREF.'/sitemap$i.xml';
?>