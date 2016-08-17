 <?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
*/
class shop_index extends control{
	function __construct($input=''){ #$input - объект входных переменных от других модулей
		if(empty($input->act)) $input->act='index';
		$this->act=$input->act;
		if($input->act=='index'){
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',20),
			);
		}
	}
}

