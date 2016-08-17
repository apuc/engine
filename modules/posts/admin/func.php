<?php
namespace posts_admin;
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
function getUrl($tbl,$title,$id=''){
	# получаем длину поля url
	list($length)=db::qrow("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS 
		WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='{$tbl}' && COLUMN_NAME='url'");
	$url=mb_substr(key2url($title),0,$length);
	$i=0;
	do{
		$i++;
		$turl=!isset($turl)?$url:"{$url}-{$i}";
		if(mb_strlen($turl,'utf8')>$length) {
			$url=mb_substr($url,0,-1); $i=0;
			continue;
		}
		list($tid)=db::qrow("SELECT id FROM `$tbl` WHERE url='$turl' && id!='$id' LIMIT 1");
	}while($tid);
	return $turl;
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
	db::query("SELECT `url`,`parentId` FROM `category` WHERE `url` IN('".implode("','",$catsEscape)."')");
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
 * запись изменений в историю
 */
function saveInHistory($id, $user_id) {
	if (empty($id) || empty($user_id)) return;
	
	$sql = sprintf('
		INSERT INTO `%s%s`
		(`id`, `date`, `url`, `title`, `txt`, `sources`, `user`, `published`, `site`, `statViews`, `statViewsShort`, `statShortFlag`)
		SELECT 
		`id`, NOW() AS date, `url`, `title`, `txt`, `sources`, %d, `published`, `site`, `statViews`, `statViewsShort`, `statShortFlag`
		FROM `post`
		WHERE id = %d
	', PREFIX_SPEC, 'postHistory', $user_id, $id);
	db::query($sql);
}
/*
	получает все категории поста, группы постов
*/
function getCatsByPost($url){
	if(!$url) return;
	$tbls=\posts\tables::init();
	$sqlPids=is_array($url)?"`pid` IN('".implode("','", $url)."')":"`pid`='{$url}'";

	$cats=array();
	db::query("SELECT `cid` FROM `".PREFIX_SPEC."{$tbls->category2post}` WHERE {$sqlPids}");
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
	$tbls=\posts\tables::init();
	db::query(
		"SELECT `cid` FROM `".PREFIX_SPEC."{$tbls->category2post}` c
		INNER JOIN 
			(SELECT url FROM `{$tbls->post}` post WHERE {$where}) post 
		ON post.url=c.pid GROUP BY c.cid");
	while ($d=db::fetch()) {
		$cats[$d->cid]=$d->cid;
	}
	return getCatsParent($cats);
}
/*
	расчет времени автопостинга поста
*/
function getDatePublish($per_day=5,$itime=''){
	static $time;
	$delta=24*60*60/$per_day; #количество секунд между постами
	if(!empty($itime))$time=$itime;
	if(!$time)$time=strtotime('tomorrow');
	else $time+=$delta;
	return date('Y-m-d H:i:s',$time+mt_rand(0,$delta));
}

/*
	получает список кейвордов принадлежащих посту
*/
function getKeywords(&$post){
	$tbls=\posts\tables::init();
	$keywords=array();
	if(!empty($post->id)){
		db::query("SELECT k.* FROM `".PREFIX_SPEC."keyword2post` kp 
			INNER JOIN `keyword` k ON k.id=kp.kid 
			WHERE kp.pid='{$post->id}' && `tbl`='{$tbls->post}'");
		while ($d=db::fetch()) {
			$keywords[$d->id]=$d;
		}
	}
	return $keywords;
}
/*
	добавляет/изменяет кейворд поста (гланый кейворд)
*/
function setKeyword($post,$keyword){
	$tbls=\posts\tables::init();
	$keywordSqlSave=db::escape($keyword);
	$currentkid=empty($post->kid)?false:$post->kid;
	list($kid)=db::qrow("SELECT id FROM `keyword` WHERE `title`='{$keywordSqlSave}' LIMIT 1");
	$kid=empty($kid)?false:$kid;
	if(empty($keyword)){
		#если пустой кейворд - отвязываем
		db::query("UPDATE `{$tbls->post}` SET `kid`='0' WHERE id='{$post->id}' LIMIT 1");
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2post` WHERE `pid`='{$post->id}' && `tbl`='{$tbls->post}' && `kid`='{$currentkid}'");
		#если кейворд ни к чему не привязан - удаляем его
	}elseif(!$currentkid&&!$kid){
		#если нет главного кейворда и кейворд не существует, добавляем
		db::query("INSERT INTO `keyword` SET `title`='{$keywordSqlSave}'");
		$kid=db::insert();
		db::query("INSERT IGNORE INTO `".PREFIX_SPEC."keyword2post` SET `pid`='{$post->id}',`tbl`='{$tbls->post}',`kid`='{$kid}'");
		db::query("UPDATE `post` SET `kid`='{$kid}' WHERE id='{$post->id}' && `tbl`='{$tbls->post}' LIMIT 1");
	}elseif(!$currentkid&&$kid){
		#если нет главного кейворда и c указанным title есть кейворд - привязываем
		db::query("INSERT IGNORE INTO `".PREFIX_SPEC."keyword2post` SET `pid`='{$post->id}',`tbl`='{$tbls->post}',`kid`='{$kid}'");
		db::query("UPDATE `post` SET `kid`='{$kid}' WHERE id='{$post->id}' && `tbl`='{$tbls->post}' LIMIT 1");
	}elseif($currentkid&&!$kid){
		# если c указанным title не существует - UPDATE текущего
		db::query("UPDATE `keyword` SET `title`='{$keywordSqlSave}' WHERE `id`='{$currentkid}' LIMIT 1");
	}elseif($currentkid!=$kid){
		# если указан новый кейворд и он существует - привязываем новый
		db::query("INSERT IGNORE INTO `".PREFIX_SPEC."keyword2post` SET `pid`='{$post->id}',`tbl`='{$tbls->post}',`kid`='{$kid}'");
		db::query("UPDATE `post` SET `kid`='{$kid}' WHERE id='{$post->id}' LIMIT 1");
		#отвязываем от предыдущего
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2post` WHERE `pid`='{$post->id}' && `tbl`='{$tbls->post}' && `kid`='{$currentkid}'");
		#если кейворд ни к чему не привязан - удаляем его
		removeKeyword($currentkid);
	}
}
/*
	добавляет кейворды к посту
*/
function addnNewKeywords($post,$keywords){
	$tbls=\posts\tables::init();
	foreach ($keywords as $k) {
		$keywordSqlSave=db::escape($k);
		list($kid)=db::qrow("SELECT id FROM `keyword` WHERE `title`='{$keywordSqlSave}' LIMIT 1");
		$kid=empty($kid)?false:$kid;
		if(!$kid){
			db::query("INSERT INTO `keyword` SET `title`='{$keywordSqlSave}'");
			$kid=db::insert();
		}
		db::query("INSERT IGNORE INTO `".PREFIX_SPEC."keyword2post` SET `pid`='{$post->id}',`tbl`='{$tbls->post}',`kid`='{$kid}'");
	}
}
/*
	обрабатываем новые кейворды из текстовой формы
*/
function prepareKeywordsFromText($text){
	$keywords=explode("\n", $text);
	$keywords=array_filter($keywords,function($v){
		return !empty($v);
	});
	return $keywords;
}
/*
	отвязываем от кейвордов
*/
function detachKeywords($id,$keywords){
	if(empty($keywords['all'])) return;

	$tbls=\posts\tables::init();
	if(isset($keywords['selected'])){
		foreach ($keywords['selected'] as $v) {
			unset($keywords['all'][$v]);
		}
	}
	$unBind=$keywords['all'];
	if(!empty($unBind)){
		$unBindConcat=join(',',$unBind);
		#отвязываем посты
		db::query("DELETE FROM `".PREFIX_SPEC."keyword2post` WHERE `pid`='{$id}' && `tbl`='{$tbls->post}' && `kid` IN($unBindConcat)");
		#отвязываем картинки
		db::query("UPDATE `".PREFIX_SPEC."imgs` SET kid='' WHERE `tbl`='{$tbls->post}' && `pid`='{$id}' && `kid` IN($unBindConcat)");
		#удаляем кейвордsы
		removeKeyword($unBind);
	}
}
/*
	Определяет какие кейворды из списка следует удалить и удаляет кейворд
*/
function removeKeyword($kids){
	db::query("SELECT DISTINCT kid FROM `".PREFIX_SPEC."keyword2post` WHERE `kid` IN(".join(',',$kids).")");
	while ($d=db::fetch()) {
		unset($kids[$d->kid]);
	}
	if(!empty($kids))
		db::query("DELETE FROM `keyword` WHERE id IN(".join(',',$kids).")");
}
/*
	сохраняем связь с поста кейвордами
	вход: array(keywordID)
*/
function saveRelKeyword($kids,$pid){
	if(empty($kids)) return;
	$tbls=\posts\tables::init();
	foreach ($kids as $kid) {
		db::query("INSERT IGNORE INTO ".PREFIX_SPEC."keyword2post (kid,pid,tbl) VALUES ({$kid},{$pid},'{$tbls->post}')");
	}
}
function updateDates(){
	$count=db::qfetch("SELECT COUNT(*) as cn FROM post")->cn;
	$interval=4*7*24*3600/$count;
	$now=time();
	$q=db::query("select id from post");
	while($d=db::fetch($q)){
		@$i++;
		$date=date("Y-m-d H:i:s",$now-($count-$i)*$interval+mt_rand(0,$interval));
		$qq="UPDATE post SET datePublish='$date' WHERE id=$d->id";
		db::query($qq,1);
	}
	print "updateing posts done";
}
#получает список созданных префиксов для таблиц постов
function getPrefixList(){
	$prfx=array();
	db::query("SHOW TABLES WHERE `tables_in_".DB_NAME."` REGEXP '^[^\_]+\_post$'");
	while ($d=db::fetchRow()) {
		$prfx[]=preg_replace('!\_post$!i', '', $d);
	}
	return $prfx;
}
# подготавливает параметры поста для вывода в форму редактирования
function paramsPrettyPrint(&$json){
	$o=json_decode($json);
	if($o==NULL){
		$json='';
	}else{
		$json=json_encode($o,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		$json=preg_replace(array(
			'!^\{\s?(.+?)\s?\}$!s',#убираем внешние скобки
			'!^\s{4}!s',#убираем лишнее смещение слева первой строки
			'!\n\s{4}!',#убираем лишнее смещение слева
			'!"([^\"]+)"\:!',#убираем кавычки из ключей
			'!\:\s?"([^\"]+)"!',#убираем кавычки из значений
			'!,(\r*\n|$)!',#убираем запятые
			), 
			array('$1','',"\n",'$1:',': $1','$1'), $json
		);
	}
}
/*
 * Класс сохранения новости
 * requireFields - поля без которых нельзя сохранить новость
 * error - last error
 */
class Sv{
	public $requireFields;
	public $error;
	function __construct(){
		$this->error=false;
	}
	/*
		очистка текста от лишних данных
			- title
			- text
	*/
	function stripText(&$title,&$text,$editorlinks,$access){
		#Удаляем лишние из title
		$title=stripcslashes(html_entity_decode($title));
		$title=strip_tags($title);

		#заменяем неразрывные пробелы
		$text=str_replace('&nbsp;', ' ', $text);
		#заменяем знак % на код &#37;
		$text=str_replace('%', '&#37;', $text);
		
		$text=stripcslashes($text);
		$domain_approve=preg_replace(array('!^https?://!i','!^//!i','!^www.!i'), '', HREF);
		$domain_approve=preg_quote($domain_approve);
		# замена разрешенного домена на /
		$text=preg_replace('!((?:src|href)\=["\'])(?:https?\:)?//(?:[^/]+\.)?'.$domain_approve.'/*!si', '\1/',$text);
		# экранирование разрешенных картинок
		$text=preg_replace('!<\s*(img[^\>]+src\=[\'\"]/[^/][^\'\"]*[\'\"][^\>]+)>!si','%$1%',$text);
		# экранирование разрешенных ссылок
		$text=preg_replace('!<\s*(a[^\>]+href\=[\'\"]/[^/][^\'\"]*[\'\"][^\>]+)>(.+?)<\s*(/a)\s*>!si','%$1%$2%$3%',$text);
		# удаление скриптов, картинок
		$text=preg_replace('!<\s*(script).*?>.*?</\1>!si', '', $text);
		$text=preg_replace('!<\s*(script|img).*?>!si', '', $text);
		# удаление или обработка ссылок
		if(@$access->editorSaveLinks){
			if(isset($editorlinks['nofollow'])){
				# добавлене nofollow к внешним ссылкам
				# удаляем атрибут rel, если есть
				$text=preg_replace('!(<\s*a .*?) rel=[\'\"]?[^\'\"]*[\'\"]?(.*?>)!si', '$1 $2', $text);
				$text=preg_replace('!(<\s*a)( .*?>)!si', '$1 rel="nofollow"$2', $text);
			}elseif(!isset($editorlinks['save'])){
				# удаление ссылок, если ничего не выбрано
				$text=preg_replace('!<\s*(a).*?>.*?</\1>!si', '', $text);
				$text=preg_replace('!<\s*a.*?>!si', '', $text);
			}
		}else{
			# удаление ссылок, если нет прав
			$text=preg_replace('!<\s*(a).*?>.*?</\1>!si', '', $text);
			$text=preg_replace('!<\s*a.*?>!si', '', $text);
		}
		# обработка разрешенных картинок
		$text=preg_replace('!\%(img[^\%]+)\%!si','<$1>',$text);
		# обработка разрешенных ссылок
		$text=preg_replace('!\%(a[^\%]+)\%([^\%]+)\%(/a)\%!si','<$1>$2<$3>',$text);
	}
	#убирает pretty print из JSON объекта
	function checkParams(&$json){
		if($json=='') return;
		$o=json_decode($json);
		if(is_object($o)){
			$json=json_encode($o);
		}else{
			$json=str_replace('"', '&quot;', $json);#экранируем кавычки в тексте
			$json=preg_replace(array('!\:\s+?\{!m'),array(':{'),$json);
			$json=preg_replace(array(
				'!((?:^|^\s+)\s*)(.+?)\s?\:!m',#добавляем кавычки к ключам
				'!("[^\"]+"\s?\:\s*)([^\[\{\n].*?)(,?\r*\n|$)!m',#добавляем кавычки к значениям
				'!^(.+?)$!s',#добавляем внешние скобки
				'!([\}\]"])(\s*[\n]\s*)(")!s',#добавляем запятые
				), 
				array('$1"$2":','$1"$2"$3',"{\n$1\n}",'$1,$2$3'),
				$json
			);
			#print "<pre>";print $json;exit;
			$o=json_decode($json);
			$json=is_object($o)?json_encode($o):false;
		}
	}
	#Сохраняем новую новость или редактируем существующую
	function save($tbl,$post,$title,$text,$params,$cats,$pincid,$sources,$site,$foruser,$published,$keyword,$keywords,$new_keywords,$images,$theme){
		$id=isset($post->id)?$post->id:false;
		# все поля которые надо записать, кроме категорий
		$set=array(
			'title'=>db::escape($title),
			'txt'=>db::escape($text),
			'sources'=>$sources,
			'published'=>$published,
			'user'=>$foruser,
			'site'=>db::escape($site),
			'pincid'=>($pincid!==-1)?db::escape($pincid):'',
			'theme'=>db::escape($theme),
		);
		if($params!==false) $set['data']=db::escape($params);
		# обязательные поля
		foreach($this->requireFields as $rf){
			if(empty($set[$rf])) {$this->error="Require: {$rf}"; return false;}
			else{
				$sqlSet[]="`{$rf}`='{$set[$rf]}'";
				unset($set[$rf]);
			}
		}
		# поля не требующие проверки
		foreach ($set as $key => $val) {
			$sqlSet[]="`{$key}`='{$val}'";
		}
		if($published=='published') $sqlSet[]="`datePublish`=NOW()";
		elseif($published=='autoposting'){
			list($maxdate)=db::qrow("SELECT max(datePublish) from `$tbl`");
			list($per_day)=$qq=db::qrow("SELECT `value` FROM `".PREFIX_SPEC."config` where `key`='autopost_per_day'");
			if(empty($per_day))$per_day=1;
			$sqlSet[]="`datePublish`='".getDatePublish($per_day,strtotime($maxdate))."'";
		}

		$tblRel=PREFIX_SPEC."category2post";
		if($id){
			db::query("UPDATE `$tbl` SET ".implode(',',$sqlSet)." WHERE `id`='$id' LIMIT 1");
			# удаляем предыдущие записи о категориях
			list($url)=db::qrow("SELECT `url` FROM `$tbl` WHERE `id`='$id' LIMIT 1");
			db::query("DELETE FROM `{$tblRel}` WHERE `pid`='{$url}'");
			# отвязываем от кейвордов
			detachKeywords($id,$keywords);
			# обновляем кейворд у картинки
			if(!empty($images['keyword_updade'])&&!empty($images['text_update'])){
				$res=module::exec('images/admin',
					array(
						'act'=>'updateKeyword',
						'pid'=>$id,
						'keywords'=>$images['keyword_updade'],
						'texts'=>$images['text_update'],
						'tbl'=>'post',
					),'data')->data;
				# сохраняем связь с новыми кейвордами от картинки
				saveRelKeyword($res->newkids,$id);
			}
		}else{
			$url=getUrl($tbl,$title);
			$sqlSet[]="`url`='{$url}'";
			db::query("INSERT INTO `$tbl` SET ".implode(',',$sqlSet).", `date`=NOW()");
			$id=db::insert();
			$post->id=$id;
		}
		# записываем основной кейворд
		setKeyword($post,$keyword);
		# записываем дополнительные кейворды
		addnNewKeywords($post,$new_keywords);
		# записываем новые категории
		if(!empty($cats)&&!empty($id)){
			foreach ($cats as $val) {
				$sqlVal[]="('{$val}','{$url}')";
			}
			db::query("INSERT IGNORE INTO `{$tblRel}` (`cid`,`pid`) VALUES".implode(',',$sqlVal));
		}
		return $id;
	}
}
