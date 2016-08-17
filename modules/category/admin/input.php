<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class category_admin extends control{
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
					'itemUrl'=>!isset($_POST['itemUrl'])?false:db::escape($_POST['itemUrl']),
					'prfxtbl'=>!isset($_POST['prfxtbl'])?'':$_POST['prfxtbl'],
					'merge'=>!isset($_POST['merge'])?false:db::escape($_POST['merge']),
					'name'=>empty($_POST['name'])?'':db::escape($_POST['name']),
					'view'=>@$_POST['view']=='on'?1:0,
					'subCatList'=>empty($_POST['subCatList'])?0:(int)$_POST['subCatList'],
					'theme'=>empty($_POST['theme'])?'':db::escape($_POST['theme']),
				);
			}else{
				#вывод формы
				$this->data=(object)array(
					'parentID'=>empty($input->parentID)?'':$input->parentID,
					'itemID'=>empty($input->itemID)?'':$input->itemID,
					'prfxtbl'=>!isset($_POST['prfxtbl'])?'':$_POST['prfxtbl'],
				);
			}
		}elseif($input->act=='structSave'&&$input->easy){
			# для вызова сохранения категорий из других модулей
			$this->data=(object)array(
				'parentID'=>empty($input->parentID)?'':db::escape($input->parentID),
				'name'=>db::escape(@$input->name),
				'view'=>$input->view=='on'?1:0,
			);
		}elseif($input->act=='del'){
			$this->data=(object)array(
				'itemID'=>empty($input->itemID)?0:(int)$input->itemID,
				'prfxtbl'=>!isset($input->prfxtbl)?'':$input->prfxtbl,
			);
		}
	}
}

