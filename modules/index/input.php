 <?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
*/
class index extends control{
	function __construct($input=''){ #$input - объект входных переменных от других модулей
		$this->data=(object)array(
			'page'=>empty($input->page)?1:$input->page,
			'num'=>cookiePage($input,'num',20),
		);
	}
}