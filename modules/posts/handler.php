<?php
namespace posts;
use module,db,url,cache;
#Используем сторонние модули
require_once(module::$path.'/category/handler.php');

require_once(module::$path."/posts/func.php");
/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */

class handler{
	function __construct(){
		$this->headers=(object)array();
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->userControl=module::exec('user',array(),1)->handler;
		$this->user=$this->userControl->user;
		$this->tbl=tables::init()->post;
	}
	/*
		1. данные текущего поста
		2. подключает данные категории (текущей и список подкатегорий)
	*/
	function post($url,$imgfromcookie,$prfxtbl){
		#переключаем таблицы постов
		$tbls=tables::init($prfxtbl);

		$post=getPost($url,$this->userControl->access(),$imgfromcookie);
		#Проверяем права на просмотр и редактирование поста
		$accessEdit=checkAccess($this->userControl,$post->user);
		if(empty($post) or ($post->published!='published' && !$accessEdit)){
			$this->headers->location=HREF;
			return;
		}
		$related=relatedByCat($post,6);
		$ex=$related;
		$ex[$post->id]=$post;

		return array(
			'post'=>$post,
			'prfxtbl'=>$tbls->prfx,
			'keyword'=>$post->keyword,
			# получаем данные категорий
			'subCats'=>module::exec('category',array('act'=>'subList','url'=>isset($post->cats[0])?$post->cats[0]->url:false,'tbl'=>$this->tbl),1)->str,
			'topLevelCats'=>module::exec('category',array('act'=>'mlist','tbl'=>$this->tbl),1)->str,
			# Коментарии
			'comments' => module::exec('posts/comments', 
				array('pid'=>$post->id), 1)->str,
			# Доступы
			'access'=>(object)array(
				'publish'=>$this->userControl->rbac('publishPost'),
				'edit'=>$accessEdit,
			),
			# Перелинковка на другие посты
			'related'=>$related,
			'otherPosts'=>randomPosts(4,$ex),
		);
	}
	/*
		выводит панель удаления/редактирования поста
	*/
	function editPanel($post){
		$tbls=tables::init();
		$rbac=false;
		if(!$rbac=$this->userControl->rbac('editNews')){
			$uid=is_object($post->user)?$post->user->id:$post->user;
			if($this->user->id==$uid && $post->published != 'published') 
				$rbac=$this->userControl->rbac('editNewsMy');
		}
		$accessEdit=$rbac;
		$accessPub=$this->userControl->rbac('publishPost');
		$accessDel=($rbacDel=$this->userControl->rbac('delNews'))?$rbacDel:($post->published!='published'&&$this->userControl->rbac('delMyUnpublishedNews'));
		$accessHistory=$this->userControl->rbac('viewHistory');
		return array(
			'post'=>$post,
			'accessEdit'=>$accessEdit,
			'accessPub'=>$accessPub,
			'accessHistory'=>$accessHistory,
			'accessDel'=>$accessDel,
			'prfxtbl'=>$tbls->prfx,
		);
	}
	/*
		обработка rss шаблона
	*/
	function rss(){
		$this->template='';
		db::query("SELECT * FROM `".$this->tbl."` WHERE `published`='published' && `pincid`='' ORDER BY `datePublish` DESC LIMIT 50");
		$posts = array();
		while($d=db::fetch()){
			$d->title=htmlentities(html_entity_decode($d->title),ENT_XML1);
			$d->txt=htmlentities(html_entity_decode(cutText(strip_tags($d->txt))),ENT_XML1);
			$posts[$d->id]=$d;
		}
		
		#получаем картинки
		db::query("SELECT pid,GROUP_CONCAT(img.url ORDER BY img.priority DESC) AS img FROM `".PREFIX_SPEC."imgs` img
			WHERE `tbl`='".$this->tbl."' && `pid` IN(".implode(',',array_keys($posts)).") GROUP BY pid");
		while ($d=db::fetch()) {
			$posts[$d->pid]->imgs=explode(',',$d->img);
		}
		
		return array(
			'posts'=>$posts,
		);
	}
	
	/**
	 * Обработка событий для кнопок соц сетей
	 * 
	 * @param string $buttonID ID кнопки
	 * @param bool $isLike like = true, unlike = false, share = true 
	 */
	function socStat($buttonID,$isLike){
		$buttonIDs = array(
			'fb-follow-top','fb-follow-bottom','fb-share-top','fb-share-bottom',
			'fb-like','fb-follow-asidepopup','fb-follow-sidebarwidget'
		);
		if(!in_array($buttonID,$buttonIDs)){
			exit;
		}
		$like=$isLike?1:0;
		$unlike=$isLike?0:1;
		db::query("INSERT INTO `".PREFIX_SPEC."socStat` (`buttonID`,`date`,`like`,`unlike`) 
					VALUES('{$buttonID}','".date('Y-m-d')."','{$like}','{$unlike}')
					ON DUPLICATE KEY UPDATE `like`=`like`+{$like}, `unlike`=`unlike`+{$unlike}",1);
		exit;
	}
}