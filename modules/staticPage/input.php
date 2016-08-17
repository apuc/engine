<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class staticPage extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		if($input->act=='index'){
			$this->data=(object)array(
				'url'=>!empty($input->url)?$input->url:false,
			);
		}else
			$this->act=false;
	}
}