<?php
namespace shop_posts_lists;
use module,db,url,cache;

require_once(__DIR__."/../func.php");

class handler{
	function __construct(){
		$this->headers=(object)array();
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->userControl=module::exec('user',array(),1)->handler;
		$this->user=$this->userControl->user;
	}
	/*
		1. список постов по категории
		2. подключает данные категории (текущей и список подкатегорий)
	*/ 
	function mainList($category,$uid,$page,$num,$uri,$popularPostsCount,$excludeCat){
		$baseCat=module::exec('shop/category',array('url'=>$category),'data')->data->cat;
		if(!$this->user->id&&$baseCat->id&&$baseCat->count<3) {
			$this->headers->location=HREF; return;
		}

		$accessEditNewsMy=$this->userControl->rbac('editNewsMy');
		$accessPublish=$this->userControl->rbac('publishPost');

		$showRights=showRights($accessPublish,$accessEditNewsMy,$uid);
		
		if($category&&$category!='no-category'){
			$pinCategory=true;
		}else $pinCategory=false;
		$start=($page-1)*$num;
		
		$q="SELECT SQL_CALC_FOUND_ROWS post.*, user.name AS authorName, user.mail AS authorMail	FROM (
				".listPostsSubquery($category,$excludeCat,$showRights)."
			) post
			/* присоединяем данные пользователя */
			LEFT JOIN `".PREFIX_SPEC."users` user ON user.id=post.user";
		db::query($q." ORDER BY `datePublish` DESC LIMIT $start,$num");
		$posts=array();
		while($d=db::fetch()){
			if($d->pincid!=$category){
				$d->txt=\shop_posts\cutText($d->txt);
			}
			$d->funcPanel=module::exec('shop/posts',array('act'=>'editPanel','post'=>$d),1)->str;
			$posts[$d->id]=$d;
		}

		list($count)=db::qrow('SELECT FOUND_ROWS()');
		if(empty($posts)&&$category&&!$this->user->id){
			$this->headers->location=HREF; return;
		}

		#получаем pin посты
		$pins=array();
		if($pinCategory){
			db::query("SELECT * FROM `post` WHERE `pincid`='{$category}'{$showRights} ORDER BY `datePublish`");
			while ($d=db::fetch()) {
				$d->funcPanel=module::exec('shop/posts',array('act'=>'editPanel','post'=>$d),1)->str;
				$pins[$d->id]=$d;
			}
		}

		#получаем картинки
		db::query(
			"SELECT img.*,keyword.title FROM (
				SELECT pid,kid,url,width,height,priority,text FROM `".PREFIX_SPEC."imgs` img
				WHERE `tbl`='shop_post' && `pid` IN(".implode(',',array_keys($posts)).")
			) img 
			LEFT JOIN `keyword` ON img.kid=keyword.id 
			ORDER BY img.priority DESC");
		while ($d=db::fetch()) {
			$img=new classImg;
			$posts[$d->pid]->imgs[]=add2obj($img,$d);
		}
		#Выводим снипеты картинок если у постов нет текстов
		foreach($posts as $k=>&$pp){
			if($pp->txt==''&&isset($pp->imgs[0]))
				$pp->txt=$pp->imgs[0]->text;
		}
				
		#собираем title постов (3 шт) для description страницы
		$forDesc=array();
		if(!empty($posts)){
			reset($posts);
			while (@++$i<=3) {
				$p=current($posts);
				if(!empty($p)){
					$forDesc[]=$p->title; next($posts);
				}
			}
			reset($posts);
		}
		return array(
			'posts'=>@$posts,
			'pins'=>$pins,
			#получаем данные категорий
			'cat'=>$baseCat,
			'breadCrumbs'=>\shop_posts\getCrumbs($baseCat),
			'subCats'=>module::exec('shop/category',array('act'=>'subList','url'=>$category,'tbl'=>'shop_post'),1)->str,
			'topLevelCats'=>module::exec('shop/category',array('act'=>'mlist','tbl'=>'shop_post','category'=>$category,'parentId'=>$baseCat->parentId),1)->str,
			'page'=>$page,
			'num'=>$num,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'act'=>((!$category&&$uri!='')||($category&&$uri==''))?'index':'typesmall',
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>$uri==''?url::category($category?$category:'main')."%d/":$uri,
					true,
				)
			)->str,
			'count'=>$count,
			'accessPostAdd'=>$this->userControl->rbac('editNews'),
			'accessPublish'=>$accessPublish,
			'access'=>$this->userControl->access(),
			'accessEditNewsMy'=>$accessEditNewsMy,
			'forDesc'=>$forDesc,
		);
	}
}

/*
	возвращает подзапрос выборки списка постов
*/
function listPostsSubquery($cat,$excludeCat,$showRights){
	if($cat=='no-category'){
		#Получаем список постов без категорий
		$q="SELECT post.* FROM `shop_post` post
				LEFT OUTER JOIN `".PREFIX_SPEC."category2shop_post` rel ON rel.pid=post.url
			WHERE rel.cid IS NULL && post.pincid=''{$showRights}";
	}elseif($cat&&!$excludeCat){
		#Получаем список постов внутри категории и всех ее подкатегорий
		$q="SELECT post.*,category.title AS catTitle 
			FROM 
			(
				SELECT post.*,rel.cid FROM `".PREFIX_SPEC."category2shop_post` rel
				INNER JOIN `shop_post` post ON post.url=rel.pid && cid='{$cat}'
				WHERE post.pincid=''{$showRights}
				GROUP BY post.id
			) post
			/* присоединяем данные категории */
			INNER JOIN `shop_category` category ON category.url=post.cid";
	}elseif($cat&&$excludeCat){
		#Получаем список постов в $cat кроме $excludeCat
		$q="SELECT post.*,category.title AS catTitle 
			FROM (
				SELECT post.*,rel.cid FROM `shop_post` post
				INNER JOIN (
					SELECT rel1.* FROM (
						SELECT * FROM `".PREFIX_SPEC."category2shop_post` WHERE cid='{$cat}'
					) rel1
					LEFT OUTER JOIN `".PREFIX_SPEC."category2shop_post` rel2 ON rel1.pid=rel2.pid && rel2.cid='{$excludeCat}' WHERE rel2.cid IS NULL
				) rel ON rel.pid=post.url WHERE post.pincid=''{$showRights}
			) post
			/* присоединяем данные категории */
			INNER JOIN `shop_category` ON category.url=post.cid";
	}elseif($excludeCat){
		#Получаем список постов во всех категориях кроме $excludeCat
		$q="SELECT * FROM (
				SELECT post.* FROM `shop_post` post
				LEFT OUTER JOIN `".PREFIX_SPEC."category2shop_post` rel 
				ON post.url=rel.pid && cid='{$excludeCat}'
				WHERE rel.cid IS NULL && post.pincid=''{$showRights}
			) post";
	}else{
		#Получаем список по всем категориям
		$q="SELECT * FROM `shop_post` post WHERE post.pincid=''{$showRights}";
	}
	return $q;
}

/*
	обертка для объекта картинок на списках
*/
class classImg{
	public function __toString(){
		return $this->url;
	}
}

/*
	#определяем права на видимость постов
	готовит выражение для вставки в SQL WHERE
*/
function showRights($accessPublish,$accessEditNewsMy,$uid){
	if($accessPublish){
		$sqlPublish='';	
	}elseif($accessEditNewsMy){
		$sqlPublish=" && (post.published='published' OR (post.published!='published' && post.user='{$uid}'))";
	}else{
		$sqlPublish=" && post.published='published'";
	}
	#устанавливаем режим видимости только постов определенного пользователя
	$sqlUid=$uid?" && `user`='{$uid}'":'';
	return $sqlPublish.$sqlUid;
}
