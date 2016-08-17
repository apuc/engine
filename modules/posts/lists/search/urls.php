<?php
#url модуля
$GLOBALS['urls']->searchQuery=HREF.'/search/$q/';
$GLOBALS['urls']->searchQueryPage=HREF.'/search/$q/$page/';

route::set('^/search/([^/]+)/(?:(\d+)/?)?',array('module'=>'posts/lists/search','q','page'),4);
route::set('^/search_([^\/]+)/([^/]+)/(?:(\d+)/?)?',array('module'=>'posts/lists/search','prfxtbl','q','page'),4);