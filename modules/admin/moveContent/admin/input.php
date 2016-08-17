<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_moveContent_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		
		#Передаваемые переменные для каждого действия
		if($input->act=='install'){
			$this->data=(object)array();
		}
	}
}
