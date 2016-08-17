<?php
namespace admin_install;
use module,db,url;

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
	}

	/*
	 * Выполняет функции install нужных модулей
	 */
	function index($installModules){
		$ready=array();
		$message=array();
		$tables=db::qall("SHOW TABLES");
		if($installModules){
			if(!$tables){
				foreach ($installModules as $name) {
					module::exec($name,array('act'=>'install'),1);
				}
				$message[]='Installed';
				#устанавливаем базовую тему
				$m=module::exec('admin/themes',array('act'=>'genBaseTheme'),'data')->data->message;
				#записываем последнюю версию
				$updates=module::exec('admin/update',array('act'=>'index'),'data')->data;
				$updatesLists=\admin_update\getUpdateList(0);
				file_put_contents($updates->storage, end($updatesLists));
				$message[]=$m=='done'?"ThemeInstalled":"Theme - {$m}";
			}else{
				$message[]='Error: DB is not empty';
			}
		}else
			if($tables) $this->headers->location=HREF;
			else{
				$ready=readyToInstall(module::$path);
			}

		$status='';
		$status.=module::exec('images/admin',array('act'=>'status'),1)->str;
		$status.=module::exec('admin/themes',array('act'=>'status'),1)->str;
		$status.=module::exec('admin/install',array('act'=>'status'),1)->str;

		return (object)array(
			'ready'=>$ready,
			'message'=>$message,
			'status'=>$status,
		);
	}
	function status(){
		return (object)array('writable'=>is_writable(TMP));
	}
}
/*
	получает список готовых к установке модулей
*/
function readyToInstall($basedir){
	static $ready;
	if(!isset($ready)) $ready=array();
	foreach (scandir($basedir) as $dirName) {
		if(substr($dirName,0,1)=='.'||in_array($dirName,array('files','tmp','tpl'))) continue;
		if(!is_dir($tdir="$basedir/$dirName")) continue;
		#собираем все admin/handler.php
		if($dirName=='admin'){
			if(file_exists("{$tdir}/handler.php")){
				$ready[]=str_replace(module::$path.'/','',$tdir);
				continue;
			}
		}
		readyToInstall($tdir,true);
	}
	return $ready;
}
