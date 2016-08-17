<?
/*
	Конфигурация глобальных констант которые не нужны в git
	1. переменная HREF - название хоста
	2. переменные модулей, например различные статичные пароли
*/
error_reporting(0);ini_set('display_errors','Off');

define('HREF',"http://engine3.loc");
define('NAME',"BLOG");

/*
#Facebook, please setup as meta tags
define('FACEBOOKPAGE','malecars');
define('FACEBOOKAPPID','null');
define('FACEBOOKLANG','en_US');#ru_RU
*/

#Данные БД
define('DB_HOST','localhost');
define('DB_USER','');
define('DB_PASS','');
define('DB_NAME','');

#module moveContent
define('SRC_IMG_HREF','');# http://[domain]/images/

define('SRC_DB_HOST','');
define('SRC_DB_USER','');
define('SRC_DB_PASS','');
define('SRC_DB_NAME','');

#включить лог скорости обработки страниц
#define('SPEEDLOG_ENABLE',1);

#включить статистику трафика
#define('TRAFFICSTAT_ENABLE',1);

#default theme
#define('THEME_SET','themename');
#Выключает возможность редактировать тему одного поста или категории
#define('THEME_POST_ALLOW',0);

#авторизация через соц. сети
# facebook
#define('AUTH_FACEBOOK_CLIENT_ID', '474178579406077');
#define('AUTH_FACEBOOK_CLIENT_SECRET', '349d54c0d6e67a70bee11716de15ea63');
# google
#define('AUTH_GOOGLE_CLIENT_ID','733717023843-mgfb3gr71q3as4srcepf34d9bjsb73a0.apps.googleusercontent.com');
#define('AUTH_GOOGLE_CLIENT_SECRET','DyCQu5E0KAXmwb5Qragj_8zK');
# twitter
#define('AUTH_TWITTER_CONSUMER_KEY','2t0Ocm2psN77WxgRz0aIRPs0R');
#define('AUTH_TWITTER_CONSUMER_SECRET','AAWo4ypJXO74RCPEzJrlPnMlBcBZWg62L0ve4pyrZDQ7UDB3uk');

#отображение данных по кэшу в виде HTML комментариев
#define('CACHE_DEBUG',0);


