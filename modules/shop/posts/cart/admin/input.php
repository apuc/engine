<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class shop_posts_cart_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		
		#Передаваемые переменные для каждого действия
		if($input->act=='install'&&$input->easy){
			$this->data=(object)array();
		}else
			$this->act=false;
	}
}