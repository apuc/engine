<?php
namespace shop_posts_admin;
use module,db,url;
/*
	1. преобразование title в URL
*/
function key2url($title){#получить урл по названию
	return preg_replace(array('!(\s+|\/)!','![^\w\d\-\_\.]!iu'),array('-',''),strtolower(trim($title)));
}
/*
	Получает уникальный URL для таблицы
*/
function getUrl($title,$id=''){
	# получаем длину поля url
	list($length)=db::qrow("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS 
		WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='shop_post' && COLUMN_NAME='url'");
	$url=mb_substr(key2url($title),0,$length);
	$i=0;
	do{
		$i++;
		$turl=!isset($turl)?$url:"{$url}-{$i}";
		if(mb_strlen($turl,'utf8')>$length) {
			$url=mb_substr($url,0,-1); $i=0;
			continue;
		}
		list($tid)=db::qrow("SELECT id FROM `shop_post` WHERE url='$turl' && id!='$id' LIMIT 1");
	}while($tid);
	return $turl;
}
/*
	1. Очистка текста от лишних данных
*/
function stripText($text,$domain_approve=0){
	#заменяем неразрывные пробелы
	$text=str_replace('&nbsp;', ' ', $text);
	#убираем лишние мнемоники
	$text=html_entity_decode($text);
	$text=stripcslashes($text);
	if($domain_approve){
		$domain_approve=preg_replace(array('!^https?://!i','!^//!i','!^www.!i'), '', $domain_approve);
		$domain_approve=preg_quote($domain_approve);
	}
	# замена разрешенного домена на /
	$text=preg_replace('!((?:src|href)\=["\'])(?:https?\:)?//(?:www\.)?'.$domain_approve.'/*!si', '\1/',$text);
	# экранирование разрешенных картинок
	$text=preg_replace('!<\s*(img[^\>]+src\=[\'\"]/[^/][^\'\"]*[\'\"][^\>]+)>!si','%$1%',$text);
	# экранирование разрешенных ссылок
	$text=preg_replace('!<\s*(a[^\>]+href\=[\'\"]/[^/][^\'\"]*[\'\"][^\>]+)>(.+?)<\s*(/a)\s*>!si','%$1%$2%$3%',$text);
	# удаление скриптов, картинок и ссылок
	$text=preg_replace('!<\s*(script|a).*?>.*?</\1>!si', '', $text);
	$text=preg_replace('!<\s*(script|a|img).*?>!si', '', $text);
	# обработка разрешенных картинок
	$text=preg_replace('!\%(img[^\%]+)\%!si','<$1>',$text);
	# обработка разрешенных ссылок
	$text=preg_replace('!\%(a[^\%]+)\%([^\%]+)\%(/a)\%!si','<$1>$2<$3>',$text);

	return $text;
}

/*
	Получаем ID всех родительских категорий
*/
function getCatsParent($Cats,$reset=false){
	static $Res;
	if($reset) $Res=array();
	$catsEscape=array();
	foreach ($Cats as $val) {
		if(empty($val)) continue;
		$href = preg_quote(preg_replace(array('!^https?\://!i','!^www\.!i'), '', HREF));
		preg_match('!(https?:\/\/)?(www\.)?('.$href.')?\/?([^\/]+)\/?!i', $val, $matches);
		$purifiedVal = $matches[4];

		$catsEscape[]=db::escape($purifiedVal);
	}
	db::query("SELECT `url`,`parentId` FROM `shop_category` WHERE `url` IN('".implode("','",$catsEscape)."')");
	while ($d=db::fetch()) {
		$Res[$d->url]=$d->url;
		if(!empty($d->parentId)&&!isset($Res[$d->parentId]))
			$CatsParent[]=$d->parentId;
	}
	if(!empty($CatsParent)) 
		getCatsParent($CatsParent);
	return $Res;
}
/*
	получаем список авторов
*/
function getAuthors($authors){
	# получаем список авторов
	$userList=array();
	db::query("SELECT * FROM `".PREFIX_SPEC."users` WHERE `rbac` IN(".implode(',',$authors).")");
	while ($d=db::fetch()) {
		$d->longName=$d->mail.($d->name!=''?"&nbsp;({$d->name})":'');
		$userList[]=$d;
	}
	return $userList;
}
/*
 * Считаем количество разных типов постов
 * $type  1 - редактор // 0 - писатель
*/ 
function countPosts($sqlWhereCount,$tbl,$userHandler){
	$postsCounter=new \stdClass;
	#количество статей по пользователю
	# c текстом
	db::query("SELECT `user`,COUNT(id) AS `count` FROM `{$tbl}` WHERE `txt`!=''{$sqlWhereCount} GROUP BY `user`");
	while ($d=db::fetch()) {
		$postsCounter->text[$d->user]=$d->count;
	}
	# без текста
	db::query("SELECT `user`,COUNT(id) AS `count` FROM `{$tbl}` WHERE `txt`=''{$sqlWhereCount} GROUP BY `user`");
	while ($d=db::fetch()) {
		$postsCounter->notext[$d->user]=$d->count;
	}
	
	$status=array(
		'unpublished',
		'researchdone',
		'researchchecked',
		'waitcopywrite',
		'remakesearch',
		'remakecontent',
		'readytopublish',
		'published',
		'autoposting'
	);
	foreach ($status as $s) {
		db::query("SELECT `user`,COUNT(id) AS `count` FROM `{$tbl}` WHERE  published='{$s}' GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->{$s}[$d->user]=$d->count;
		}
	}
	
	if ($userHandler->rbac('authorList')) {
		db::query("SELECT `user`,COUNT({$tbl}.id) AS `count` FROM `{$tbl}` WHERE  published='waitcopywrite' AND user = '{$userHandler->user->id}' GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->author_todo[$d->user]=$d->count;
		}
	}
	if($userHandler->rbac(array('authorList','searcherList'))){
		if($userHandler->rbac('authorList'))
			$sqlVal="readytopublish";
		elseif($userHandler->rbac('searcherList'))
			$sqlVal="researchdone";
		db::query("SELECT `user`,COUNT(id) AS `count` FROM `{$tbl}` WHERE  published='{$sqlVal}' AND user = '{$userHandler->user->id}' GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->author_moderate[$d->user]=$d->count;
		}

		if($userHandler->rbac('authorList'))
			$sqlVal="remakecontent";
		elseif($userHandler->rbac('searcherList'))
			$sqlVal="remakesearch";
		db::query("SELECT `user`,COUNT({$tbl}.id) AS `count` FROM `{$tbl}` WHERE  published='{$sqlVal}' AND user = '{$userHandler->user->id}' GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->author_remake[$d->user]=$d->count;
		}
	}
	
	# не опубликованы (посты БЕЗ авторов)
	list($postsCounter->pub_no_without_user)=
		db::qrow("SELECT COUNT(id) AS `count` FROM `{$tbl}` WHERE user = '0' && published='unpublished'");
	
	if($userHandler->rbac('viewHistory')){
		# записи из истории
		db::query("SELECT `user`,COUNT(DISTINCT id) AS `count` FROM `".PREFIX_SPEC."postHistory` GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->history[$d->user]=$d->count;
		}
	}
	
	if($userHandler->rbac('editorList')){
		# free posts
		db::query("SELECT `user`,COUNT(id) AS `count` FROM `{$tbl}` WHERE `published` = 'published' && site = '' GROUP BY `user`");
		while ($d=db::fetch()) {
			$postsCounter->free_posts[$d->user]=$d->count;
		}
	}
		
	return $postsCounter;
}

/*
	расчет статусов для установки посту
*/
function calcStatus($foruser,$userHandler,$lastStatus,$formAction){
	list($rbac)=db::qrow("SELECT `rbac` FROM `".PREFIX_SPEC."users` WHERE id='{$foruser}' LIMIT 1");
	$status=false;
	if($formAction=='Accept'){
		if($userHandler->rbac('publishPost'))
			$status='researchchecked';
		else
			$status='waitcopywrite';
	}elseif($formAction=='Remake'){
		if(in_array($rbac,$userHandler->rbacByUT('editNewsAuthor')))
			$status='remakecontent';
		elseif(in_array($rbac,$userHandler->rbacByUT('editNewsSearch')))
			$status='remakesearch';
	}elseif($formAction=='Publish'){
		if($userHandler->rbac('publishPost'))
			$status='published';
	}elseif($formAction=='Autoposting'){
		if($userHandler->rbac('publishPost'))
			$status='autoposting';
	}else{
		#если нажата кнопка "save"
		if($userHandler->rbac('editNewsSearch'))
			$status='researchdone';
		elseif($userHandler->rbac('publishPost')&&in_array($rbac,$userHandler->rbacByUT('editNewsAuthor')))
			$status='waitcopywrite';
		elseif(in_array($rbac,$userHandler->rbacByUT('editNewsAuthor')))
			$status='readytopublish';
		elseif(empty($lastStatus))
			$status='unpublished';
	}
	return $status;
}

/*
	получает все категории поста, группы постов
*/
function getCatsByPost($url){
	if(!$url) return;
	$sqlPids=is_array($url)?"`pid` IN('".implode("','", $url)."')":"`pid`='{$url}'";

	$cats=array();
	db::query("SELECT `cid` FROM `".PREFIX_SPEC."category2shop_post` WHERE {$sqlPids}");
	while ($d=db::fetch()) {
		$cats[$d->cid]=$d->cid;
	}
	return getCatsParent($cats);
}
/*
	получает все категории группы постов по условию
*/
function getCatsByWhere($where){
	if(!$where) return;
	db::query(
		"SELECT `cid` FROM `".PREFIX_SPEC."category2shop_post` c
		INNER JOIN 
			(SELECT url FROM `post` WHERE {$where}) post 
		ON post.url=c.pid GROUP BY c.cid");
	while ($d=db::fetch()) {
		$cats[$d->cid]=$d->cid;
	}
	return getCatsParent($cats);
}