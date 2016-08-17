<?php
/*
 * Должен возвращать
 * $this->data - объект обработанных входных переменных
 * $this->act - какую функцию обработки используем
 */

class admin_install extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='') $input->act='index';

		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'installModules'=>empty($_POST['installModules'])?false:$_POST['installModules'],
			);
		}elseif($input->act=='status'){
			$this->data=(object)array();
		}else
			$input->act=false;
	}
}
