<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class category extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			$this->data=(object)array(
				'url'=>empty($input->url)?'':$input->url,
				'tbl'=>empty($input->tbl)?'post':$input->tbl,
			);
		}elseif($input->act=='mlist'){
			$this->data=(object)array('tbl'=>empty($input->tbl)?'post':$input->tbl,'category'=>empty($input->category)?null:$input->category,'parentId'=>empty($input->parentId)?null:$input->parentId);
		}elseif($input->act=='subList'){
			$this->data=(object)array(
				'url'=>empty($input->url)?'':$input->url,
				'relTbl'=>empty($input->tbl)?'post':$input->tbl,
			);
		}elseif($input->act=='ajaxCategories'){
			$this->data=(object)array(
				'query'=>!empty($_GET['query'])?$_GET['query']:false,
				'prfxtbl'=>!empty($_GET['prfxtbl'])?$_GET['prfxtbl']:''
			);
		}elseif($input->act=='updateCount'&&($input->easy||$input->act==$_POST['act'])){
			$this->data=(object)array(
				'cats'=>empty($input->cats)?false:$input->cats,
				'prfxtbl'=>empty($input->prfxtbl)?'':$input->prfxtbl,
			);
		}
	}
}

if(!function_exists('cookiePage')){
	function cookiePage($input,$key,$val){
		return !empty($input->$key)?db::escape($input->$key):(!empty($_COOKIE[$key])?db::escape($_COOKIE[$key]):$val);
	}
}

