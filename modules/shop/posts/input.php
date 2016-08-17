<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
*/
class shop_posts extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		if(empty($input->act)) $input->act='post';
		if($input->act=='post'){
			$this->act=$input->act;
			$this->data=(object)array(
				'url'=>db::escape(urldecode($input->url)),
				'imgfromcookie'=>empty($_COOKIE['imagefile'])?false:$_COOKIE['imagefile'],
			);
		}elseif(@$input->act=='editPanel'){
			$this->act=$input->act;
			$this->data=(object)array(
				'post'=>empty($input->post)?0:$input->post,
			);
		}
	}
}


