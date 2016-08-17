<?php
namespace gallery;
use module,db,url;

#сторонние функции
require_once(module::$path.'/category/handler.php');
require_once(module::$path.'/posts/handler.php');
require_once(module::$path.'/images/handler.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
	}
	function index($tbl,$pid,$url,$overlay=false){#Делаем все обработки для вывода данных
		$tbls=\posts\tables::initByTbl($tbl);
		#данные поста к картинке
		list($tblExist)=db::qrow("SELECT 1 FROM `".PREFIX_SPEC."{$tbls->category2post}` LIMIT 1");
		if(empty($tblExist)){
			$post=db::qfetch("SELECT post.* FROM `{$tbls->post}` post WHERE post.`id`='$pid' LIMIT 1");
		}else{
			$post=db::qfetch(
			"SELECT post.*,GROUP_CONCAT(rel.cid) AS cids FROM `{$tbls->post}` post 
				LEFT JOIN `".PREFIX_SPEC."{$tbls->category2post}` AS rel ON rel.pid=post.url
			WHERE post.`id`='$pid' GROUP BY post.id LIMIT 1");
		}
		#Редирект на главную если нет поста
		if(empty($post)){
			$this->headers->location=HREF;
			return;
		}
		#получаем категории
		$post->cats=!empty($post->cids)?\category\getCategoryData(explode(',', $post->cids),$this->uhandler->rbac('showAllCat')):array();

		$i=0;
		db::query("SELECT imgs.*,keyword.title FROM `".PREFIX_SPEC."imgs` imgs 
			LEFT JOIN keyword ON keyword.id=imgs.kid WHERE `tbl`='{$tbls->post}' && `pid`={$post->id} ORDER BY `priority` DESC");
		while($d=db::fetch()){
			$d->num=++$i;
			if($d->url==$url) $img=$d;
			$post->imgs[$d->num]=$d;
		}
		if(empty($img)){
			$this->headers->location=HREF;
			return;
		}
		$prev=(object)array(); $next=(object)array();
		$c=count($post->imgs);
		if($img->num==1&&$c!=0){
			$prev->url=$post->imgs[$c]->url;
			$prev->title=$post->imgs[$c]->title." #".$c;
		}elseif(!empty($post->imgs[$img->num-1])){
			$j=$img->num-1;
			$prev->url=$post->imgs[$j]->url;
			$prev->title=$post->imgs[$j]->title." #".$j;
		}
		if($img->num>=count($post->imgs)){
			$next->url=$post->imgs[1]->url;
			$next->title=$post->imgs[1]->title." #1";
		}else{
			$j=$img->num+1;
			$next->url=$post->imgs[$j]->url;
			$next->title=$post->imgs[$j]->title." #".$j;
		}
		//Статистика просмотров
		statViewsImage($img);
        
		if($img->source){
			$img->sourceDomain=parse_url($img->source, PHP_URL_HOST);
		}
		
		//Выводить размеры
		$file=PATH.'modules/images/files/images/'.$img->url;
		$resolution=null;
		$size=null;
		if(file_exists($file)){
			$s=getimagesize($file);
			if($s){
				$resolution=$s[0].'x'.$s[1];
			}
			$size=round(filesize($file)/1024);
		}
		$post->cats=\posts\sortCats($post->cats);
		return (object)array(
			'post'=>$post,
			'prfxtbl'=>$tbls->prfx,
			'img'=>$img,
			'relatedImgs'=>$overlay?false:relatedImgs($post->id,$img->id,$img->title),
			'count'=>$c,
			'prev'=>$prev,
			'next'=>$next,
			'tbl'=>$tbl,
			'del'=>$this->uhandler->rbac('delImage'),
			'author'=>!empty($post->user)?
				\posts\setAuthorName(
					db::qfetch("SELECT name AS authorName, mail AS authorMail FROM ".PREFIX_SPEC."users WHERE id={$post->user}")
				)
				:'',
			'resolution'=>$resolution,
			'size'=>$size,
		);
	}
	function overlayGallery($tbl,$pid,$url){
		return module::exec('gallery',
			array(
				'act'=>'index',
				'tbl'=>$tbl,
				'pid'=>$pid,
				'url'=>$url,
				'overlay'=>true,
			),'data')->data;
	}
	function overlayAds($type){
		return (object)array(
			'type'=>$type,
		);
	}
	function mobile($tbl,$pid,$url,$overlay=false){
		return module::exec('gallery',array('tbl'=>$tbl,'pid'=>$pid,'url'=>$url,'overlay'=>$overlay),'data')->data;
	}
	function imgResolution($src,$x,$y){
		if($src||$x||$y){
			header('Content-Disposition: attachment; filename="downloaded.jpg"');
			\images\resize(PATH.'modules/images/files/images/'.$src,false,$x,$y,1);
		}
		die;
	}
}

/*
	Записывает количество просмотров для картинки
*/
function statViewsImage(&$img){
	if(date('Y-m-d',strtotime($img->statShortFlag))<date('Y-m-d',time()-604800)){
		$statViewsShort=", `statViewsShort`='1', `statShortFlag`=NOW()";
	}else{
		$statViewsShort=", `statViewsShort`=`statViewsShort`+1";
	}
	db::query("UPDATE `".PREFIX_SPEC."imgs` SET `statViews`=`statViews`+1{$statViewsShort} WHERE `id`='{$img->id}'");
}
/*
	Выборка изображений для блока Related Images
*/
function relatedImgs($pid,$imid,$query,$limit=16){
	$tblImgs=PREFIX_SPEC."imgs";
	$res=array(array(),array());
	$match="MATCH img.text AGAINST('".db::escape($query)."')";
	db::query("SELECT img.*,k.title,$match as relevance  
		FROM {$tblImgs} img
		INNER JOIN `post` 
			ON post.id=img.pid
		INNER JOIN `keyword` k 
			ON img.kid=k.id
		WHERE `tbl`='post' && pid='$pid'
	ORDER BY relevance DESC");
	$start=0;
	while ($d=db::fetch()){
		if($d->id==$imid){
			$start=1;
			continue;
		}
		$res[$start][]=$d;
	}
	$out=array_slice($res[1],0,$limit);
	#Дополняем, если необходимо, список картинок с начала
	$cn=count($out);
	if($cn<$limit){
		$out=array_merge(array_slice($res[0],0,$limit-$cn),$out);
	}
	return $out;
}
