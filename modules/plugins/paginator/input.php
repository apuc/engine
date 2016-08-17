<?php
 /*
  * Должен возвращать
  * $this->data - объект обработанных входных переменных
  * $this->act - какую функцию обработки используем
  */
class plugins_paginator extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		if(in_array($input->act,array('index','typesmall'))){
			$this->act=$input->act;
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
				'count'=>$input->count,
				'uri'=>$input->uri,
				'showPagen'=>empty($input->showPagen)?false:$input->showPagen,
				'setnum'=>empty($_GET['num'])?false:(int)$_GET['num'],
			);
		}
	}
}
