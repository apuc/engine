<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class shop_posts_cart extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='cart';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='tocart'){
			$this->data=(object)array(
				'gid'=>empty($input->gid)?false:$input->gid,
				'q'=>empty($input->q)?1:$input->q,
				'increment'=>empty($input->increment)?false:$input->increment,
				'cookieCart'=>empty($_COOKIE['cookieCart'])?false:(int)$_COOKIE['cookieCart'],
			);
		}elseif($input->act=='outcart'){
			$this->data=(object)array(
				'gid'=>empty($input->gid)?false:$input->gid,
				'cookieCart'=>empty($_COOKIE['cookieCart'])?false:(int)$_COOKIE['cookieCart'],
			);
		}elseif($input->act=='cart'){
			$this->data=(object)array(
				'cookieCart'=>empty($_COOKIE['cookieCart'])?false:(int)$_COOKIE['cookieCart'],
			);
		}else
			$this->act='';
	}
}