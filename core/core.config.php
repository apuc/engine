<?
#date_default_timezone_set('America/Los_Angeles');

#Урл и пути
if(!defined('HREF')) die('HOST empty!');
$purl=parse_url(HREF);
if(!empty($_SERVER['HTTP_HOST'])){
	if($_SERVER['HTTP_HOST']!=$purl['host']){
		location("http://".$purl['host']."$_SERVER[REQUEST_URI]"); die;
	}
}
define('SITE',!empty($purl)?$purl['host']:'');
define('COOKIE_HOST','.'.preg_replace("!^www\.!",'',SITE));
define('PATH',dirname(__DIR__).'/');
define('TMP',PATH.'tmp/');

#Префиксы таблиц по задачам
define('PREFIX_SPEC','zspec_');
define('PREFIX_CON','zcon_');
define('PREFIX_LIKE','zlike_');
define('PREFIX_TMP','ztmp_');

#значения по умолчанию для некоторых констант, если не заданы в config.php
defined('TRAFFICSTAT_ENABLE') or define('TRAFFICSTAT_ENABLE',0);
defined('SPEEDLOG_ENABLE') or define('SPEEDLOG_ENABLE',0);

#глобальная переменная для хранения шаблонов URL страниц
$urls=(object)array();

mb_internal_encoding('utf8');
