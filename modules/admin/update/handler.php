<?php
namespace admin_update;
use module,db,url;

#Используем собственные функции
require_once('func.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->storage=TMP.__NAMESPACE__.'/version.txt';
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
	}
	/*
		вывод количества доступных обновлений
	*/
	function index($localcall=false){#Делаем все обработки для вывода данных
		if(!$localcall&&!$this->uhandler->rbac('update')) $this->headers->location=HREF;
		# check last version
		$point=0;
		if(file_exists($this->storage)){
			$lastVer=trim(file_get_contents($this->storage));
			foreach (getUpdateList(0) as $i=>$val) {
				if($val==$lastVer) {$point=$i+1; break;}
			}
		}else{
			@mkdir(dirname($this->storage)); touch($this->storage);
		}

		return (object)array(
			'count'=>count(getUpdateList($point)),
			'point'=>$point,
			'noPerms'=>!is_writable($this->storage)?dirname($this->storage):false,
			'storage'=>$this->storage,
		);
	}
	/*
		применение доступных обновлений
		фиксация текущей версии
	*/
	function applyUpdates($point){
		if(!$this->uhandler->rbac('update')||$point===false) die;
		$updates=getUpdateList($point);
		foreach ($updates as $func) {
			if(function_exists($func)) call_user_func($func);
		}
		# store last update
		if(isset($func))
			file_put_contents($this->storage, $func);
		return (object)array('html'=>'done');
	}
}
function getUpdateList($point){
	$updates=array(); $i=1;
	while (function_exists($funcName="update{$i}")) {
		$updates[]=$funcName; $i++;
	}
	return array_slice($updates, $point);
}
