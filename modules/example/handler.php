<?php
namespace example;
use module,db,url,cache;
#Используем сторонние модули
#require_once(module::$path.'/[module_name]/[file_name].php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 * $this->cache - array(
 		[methodName1]=>array(
 			'id', #uniq key - (int), (string) or as array of (int), (string)
 			'10 min' #expire
 		)
 		...
 	) - определяет список методов для кэширования
 */
class handler{
	#enable cache for selected methods
	public $cache=array(
		#'method'=>array(array(id1,id2),expire)
		'post'=>array(array('url','another-input-param'),'10 min'),
		#'method'=>array(id,expire)
		'index'=>array('url','1 day'),
	);
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=new \stdClass;
	}
	function index(){#Делаем все обработки для вывода данных
		return (object)array(
			'test'=>1
		);
	}
}
