<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_multiPost extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		
		#Список разрешенных действий
		if(in_array(@$input->act,array('index','save'))){
			$this->act=$input->act;
		}
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'page'=>@$input->page>1?(int)$input->page:1,
				'num'=>empty($input->num)?10:(int)$input->num,
				"sort"=>@$input->order,
			);
		}
		if($input->act=='save'){
			$this->data=(object)array(
				'pids'=>@$input->pids,
			);
		}
		if($input->act=='status'){
			$this->data=(object)array(
				'ajax'=>@$input->ajax,
			);
		}
	}
}

#Получаем данные из куков
if(!function_exists('cookiePage')){
	function cookiePage($input,$key,$val){
		return !empty($input->$key)?db::escape($input->$key):(!empty($_COOKIE[$key])?db::escape($_COOKIE[$key]):$val);
	}
}
