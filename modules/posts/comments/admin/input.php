<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class posts_comments_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'type'=>!empty($input->type)?(int)$input->type:1,
			);
		}elseif($input->act=='save'){
			#Сохраняем данные
			$this->data=(object)array(
				'com'=>@$input->com,
				'seen'=>@$input->seen,
				'type'=>(int)@$input->type,
			);
		}else
			$this->act='';
	}
}
