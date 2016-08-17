<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_keywords extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		
		$this->act=$input->act;
		#Передаваемые переменные для каждого действия
		if($input->act=='postsList'){
			$this->data=(object)array(
				'page'=>@$input->page>1?(int)$input->page:1,
				'num'=>empty($input->num)?10:(int)$input->num,
				'sort'=>empty($input->sort)?false:$input->sort,
			);
		}elseif($input->act=='keywordsList'){
			$this->data=(object)array(
				'page'=>@$input->page>1?(int)$input->page:1,
				'num'=>empty($input->num)?10:(int)$input->num,
				'sort'=>empty($input->sort)?false:$input->sort,
			);
		}elseif($input->act=='initParsing'){
			$this->data=(object)array(
				'kidpids'=>empty($input->kidpids)?null:$input->kidpids,
				'nested'=>empty($input->nested)?0:$input->nested,
				'addword'=>empty($input->addword)?'':$input->addword,
			);
		}elseif($input->act=='showLog'){
			$this->data=(object)array(
				'log'=>empty($input->log)?false:$input->log,
				'tail'=>empty($input->tail)?0:(int)$input->tail,
			);
		}elseif($input->act=='stopDaemons'){
			$this->data=(object)array();
		}
	}
}