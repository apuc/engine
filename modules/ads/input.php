<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class ads extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		#Список разрешенных действий
		if(in_array(@$input->act,array('index'))){
			$this->act=$input->act;
		}
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'channel'=>@$input->page,
			);
		}
	}
}
