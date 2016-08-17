<?php
namespace shop_category_admin;
use module,db,url,StdClass;

#Используем собственные функции
#require_once(PATH."modules/example/func.php");

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->userControl=module::exec('user',array(),1)->handler;
	}
	/*
		1. данные для вывода формы редактирования категории
	*/
	function edit($parentID,$itemID){
		if(!$this->userControl->rbac('editCat')) die('forbidden');
		# получаем данные самого элемента
		if($itemID){
			$item=db::qfetch("SELECT * FROM `shop_category` WHERE `id`='{$itemID}' LIMIT 1");
			$parentID=$item->parentId;
		}else{
			$item = new StdClass;
			$item->id=$itemID;
		}
		# получаем данные родителя
		if($parentID){
			$parent=db::qfetch("SELECT * FROM `shop_category` WHERE `url`='{$parentID}' LIMIT 1");
		}else{
			$parent = new StdClass;
			$parent->url='';
			$parent->title='Index';
		}
		return (object)array(
			'parent'=>$parent,
			'item'=>$item,
		);
	}
	/*
		1. сохранение категории
		2. подключает вывод формы редактирования
	*/
	function save($parentID,$itemID,$name,$view){
		if(!$this->userControl->rbac('editCat')) die('forbidden');
		$mesSuccess=$mesWarning='';
		if($itemID){
			#edit
			db::query("UPDATE `shop_category` SET `title`='{$name}',`view`='{$view}'
				WHERE `id`='{$itemID}' LIMIT 1");
			if(mysql_affected_rows())
				$mesSuccess='Update successful';
			else
				$mesWarning='Update failed';
		}else{
			#add
			list($exists)=db::qrow("SELECT `id` FROM `shop_category` WHERE `title`='{$name}' LIMIT 1");
			if(!$exists){
				db::query("INSERT INTO `shop_category` (`url`,`title`,`parentId`,`view`) 
					VALUES('".getUrl('category',$name)."','{$name}','{$parentID}','{$view}')",1);
				if($itemID=db::insert())
					$mesSuccess='Insert successful';
				else
					$mesWarning='Insert failed';
			}else{
				$mesWarning='Name already exists';
			}
		}
		return (object)array(
			'success'=>$mesSuccess,
			'warning'=>$mesWarning,
			'html'=>module::exec('shop/category/admin',
				array('act'=>'edit','parentID'=>$parentID,'itemID'=>$itemID),1)->str,
		);
	}
	/*
		1. удаление категории
	*/
	function del($itemID){
		if(!$this->userControl->rbac('delCat')) die('forbidden');
		if($itemID){
			list($url,$parentID)=db::qrow("SELECT `url`,`parentId` FROM `shop_category` WHERE `id`='{$itemID}' LIMIT 1");
			db::query("DELETE FROM `shop_category` WHERE `id`='{$itemID}' LIMIT 1");
			# удаление связи с постами
			delRel('category','post',$url);
		}
		$this->headers->location=!empty($parentID)?url::category($parentID):HREF;
		return (object)array();
	}
	/*
		вывод панели действй (удалить/редактировать и тд.)
			- включает проверку прав на эти действия
	*/
	function funcPanel($itemID,$itemUrl){
		$accessEdit=$this->userControl->rbac('editCat');
		$accessDel=$this->userControl->rbac('delCat');
		return (object)array('itemID'=>$itemID,'itemUrl'=>$itemUrl,'accessEdit'=>$accessEdit,'accessDel'=>$accessDel);
	}

	function install(){
		db::query("CREATE TABLE IF NOT EXISTS `shop_category` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `url` varchar(125) NOT NULL,
		  `title` varchar(255) NOT NULL,
		  `parentId` varchar(255) NOT NULL,
		  `count` int(11) NOT NULL COMMENT 'cache count',
		  `countAdmin` int(11) NOT NULL COMMENT 'cache admin count',
		  `view` int(11) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `url` (`url`),
		  KEY `parentId` (`parentId`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."category2shop_post` (
		  `cid` varchar(125) NOT NULL COMMENT 'category ID',
		  `pid` varchar(125) NOT NULL COMMENT 'post ID',
		  UNIQUE KEY `cid_2` (`cid`,`pid`),
		  KEY `cid` (`cid`),
		  KEY `pid` (`pid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		return array('instaled');
	}
}

/*
	1. преобразование title в URL
*/
function key2url($title){#получить урл по названию
	return preg_replace(array('!(\s+|\/)!','![^\w\d\-\_\.]!iu'),array('-',''),strtolower(trim($title)));
}
/*
	Получает уникальный URL для таблицы
*/
function getUrl($tbl,$title,$id=''){
	$turl=$url=key2url($title);
	do{
		list($tid)=db::qrow("SELECT id FROM `$tbl` WHERE url='$turl' && id!='$id' LIMIT 1");
		if($tid){
			@$i++;
			$turl="$url-$i";
		}
	}while($tid);
	return $turl;
}
/*
	удаление из таблицы связей
*/
function delRel($tbl,$tbl2,$id){
	if(empty($id)||empty($tbl)) return;
	db::query("DELETE FROM `".PREFIX_SPEC."{$tbl}2{$tbl2}` WHERE `cid`='{$id}'");
}
