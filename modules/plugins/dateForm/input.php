<?php
 /*
  * Должен возвращать
  * $this->data - объект обработанных входных переменных
  * $this->act - какую функцию обработки используем
  */
class plugins_dateForm extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->act=$input->act='index';
		$this->data=(object)array(
			'start'=>!empty($input->start)?$input->start:date("Y-m-d"),
			'stop'=>!empty($input->stop)?$input->stop:date("Y-m-d"),
			'params'=>!empty($input->params)?$input->params:array(),
			'switchType'=>!isset($input->switchType)?1:(int)$input->switchType,
		);
	}
}
