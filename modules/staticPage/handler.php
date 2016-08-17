<?php
namespace staticPage;
use module,db,url,cache;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function index($url){#Делаем все обработки для вывода данных
		$url=urldecode($url);
		$file=__DIR__."/tpl/index/{$url}.php";
		if(!$url||strstr($url, '/')||!file_exists($file)){
			$this->headers->location=HREF;
			return;
		}
		return (object)array('file'=>"{$url}.php");
	}
}
