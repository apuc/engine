<?php
namespace category_admin;
use module,db,url,StdClass;

require_once(module::$path.'/admin/themes/handler.php');
require_once(module::$path.'/posts/handler.php');

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
	function edit($parentID,$itemID,$prfxtbl){
		if(!$this->userControl->rbac('editCat')) die('forbidden');
		$tbls=\posts\tables::init();
		# получаем данные самого элемента
		if($itemID){
			$item=db::qfetch("SELECT * FROM `{$tbls->category}` WHERE `id`='{$itemID}' LIMIT 1");
			$parentID=$item->parentId;
		}else{
			$item = new StdClass;
			$item->id=$itemID;
		}
		# получаем данные родителя
		if($parentID){
			$parent=db::qfetch("SELECT * FROM `{$tbls->category}` WHERE `url`='{$parentID}' LIMIT 1");
		}else{
			$parent = new StdClass;
			$parent->url='';
			$parent->title='Index';
		}
		return (object)array(
			'parent'=>$parent,
			'item'=>$item,
			'themes'=>$this->userControl->rbac('themesSet')?\admin_themes\themes():array(),
		);
	}
	/*
		1. сохранение категории
		2. подключает вывод формы редактирования
	*/
	function save($parentID,$itemID,$itemUrl,$prfxtbl,$merge,$name,$view,$subCatList,$theme){
		if(!$this->userControl->rbac('editCat')) die('forbidden');
		$tbls=\posts\tables::init();
		$mesSuccess=$mesWarning='';
		$urlExists=false;
		if($itemID){
			list($oldUrl)=db::qrow("SELECT url FROM `{$tbls->category}` WHERE id='{$itemID}' LIMIT 1");
			#edit
			if($parentID!=''){
				list($parentExists)=db::qrow("SELECT id FROM `{$tbls->category}` WHERE `url`='{$parentID}' LIMIT 1");
				if(empty($parentExists)){
					$mesWarning='parentNotExists';
				}
			}
			if($itemUrl){
				if($itemUrl=='')
					$mesWarning='urlEmpty';
				else{
					list($urlExists)=db::qrow("SELECT id FROM `{$tbls->category}` WHERE `url`='{$itemUrl}' LIMIT 1");
					if(!empty($urlExists)&&!$merge){
						$mesWarning='urlExists';
					}else{
						if(mergeCats($oldUrl,$itemUrl)){
							$itemID=$urlExists;
							$mesWarning='mergeComplete';
							$mesSuccess='usuccess';
						}else
							$mesWarning='mergeFail';
						
					}
				}
			}
			if(empty($mesWarning)){
				db::query(
				"UPDATE `category` SET 
					`title`='{$name}',
					`view`='{$view}',
					`parentID`='{$parentID}',
					".($itemUrl?"`url`='{$itemUrl}',":'')."
					`subCatList`='{$subCatList}',
					`theme`='{$theme}'
					WHERE `id`='{$itemID}' LIMIT 1");
				if(!db::error()){
					$mesSuccess='usuccess';
					if($urlExists!=$itemID&&$itemUrl)
						updateCategoryUrlRel($oldUrl,$itemUrl);
				}
				else
					$mesWarning='ufail';
			}
		}else{
			#add
			list($exists)=db::qrow("SELECT `id` FROM `{$tbls->category}` WHERE `title`='{$name}' && `parentId`='{$parentID}' LIMIT 1");
			if(!$exists){
				db::query("INSERT INTO `{$tbls->category}` (`url`,`title`,`parentId`,`view`,`theme`,`subCatList`) 
					VALUES('".getUrl('{$tbls->category}',$name)."','{$name}','{$parentID}','{$view}','{$theme}','{$subCatList}')",1);
				if($itemID=db::insert())
					$mesSuccess='isuccess';
				else
					$mesWarning='ifail';
			}else{
				$mesWarning='nameExists';
			}
		}
		return (object)array(
			'success'=>$mesSuccess,
			'warning'=>$mesWarning,
			'html'=>module::exec('category/admin',
				array('act'=>'edit','itemID'=>$itemID),1)->str,
		);
	}
	/*
		сохраняет категории из загрузки структуры см. posts/admin loadStruct()
	*/
	function structSave($parentID,$name,$view){
		if(!$this->userControl->rbac('editCat')) die('forbidden');
		$tbls=\posts\tables::init();
		@$url=db::qfetch("SELECT `url` FROM `{$tbls->category}` WHERE `title`='{$name}' && parentId='$parentID' LIMIT 1")->url;
		if(!$url){
			$url=getUrl($tbls->category,$name);
			$renamed=key2url($name)!=$url;
			db::query("INSERT INTO `{$tbls->category}` (`url`,`title`,`parentId`,`view`) 
				VALUES('{$url}','{$name}','{$parentID}','{$view}')",1);
			if(!$itemID=db::insert())
				return false;
		}else
			$exists=1;
		return (object)array(
			'id'=>$url,
			'exists'=>@$exists,
			'renamed'=>@$renamed,
		);
	}
	/*
		1. удаление категории
	*/
	function del($itemID,$prfxtbl){
		if(!$this->userControl->rbac('delCat')) die('forbidden');
		$tbls=\posts\tables::init();
		if($itemID){
			list($url,$parentID)=db::qrow("SELECT `url`,`parentId` FROM `{$tbls->category}` WHERE `id`='{$itemID}' LIMIT 1");
			db::query("DELETE FROM `{$tbls->category}` WHERE `id`='{$itemID}' LIMIT 1");
			# удаление связи с постами
			db::query("DELETE FROM `".PREFIX_SPEC."{$tbls->category2post}` WHERE `cid`='{$id}'");
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
		$tbls=\posts\tables::init();
		return (object)array(
			'itemID'=>$itemID,
			'itemUrl'=>$itemUrl,
			'accessEdit'=>$accessEdit,
			'accessDel'=>$accessDel,
			'prfxtbl'=>$tbls->prfx,
		);
	}

	function install(){
		$tbl=\posts\tables::init();
		db::query("CREATE TABLE IF NOT EXISTS `{$tbl->category}` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `url` varchar(125) NOT NULL,
		  `title` varchar(255) NOT NULL,
		  `parentId` varchar(255) NOT NULL,
		  `count` int(11) NOT NULL COMMENT 'cache count',
		  `countAdmin` int(11) NOT NULL COMMENT 'cache admin count',
		  `view` int(11) NOT NULL DEFAULT '1',
		  `theme` VARCHAR(255) NOT NULL DEFAULT '',
		  `subCatList` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `url` (`url`),
		  KEY `parentId` (`parentId`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."{$tbl->category2post}` (
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
	приобновлении URL категории 
	- обновляет связи с постами
	- связи с pin поставми
	- связи с дочерними категорями
*/
function updateCategoryUrlRel($old,$url){
	$tbls=\posts\tables::init();
	db::query("UPDATE `".PREFIX_SPEC."{$tbls->category2post}` SET cid='{$url}' WHERE cid='{$old}'");
	db::query("UPDATE `{$tbls->post}` SET pincid='{$url}' WHERE pincid='{$old}'");
	db::query("UPDATE `{$tbls->category}` SET parentId='{$url}' WHERE parentId='{$old}'");
}
/*
	объединяет категории
	a->b
*/
function mergeCats($a,$b){
	$tbls=\posts\tables::init();
	updateCategoryUrlRel($a,$b);
	db::query("DELETE FROM `{$tbls->category}` WHERE `url`='{$a}' LIMIT 1");
	if(!db::error()){
		module::exec('category',array('act'=>'updateCount','cats'=>array($b),'tbl'=>$tbls->post),'data');
		return true;
	}else
		return false;
}
