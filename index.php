<?php
set_time_limit(5);
include('core/func.php');
#load config
include('core/config.php');
include('core/core.config.php');
#load libraries
include(PATH."core/module.class.php");
include(PATH."core/db.class.php");
include(PATH."core/route.class.php");
include(PATH."core/cache.class.php");
include(PATH."core/theme.class.php");
timer(0);#Старт таймера
/*
	Создание нужных объектов и инициализация нужных классов
*/
#Подключение к ДБ
new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
#назначаем тему
$theme=new theme();
#включаем роутинг
route::load($theme->path);

if(!defined('SITEOFF')){	
	#передаем задачу модулю
	if(!isset($_GET['module'])){
		$router=route::get($_SERVER['REQUEST_URI']);
		#print_r(route::$routeList);
		#print_r($router); die;
		$themePost=$theme->tracking($router->module,$router->params);
		if($themePost!==false){
			#пересоздаем объект тем и доподключаем роутинг для темы поста
			$theme=new theme($themePost);
			route::load($theme->path);			
		}
		$m=$router->module;
		$p=$router->params;
	}else{
		$m=$_GET['module'];
		$p=array();
	}
	$theme->setconsts();
	$module=module::exec($m,$p);

	if($module===false) location("/");
	#вывод результатов
	print $module->str;
	flush();
	
	register_shutdown_function(function(){
		#статистика ботов
		module::exec('admin/stat',array('act'=>'statGoogleBots'),'data');
		#запись статистики трафика
		global $router;
		if(TRAFFICSTAT_ENABLE&&isset($router->module)){
			module::exec('admin/traffic',array('act'=>'write','module'=>$router->module),'data');
		}
		#лог скорости работы
		global $m;
		if(SPEEDLOG_ENABLE)
			module::exec('admin/speedlog',array('act'=>'write','moduleName'=>$m),'data');
	});
}
