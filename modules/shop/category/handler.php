<?php
namespace shop_category;
use module,db,url;

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
		1. получает данные категории
	*/
	function index($url){
		return (object)array(
			'cat'=>new category($url,$this->userControl->rbac('showAllCat')),
		);
	}
	/*
	* Вывод всех верхних категорий
	*/ 
	function mlist($tbl,$category,$parentId){
		$accessEdit=$this->userControl->rbac('editCat');
		# получаем самые верхние категории
		$toplevel=array();
		if($accessShowAll=$this->userControl->rbac('showAllCat')){
			$noCategory=new category('no-category',$accessShowAll);
			$noCategory->count=getSubCount($noCategory,$tbl,$this->userControl);
			$toplevel[]=$noCategory;
		}
		$res=db::query("SELECT * FROM `shop_category` WHERE `parentId`=''".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
		while ($d=db::fetch($res)) {
			$d->count=getSubCount($d,$tbl,$this->userControl);
			if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которыз больше 2х постов.
			$d->title=ucfirst($d->title);
			if(!$accessShowAll&&!$d->count) continue;
			$d->funcPanel=module::exec('shop/category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
			$toplevel[]=$d;
		}
		$samelevel=null;
		if($category && $parentId){
			$baseCat=new category($parentId,false);
			$samelevel=new \stdClass();
			$samelevel->baseCat=$baseCat;
			$samelevel->current=$category;
			$samelevel->cats=array();
			if(!empty($baseCat->id)){
				$res=db::query("SELECT * FROM `shop_category` WHERE `parentId`='".db::escape($parentId)."'".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
				while($d=db::fetch($res)){
					$d->count=getSubCount($d,$tbl,$this->userControl);
					if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которыз больше 2х постов.
					$d->title=ucfirst($d->title);
					if(!$accessShowAll&&!$d->count) continue;
					$d->funcPanel=module::exec('shop/category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
					$samelevel->cats[]=$d;
				}
			}
		}
		return (object)array(
			'cats'=>$toplevel,
			'samelevel'=>$samelevel,
			'edit'=>$accessEdit,
		);
	}
	/*
		1. Вывод текущей категории
		2. Вывод подкатегорий этой категории
	*/
	function subList($url,$tbl){
		$accessEdit=$this->userControl->rbac('editCat');
		$accessShowAll=$this->userControl->rbac('showAllCat');
		# получаем текущую категорию
		$sub=array();
		$curItem = new category($url,$accessShowAll);
		$funcPanel='';
		if($curItem->id){
			$curItem->title=ucfirst($curItem->title);
			# получаем подкатегории
			$funcPanel=module::exec('shop/category/admin',array('act'=>'funcPanel','itemID'=>$curItem->id,'itemUrl'=>$curItem->url),1)->str;
			# получаем подкатегории
			$res=db::query("SELECT * FROM `shop_category` WHERE `parentID`='{$curItem->url}'".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
			while ($d=db::fetch($res)) {
				$d->count=getSubCount($d,$tbl,$this->userControl);
				if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которых больше 2х постов.
				$d->title=ucfirst($d->title);
				if(!$accessShowAll&&!$d->count) continue;
				$d->funcPanel=module::exec('shop/category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
				$sub[]=$d;
			}
		}
		return (object)array(
			'cur'=>$curItem,
			'sub'=>$sub,
			'funcPanel'=>$funcPanel,
			'accessShowAll'=>$accessShowAll,
		);
	}
	function ajaxCategories($query){
		if (!$query) die;
		$this->template='';
		$query = db::escape($query);
		db::query("SELECT CONCAT(url,',',title) ccstr FROM `shop_category` 
			WHERE url LIKE '{$query}%' OR title LIKE '{$query}%' ORDER BY title LIMIT 10");

		$data = array();
		while($d=db::fetch()){
			$data[] = $d->ccstr;
		}

		return array(
			'categories'=> $data
		);
	}
	function updateCount($cats,$tbl){
		if(!$tbl) return;
		if(is_array($cats)) $sqlin=" && rel.cid IN('".implode("','", $cats)."')";
		elseif($cats=='all'){
			db::query("UPDATE `shop_category` SET `count`=0, `countAdmin`=0");
			$sqlin='';
		}else return;
		#получачем количество опубликованных постов
		$res=db::query(
			"SELECT rel.cid,COUNT(*) AS `count` FROM `{$tbl}` post 
				LEFT JOIN `".PREFIX_SPEC."category2{$tbl}` rel ON rel.pid=post.url
			WHERE 1 && cid IS NOT NULL {$sqlin} && post.published='published' GROUP BY rel.cid");
		while ($d=db::fetch($res)) {
			# Обновляем количество постов count в БД.
			db::query("UPDATE `shop_category` SET `count`='{$d->count}' WHERE `url`='{$d->cid}' LIMIT 1");
		}
		#получачем количество всех постов
		$res=db::query(
			"SELECT rel.cid,COUNT(*) AS `count` FROM `{$tbl}` post 
				LEFT JOIN `".PREFIX_SPEC."category2{$tbl}` rel ON rel.pid=post.url
			WHERE 1 && cid IS NOT NULL {$sqlin} GROUP BY rel.cid");
		while ($d=db::fetch($res)) {
			# Обновляем количество постов count в БД.
			db::query("UPDATE `shop_category` SET `countAdmin`='{$d->count}' WHERE `url`='{$d->cid}' LIMIT 1");
		}
		$this->template='';
		return;
	}
}

/*
	принимает: массив ID категорий
	возвращает: массив категорий с данными
*/
function getCategoryData($cids,$view=1){
	if(!is_array($cids)) return;
	$cats=array();
	if($cids[0]=='no-category'){
		$cats[]=(object)array('url'=>'no-category','title'=>'Uncategorized');
	}else{
		db::query("SELECT * FROM `shop_category` WHERE `url` IN('".implode("','", $cids)."')".(!$view?" && `view`='1' && `count` > 2 ":''));
		while($d=db::fetch()){
			$cats[]=$d;
		}
	}
	return $cats;
}
/*
	Получает количество постов в категории для no-category
*/
function getSubCount(&$category,$tbl='post',$access){
	static $accPub;
	static $accShow;
	if(!isset($accPub)) $accPub=$access->rbac('publishPost');
	if(!isset($accShow)) $accShow=$access->rbac('showAllCat');
	if(empty($tbl)) return 0;
	$relTbl = PREFIX_SPEC."category2{$tbl}";
	$SQLpublished=(!$accPub)? "&& post.published='published'":'';
	if($category->url=='no-category'){
		list($count)=db::qrow(
			"SELECT COUNT(*) FROM `{$tbl}` post 
				LEFT OUTER JOIN `{$relTbl}` rel ON rel.pid=post.url
			WHERE rel.cid IS NULL {$SQLpublished}");
	}else{
		$count=($accShow)?$category->countAdmin:$category->count;
	}
	return (int)$count;
}

class category{
	function __construct($url,$view=1){
		if(($empty=empty($url))||$url=='no-category'){
			$this->title=$empty?'':'Uncategorized';
			$this->url=$empty?'':'no-category';
			$this->parentId='';
			$this->count=0;
			$this->id=0;
		}else{
			$q="SELECT `id`,`title`,`url`,`parentId`,`count` FROM `shop_category` WHERE `url`='{$url}'".(!$view?" && `view`='1'":'')." LIMIT 1";
			list($this->id,$this->title,$this->url,$this->parentId,$this->count)=db::qrow($q);
		}
	}
}
