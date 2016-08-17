<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class user_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=new stdClass;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='install'){
			$this->data=(object)array();
		}elseif($input->act=='allUsers'){
			$this->data=(object)array(
				'users'=>@$_POST['users'],
			);
		}
	}
}
