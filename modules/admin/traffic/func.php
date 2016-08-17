<?
namespace admin_traffic;
use module,db,url,cache;
/*
	определяет запущен ли процесс по пути к исполняемому файлу
*/
function processRunning($pname){
	$ps=shell_exec($t="ps aux|grep ".escapeshellarg($pname)."|grep -v ' grep '");
	if(empty($ps))
		return 0;
	else{
		$running=explode("\n", trim($ps));
		return (count($running));
	}
}
function mkSQLorder($sort){
	return (!empty($sort[0]))?" ORDER BY `".db::escape($sort[0])."` ".db::escape($sort[1]):'';
}
/*
	формирует список конкурентов
*/
function serpRivals($date){
	$self=preg_replace('!^www\.!', '', parse_url(HREF,PHP_URL_HOST));
	db::query(
		"SELECT repl FROM `".PREFIX_SPEC."grepl` 
		WHERE `date`='".db::escape($date)."'
		GROUP BY `keyword`
	");
	$rivals=array();
	while ($d=db::fetch()) {
		$repl=unserialize($d->repl);
		foreach ($repl as $v) {
			$host=preg_replace('!^www\.!', '', parse_url($v->ref,PHP_URL_HOST));
			if($host==$self) continue;
			if(!isset($rivals[$host])) $rivals[$host]=0;
			$rivals[$host]++;
		}
	}
	arsort($rivals);
	$count=count($rivals);

	return (object)array('data'=>array_slice($rivals, 0, 1000, true),'count'=>$count);
}
/*
	проверяет наличие подкатегорий
*/
function checkCatChilds($cids){
	$childs=array();
	db::query("SELECT parentId,COUNT(id) AS c FROM `category` WHERE parentId IN('".implode("','", $cids)."') GROUP BY `parentId`");
	while ($d=db::fetch()) {
		$childs[$d->parentId]=$d->c;
	}
	return $childs;
}