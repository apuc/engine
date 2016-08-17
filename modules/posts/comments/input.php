<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class posts_comments extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		$this->act=$input->act;
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'pid'=>(int)$input->pid,
			);
		}elseif($input->act=='comments'){
			$this->data=(object)array(
				'pid'=>(int)$input->pid,
			);
		}elseif($input->act=='commentsPage'){
			$this->data=(object)array(
				'url'=>$input->url,
				'page'=>@$input->page>0?(int)$input->page:1,
				'num'=>cookiePage($input,'num',10),
			);
		}elseif($input->act=='commentform'){
			$this->data=(object)array(
				'text'=>empty($input->text)?'':strip_tags($input->text),
			);
		}elseif($input->act=='save'){
			$this->data=(object)array(
				'pid'=>(int)$input->pid,
				'prfxtbl'=>db::escape($input->prfxtbl),
				'mail'=>db::escape($input->mail),
				'text'=>db::escape($input->text),
			);
		}elseif($input->act=='del'){
			$this->data=(object)array(
				'id'=>(int)@$input->id,
			);
		}else
			$this->act='';
	}
}
if(!function_exists('cookiePage')){
	function cookiePage($input,$key,$val){
		return !empty($input->$key)?db::escape($input->$key):(!empty($_COOKIE[$key])?db::escape($_COOKIE[$key]):$val);
	}
}
