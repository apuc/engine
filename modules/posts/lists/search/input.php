<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class posts_lists_search extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		if(@$input->act=='')$input->act='index';
		$this->act=$input->act;
		if($input->act=='index'){
			$this->data=(object)array(
				'query'=>!empty($input->q)?urldecode($input->q):false,
				'prfxtbl'=>!empty($input->prfxtbl)?$input->prfxtbl:'',
				'page'=>!empty($input->page)?(int)$input->page:1,
				'num'=>cookiePage($input,'num',20),
			);
		}
	}
}