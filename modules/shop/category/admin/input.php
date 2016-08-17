<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class shop_category_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='')$input->act='index';
		$this->act=$input->act;
		
		#Передаваемые переменные для каждого действия
		if($input->act=='funcPanel'){
			$this->data=(object)array(
				'itemID'=>empty($input->itemID)?0:(int)$input->itemID,
				'itemUrl'=>empty($input->itemUrl)?'':db::escape($input->itemUrl),
			);
		}elseif($input->act=='edit'){
			if(!$input->easy&&isset($_POST['edit'])){
				# обработка POST из формы
				$this->act='save';
				$this->data=(object)array(
					'parentID'=>empty($_POST['parentID'])?'':db::escape($_POST['parentID']),
					'itemID'=>empty($_POST['itemID'])?'':$_POST['itemID'],
					'name'=>empty($_POST['name'])?'':db::escape($_POST['name']),
					'view'=>@$_POST['view']=='on'?1:0,
				);
			}else{
				#вывод формы
				$this->data=(object)array(
					'parentID'=>empty($input->parentID)?'':$input->parentID,
					'itemID'=>empty($input->itemID)?'':$input->itemID,
				);
			}
		}elseif($input->act=='del'){
			$this->data=(object)array(
				'itemID'=>empty($input->itemID)?0:(int)$input->itemID,
			);
		}
	}
}

