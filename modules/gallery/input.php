<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class gallery extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='') $input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){
			if(@$_COOKIE['mobile']==1&&!$input->easy){
				#смена шаблона вывода
				$this->act='mobile';
			}
			$this->data=(object)array(
				'tbl'=>empty($input->tbl)?'':db::escape($input->tbl),
				'pid'=>empty($input->pid)?0:(int)$input->pid,
				'url'=>db::escape(urldecode($input->url)),
				'overlay'=>isset($input->overlay)?(bool)$input->overlay:false,
			);
		}elseif($input->act=='overlayGallery'){
			$this->data=(object)array(
				'tbl'=>db::escape($input->tbl),
				'pid'=>(int)$input->pid,
				'url'=>db::escape(urldecode($input->url))
			);
		}elseif($input->act=='overlayAds'){
			$this->data=(object)array(
				'type'=>db::escape($input->type),
			);
		}elseif($input->act=='imgResolution'){
			$this->data=(object)array(
				'src'=>!empty($input->src)?$input->src:false,
				'x'=>!empty($input->x)?$input->x:false,
				'y'=>!empty($input->y)?$input->y:false,
			);
		}else
			$this->act='';
	}
}
