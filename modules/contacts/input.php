<?php
 /*
  * Должен возвращать
  * $this->data - объект обработанных входных переменных
  * $this->act - какую функцию обработки используем
  */
 class contacts extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		session_start();
		$this->data=(object)array(
			'save'=>(bool)@$_POST['send'],
			'ssecret'=>@$_SESSION['secret'],
			'secret'=>@$_POST['secret'],
			'mail'=>@$_POST['mail'],
			'text'=>@$_POST['text'],
			'host'=>$_SERVER['HTTP_HOST']
		);
		if(@$input->act=='')$input->act='index';
		if(in_array($input->act,array('index','save','capcha'))){
			$this->act=$input->act;
		}
	}
 }
