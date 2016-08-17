<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_stat extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'ref'=>empty($input->ref)?'':$input->ref,
				'uri'=>empty($input->uri)?'':$input->uri,
			);
		}elseif($input->act=='statGoogleBots'){
			$this->data=(object)array(
				'ua'=>empty($_SERVER['HTTP_USER_AGENT'])?'':$_SERVER['HTTP_USER_AGENT'],
				'uri'=>empty($_SERVER['REQUEST_URI'])?'':$_SERVER['REQUEST_URI'],
			);
		}else
			$this->act=false;
	}
}
