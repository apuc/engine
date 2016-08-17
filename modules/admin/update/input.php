<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_update extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		
		#Передаваемые переменные для каждого действия
		if($input->act=='index'){			
			$this->data=(object)array('localcall'=>$input->easy==='data'?true:false);
		}elseif($input->act=='applyUpdates'){
			$this->data=(object)array(
				'point'=>isset($_POST['point'])?(int)$_POST['point']:false
			);
		}
	}
}
