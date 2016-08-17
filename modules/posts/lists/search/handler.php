<?php
namespace posts_lists_search;
use module,db,url;
require_once(module::$path."/posts/handler.php");
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
		$this->user=module::exec('user')->handler->user;
	}
	/*
		поиск по таблице post
	*/
	function index($q,$prfxtbl,$page,$num){
		$tbls=\posts\tables::init($prfxtbl);
		$query=db::escape(substr(trim($q),0,255));
		$limit="LIMIT ".(($page-1)*$num).",$num";
		$fsql=array();
		$sqlWhere="&& published='published' && post.pincid=''";
		if(mb_strlen($q)>3){
			$sql="SELECT *,MATCH(`title`,`txt`) AGAINST('{$query}' IN BOOLEAN MODE) AS score FROM `{$tbls->post}` post WHERE 1 {$sqlWhere} HAVING `score`!=0 ORDER BY `score` DESC";
			list($count)=db::qrow("SELECT COUNT(id) FROM `{$tbls->post}` post WHERE 1 {$sqlWhere} && MATCH(`title`,`txt`) AGAINST('{$query}'  IN BOOLEAN MODE)");
		}else{
			$sql="SELECT * FROM `{$tbls->post}` post WHERE (`title` LIKE '%{$query}%' OR `txt` LIKE '%{$query}%') {$sqlWhere}";
			list($count)=db::qrow("SELECT COUNT(id) FROM `{$tbls->post}` post WHERE (`title` LIKE '%{$query}%' OR `txt` LIKE '%{$query}%') {$sqlWhere}");
		}
		db::query("$sql $limit");
		$posts=array();
		while($d=db::fetch()){
			$d->imgs=array();
			$d->cats=array();
			$posts[$d->id]=$d;
			$postsUrls[$d->url]=$d->id;
		}
		if(!empty($posts)){
			# получаем картинки
			\posts\getImages2list($posts);
			# получаем категории
			db::query("SELECT title,url,cat.pid FROM `".PREFIX_SPEC."{$tbls->category2post}` cat 
				JOIN category ON category.url=cat.cid 
				WHERE cat.pid IN('".implode("','", array_keys($postsUrls))."')");
			while ($d=db::fetch()) {
				$o=clone $d;
				unset($o->pid);
				$posts[$postsUrls[$d->pid]]->cats[$d->url]=$o;
			}	
		}
		
		return (object)array(
			'cat'=>(object)array('title'=>ucfirst($query)),
			'query'=>ucfirst($query),
			'posts'=>$posts,
			'paginator'=>module::exec("plugins/paginator",array(
				'page'=>$page,
				'count'=>$count,
				'uri'=>url::searchQueryPage($q,'%d'),
				'num'=>$num,
			),1)->str,
			'page'=>$page,
			'topLevelCats'=>module::exec('category',array('act'=>'mlist','tbl'=>$tbls->post,'category'=>'','parentId'=>''),1)->str,
			'noindex'=>1,
		);
	}
}
