<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
*/
class posts extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		if(empty($input->act)) $input->act='post';
		if($input->act=='post'){
			$this->act=$input->act;
			$this->data=(object)array(
				'url'=>db::escape(urldecode($input->url)),
				'imgfromcookie'=>empty($_COOKIE['imagefile'])?false:$_COOKIE['imagefile'],
				'prfxtbl'=>!empty($input->prfxtbl)?$input->prfxtbl:'',
			);
		}elseif(@$input->act=='editPanel'){
			$this->act=$input->act;
			$this->data=(object)array(
				'post'=>empty($input->post)?0:$input->post,
			);
		}elseif($input->act=='rss'){
			$this->act=$input->act;
			$this->data=(object)array();
		}elseif($input->act=='socStat'){
			$this->act=$input->act;
			$this->data=(object)array(
				'id'=>$_POST['buttonID'],
				'like'=>(bool)$_POST['isLike'],
			);
		}
	}
}