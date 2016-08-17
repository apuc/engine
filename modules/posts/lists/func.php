<?
namespace posts_lists;
use module,db,url,cache;
/*
	возвращает подзапрос выборки списка постов
*/
function listPostsSubquery($cat,$excludeCat,$showRights){
	$tbls=\posts\tables::init();
	if($cat=='no-category'){
		#Получаем список постов без категорий
		$q="SELECT post.* FROM `{$tbls->post}` post
				LEFT OUTER JOIN `".PREFIX_SPEC."{$tbls->category2post}` rel ON rel.pid=post.url
			WHERE rel.cid IS NULL{$showRights}";
	}elseif($cat&&!$excludeCat){
		#Получаем список постов внутри категории и всех ее подкатегорий
		$q="SELECT post.*,category.title AS catTitle 
			FROM 
			(
				SELECT post.*,rel.cid FROM `".PREFIX_SPEC."{$tbls->category2post}` rel
				INNER JOIN `{$tbls->post}` post ON post.url=rel.pid && cid='{$cat}'
				WHERE 1{$showRights}
				GROUP BY post.id
			) post
			/* присоединяем данные категории */
			INNER JOIN `category` ON category.url=post.cid";
	}elseif($cat&&$excludeCat){
		#Получаем список постов в $cat кроме $excludeCat
		$q="SELECT post.*,category.title AS catTitle 
			FROM (
				SELECT post.*,rel.cid FROM `{$tbls->post}` post 
				INNER JOIN (
					SELECT rel1.* FROM (
						SELECT * FROM `".PREFIX_SPEC."{$tbls->category2post}` WHERE cid='{$cat}'
					) rel1
					LEFT OUTER JOIN `".PREFIX_SPEC."{$tbls->category2post}` rel2 ON rel1.pid=rel2.pid && rel2.cid='{$excludeCat}' WHERE rel2.cid IS NULL
				) rel ON rel.pid=post.url WHERE 1{$showRights}
			) post
			/* присоединяем данные категории */
			INNER JOIN `category` ON category.url=post.cid";
	}elseif($excludeCat){
		#Получаем список постов во всех категориях кроме $excludeCat
		$q="SELECT * FROM (
				SELECT post.* FROM `{$tbls->post}` post 
				LEFT OUTER JOIN `".PREFIX_SPEC."{$tbls->category2post}` rel 
				ON post.url=rel.pid && cid='{$excludeCat}'
				WHERE rel.cid IS NULL{$showRights}
			) post";
	}else{
		#Получаем список по всем категориям
		$q="SELECT * FROM `{$tbls->post}` post WHERE 1{$showRights}";
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
function showRights($accessPublish,$accessEditNewsMy,$uid=false,$pin=false){
	if($accessPublish){
		$sqlPublish='';	
	}elseif($accessEditNewsMy){
		$sqlPublish=" && (post.published='published' OR (post.published!='published' && post.user='{$uid}'))";
	}else{
		$sqlPublish=" && post.published='published'";
	}
	#устанавливаем режим видимости только постов определенного пользователя
	$sqlUid=$uid?" && `user`='{$uid}'":'';
	$sqlPin=!$pin?" && post.pincid=''":' ';
	return $sqlPin.$sqlPublish.$sqlUid;
}
/*
	получает прикрепленные посты для категории
*/
function getPins($category,$showRights){
	$tbls=\posts\tables::init();
	#получаем pin посты
	$pins=array();
	if($category&&$category!='no-category'){
		db::query("SELECT *,u.mail AS authorMail, u.name AS authorName FROM `{$tbls->post}` post 
			LEFT JOIN `".PREFIX_SPEC."users` u ON u.id=post.user
			WHERE `pincid`='{$category}'{$showRights} ORDER BY `datePublish`");
		while ($d=db::fetch()) {
			$d->funcPanel=module::exec('posts',array('act'=>'editPanel','post'=>$d),1)->str;
			$d->authorName=\posts\setAuthorName($d);
			$pins[$d->id]=$d;
		}
	}
	return $pins;
}
/*
	получает картинки для списка постов
*/
function getPostsImages($pids,&$posts){
	$tbls=\posts\tables::init();
	if(is_array($pids)) $concatPids=implode(',', $pids);
	else $concatPids=$pids;
	db::query(
	"SELECT tmp.* FROM (
		SELECT img.tbl,img.pid,img.kid,img.url,img.width,img.height,img.priority,img.text,keyword.title 
		FROM `".PREFIX_SPEC."imgs` img 
		LEFT JOIN `keyword` ON img.kid=keyword.id  
		WHERE img.pid IN({$concatPids}) && img.tbl='{$tbls->post}'
	) tmp ORDER BY tmp.priority DESC");
	while ($d=db::fetch()) {
		$img=new classImg;
		if(isset($posts[$d->pid])) $posts[$d->pid]->imgs[]=add2obj($img,$d);
	}
	return $posts;
}
/*
	получает данные из текста поста, до pagepreak
*/
function pageBreak($str){
	$str=preg_replace('!<[^\>]+>[^\<]*(\<\!\-\- pagebreak \-\-\>)[^\<]*</[^\>]+>!s', '$1', $str);
	$pos=strpos($str,'<!-- pagebreak -->');
	if($pos!==false){
		$str=substr($str,0,$pos);
	}
	return $str;
}