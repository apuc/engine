<?php
namespace index;
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
		$this->userControl=module::exec('user',array(),1)->handler;
	}
	function index($page,$num){#Делаем все обработки для вывода данных
		$data=module::exec('posts/lists',array('act'=>'mainList','page'=>$page,'num'=>$num),'data')->data;
		$data->seealso=\posts\randomPosts(8);
		return $data;
	}
}
