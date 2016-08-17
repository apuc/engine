<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class example extends control{
	function __construct($input){# $input - объект входных переменных от других модулей
		$this->data=new stdClass;
		if(empty($input->act)) $input->act='index';
		
		/*
			определяет список доступных действий (act)
			если запрошенного act нет в спсике, то будет вызван метод совпадающий с названием данного класса
		*/
		if($input->act=='index'){
			$this->data=(object)array(
				'test'=>'',
			);
		}else
			$this->act=false;
	}
}