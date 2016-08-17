<?php
/*
	- удаляет старую статистику (позднее 1 мес.)
	- удаляет скачанные файлы google image serp позднее 3-х дней
	- вызывается в cron
	- может быть вызван вручную
*/
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../../index.php';#подключаем движок
error_reporting(-1); ini_set('display_errors', 'On');
set_time_limit(0);
define('TRAFFIC_TMP',TMP.'admin_traffic');
define('XDATE',date('Y-m-d',strtotime('-1 month')));
define('XDATE_STAMP',strtotime(XDATE));
define('TMP_TRAFFIC_DIR', TRAFFIC_TMP."/stat");

rmTrafficArchive();
rmReplStat();
rmSerpFiles();

/*
	удаляет старые данные по трафику из TMP_TRAFFIC_DIR
*/
function rmTrafficArchive(){
	$dh=opendir(TMP_TRAFFIC_DIR);
	while ($file=readdir($dh)) {
		if($file=='.'||$file=='..') continue;
		$date=preg_replace('!\.txt$!si', '', $file);
		$datestamp=strtotime($date);
		if($datestamp<XDATE_STAMP) {
			unlink(TMP_TRAFFIC_DIR."/{$file}");
		}
	}
	closedir($dh);
}
/*
	удаляет старые данные по замене
*/
function rmReplStat(){
	db::query("DELETE FROM `".PREFIX_SPEC."grepl` WHERE `date`<'".XDATE."'",1);
	db::query("DELETE FROM `".PREFIX_SPEC."greplSum` WHERE `date`<'".XDATE."'",1);
	db::query("DELETE FROM `".PREFIX_SPEC."greplSumCats` WHERE `date`<'".XDATE."'",1);
}
/*
	удаляет старые скачанные страницы выдачи
*/
function rmSerpFiles(){
	$dh=opendir(TRAFFIC_TMP);
	while ($file=readdir($dh)) {
		if($file=='.'||$file=='..'||!is_dir($fullpath=TRAFFIC_TMP."/{$file}")) continue;
		if(preg_match('!gglreplparse_(\d{4}-\d{2}-\d{2})\.tmp!', $file, $m)){
			$datestamp=strtotime($m[1]);
			if($datestamp<strtotime("-3 days")) {
				unlink($fullpath);
			}
		}
	}
	closedir($dh);
}
?>