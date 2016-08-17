<?php
namespace category;
use module,db,url;

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
		$tbls=\posts\tables::init();
		$accessEdit=$this->userControl->rbac('editCat');
		# получаем самые верхние категории
		$toplevel=array();
		if($accessShowAll=$this->userControl->rbac('showAllCat')){
			$noCategory=new category('no-category',$accessShowAll);
			$noCategory->count=getSubCount($noCategory,$this->userControl);
			$toplevel[]=$noCategory;
		}
		$res=db::query("SELECT * FROM `{$tbls->category}` WHERE `parentId`=''".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
		while ($d=db::fetch($res)) {
			$d->count=getSubCount($d,$this->userControl);
			if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которыз больше 2х постов.
			$d->title=ucfirst($d->title);
			if(!$accessShowAll&&!$d->count) continue;
			$d->funcPanel=module::exec('category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
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
				$res=db::query("SELECT * FROM `{$tbls->category}` WHERE `parentId`='".db::escape($parentId)."'".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
				while($d=db::fetch($res)){
					$d->count=getSubCount($d,$this->userControl);
					if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которыз больше 2х постов.
					$d->title=ucfirst($d->title);
					if(!$accessShowAll&&!$d->count) continue;
					$d->funcPanel=module::exec('category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
					$samelevel->cats[]=$d;
				}
			}
		}
		return (object)array(
			'cats'=>$toplevel,
			'samelevel'=>$samelevel,
			'edit'=>$accessEdit,
			'prfxtbl'=>$tbls->prfx,
		);
	}
	/*
		1. Вывод текущей категории
		2. Вывод подкатегорий этой категории
	*/
	function subList($url,$tbl){
		$tbls=\posts\tables::init();
		$accessEdit=$this->userControl->rbac('editCat');
		$accessShowAll=$this->userControl->rbac('showAllCat');
		# получаем текущую категорию
		$sub=array();
		$curItem = new category($url,$accessShowAll);
		$funcPanel='';
		if($curItem->id){
			$curItem->title=ucfirst($curItem->title);
			# получаем подкатегории
			$funcPanel=module::exec('category/admin',array('act'=>'funcPanel','itemID'=>$curItem->id,'itemUrl'=>$curItem->url),1)->str;
			# получаем подкатегории
			$res=db::query("SELECT * FROM `{$tbls->category}` WHERE `parentID`='{$curItem->url}'".(!$accessShowAll?" && `view`='1'":'')." ORDER BY `title` ASC");
			while ($d=db::fetch($res)) {
				$d->count=getSubCount($d,$this->userControl);
				if(($d->count<3)&&(!$accessShowAll))continue; # выводим только категории в которых больше 2х постов.
				$d->title=ucfirst($d->title);
				if(!$accessShowAll&&!$d->count) continue;
				$d->funcPanel=module::exec('category/admin',array('act'=>'funcPanel','itemID'=>$d->id,'itemUrl'=>$d->url),1)->str;
				$sub[]=$d;
			}
		}
		return (object)array(
			'cur'=>$curItem,
			'sub'=>$sub,
			'funcPanel'=>$funcPanel,
			'accessShowAll'=>$accessShowAll,
			'prfxtbl'=>$tbls->prfx,
		);
	}
	function ajaxCategories($query,$prfxtbl){
		if (!$query) die;
		$tbls=\posts\tables::init($prfxtbl);
		$this->template='';
		$query = db::escape($query);
		db::query("SELECT CONCAT(url,',',title) ccstr FROM `{$tbls->category}` 
			WHERE url LIKE '{$query}%' OR title LIKE '{$query}%' ORDER BY title LIMIT 10");

		$data = array();
		while($d=db::fetch()){
			$data[] = $d->ccstr;
		}

		return array(
			'categories'=> $data
		);
	}
	function updateCount($cats,$prfxtbl){
		$tbls=\posts\tables::init($prfxtbl);
		if(is_array($cats)) $sqlin=" && cat.url IN('".implode("','", $cats)."')";
		elseif($cats=='all'){
			db::query("UPDATE `{$tbls->category}` SET `count`=0, `countAdmin`=0");
			$sqlin='';
		}else return;
		#получачем количество опубликованных постов
		$sql="SELECT cat.url as cid,SUM(IF(post.id IS NULL,0,1)) AS `count` FROM `{$tbls->category}` cat
				LEFT JOIN `".PREFIX_SPEC."{$tbls->category2post}` rel ON rel.cid=cat.url
				LEFT JOIN `{$tbls->post}` post ON rel.pid=post.url %s
			WHERE 1 {$sqlin} GROUP BY cat.url";
		$res=db::query(sprintf($sql,"&& post.published='published'"));
		while ($d=db::fetch($res)) {
			# Обновляем количество постов count в БД.
			db::query("UPDATE `{$tbls->category}` SET `count`='{$d->count}' WHERE `url`='{$d->cid}' LIMIT 1");
		}
		#получачем количество всех постов
		$res=db::query(sprintf($sql,''));
		while ($d=db::fetch($res)) {
			# Обновляем количество постов count в БД.
			db::query("UPDATE `{$tbls->category}` SET `countAdmin`='{$d->count}' WHERE `url`='{$d->cid}' LIMIT 1");
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
		db::query("SELECT * FROM `category` WHERE `url` IN('".implode("','", $cids)."')".(!$view?" && `view`='1' && `count` > 2 ":''));
		while($d=db::fetch()){
			$cats[]=$d;
		}
	}
	return $cats;
}
/*
	Получает количество постов в категории
*/
function getSubCount(&$category,$access){
	static $accPub;
	static $accShow;
	if(!isset($accPub)) $accPub=$access->rbac('publishPost');
	if(!isset($accShow)) $accShow=$access->rbac('showAllCat');
	$tbls=\posts\tables::init();
	$relTbl = PREFIX_SPEC.$tbls->category2post;
	$SQLpublished=(!$accPub)? "&& post.published='published'":'';
	if($category->url=='no-category'){
		list($count)=db::qrow(
			"SELECT COUNT(*) FROM `{$tbls->post}` post 
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
			$tbls=\posts\tables::init();
			$q="SELECT `id`,`title`,`url`,`parentId`,`count` FROM `{$tbls->category}` WHERE `url`='{$url}'".(!$view?" && `view`='1'":'')." LIMIT 1";
			list($this->id,$this->title,$this->url,$this->parentId,$this->count)=db::qrow($q);
		}
	}
}
