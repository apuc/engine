<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class admin_loadstruct extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=new stdClass;
		if(empty($input->act)) $input->act='loadStruct';
		
		/*
			определяет список доступных действий (act)
			если запрошенного act нет в спсике, то будет вызван метод совпадающий с названием данного класса
		*/
		if($input->act=='loadStruct'){
			$this->data=(object)array(
				'file'=>!empty($_FILES['structfile']['tmp_name'])?$_FILES['structfile']:false,
				'ftpfile'=>empty($_POST['ftpstructfile'])?false:$_POST['ftpstructfile'],
				'repeatKeys'=>@$_POST['repeatKeys'],
				'tblprefix'=>!empty($_POST['tblprefix'])?$_POST['tblprefix']:'',
				'kwastitle'=>!empty($_POST['kwastitle'])?$_POST['kwastitle']:false,
				'autoposting'=>!empty($_POST['autoposting'])?$_POST['autoposting']:false,
			);
		}else
			$this->act=false;
	}
}