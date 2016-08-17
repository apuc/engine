<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_traffic_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		
		/*
			определяет список доступных действий (act)
			если запрошенного act нет в спсике, то будет вызван метод совпадающий с названием данного класса
		*/
		if($input->act=='install'){
			$this->data=(object)array();
		}else
			$this->act=false;
	}
}