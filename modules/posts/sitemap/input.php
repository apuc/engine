<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class posts_sitemap extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act))$input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array();
		}elseif($input->act=='sitemap'){
			$this->data=(object)array(
				'page'=>empty($input->page)?false:$input->page,
			);
		}
	}
}
