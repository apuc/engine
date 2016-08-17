<?php
namespace plugins_dateForm;
use module,db;

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function index($start,$stop,$params,$switchType){#Делаем все обработки для вывода данных
		@$this->headers->cookie->start=array($start,'');
		@$this->headers->cookie->stop=array($stop,'');
		return (object)array(
			'start'=>$start,
			'stop'=>$stop,
			'params'=>$params,
			'switchType'=>$switchType, // Возможность запустить в режиме одной даты
		);
	}
}
