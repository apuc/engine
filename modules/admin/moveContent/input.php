<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_moveContent extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='form';

		#Список разрешенных действий
		if(in_array(@$input->act,array('form','process','downloadImage', 'autoPosting', 'autoPostingCheck'))){
			$this->act=$input->act;
		}

		#Передаваемые переменные для каждого действия
		if($input->act=='form'){
			$count=(isset($input->count)) ? max((int)$input->count, 1) : 100;
			$this->data=(object)array(
				'count'=>$count,
				'site'=>empty($_POST['siteForCopying'])?'':db::escape($_POST['siteForCopying']),
			);
		}elseif($input->act=='process'){
			$pid=array();
			if(is_array(@$input->pid)){
				foreach($input->pid as $id){
					if(is_string($id) && ctype_digit($id)) $pid[]=$id;
				}
			}
			$this->data=(object)array(
				'pid'=>$pid,
			);
		}elseif($input->act=='downloadImage'){
			$this->data=(object)array(
				'url'=>@$input->url,
				'link'=>@$input->link,
			);
		}elseif($input->act=='autoPosting'){
			$this->data=(object)array(
				'submit'=>isset($_POST['autopost']),
				'per_day'=>empty($_POST['count_per_day'])?5:(int)$_POST['count_per_day'],
				'at_once'=>!isset($_POST['count_at_once'])?10:(int)$_POST['count_at_once'],
			);
		}
	}
}

