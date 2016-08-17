<?php
namespace shop_posts;
use module,db,url,cache;
#Используем сторонние модули
require_once(__DIR__.'/../category/handler.php');
require_once(__DIR__."/func.php");
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
	}
	/*
		1. данные текущего поста
		2. подключает данные категории (текущей и список подкатегорий)
	*/
	function post($url,$imgfromcookie){
		$post=db::qfetch("SELECT 
				post.*, user.name AS authorName, user.mail AS authorMail, keyword.title as keyword
			FROM 
				(SELECT 
						DISTINCT post.*,GROUP_CONCAT(rel.cid) cids 
					FROM `shop_post` post
					LEFT JOIN `".PREFIX_SPEC."category2shop_post` rel 
					ON rel.pid=post.url
						WHERE `url`='$url' GROUP BY `post`.id 
					LIMIT 1
				) post
			/* присоединяем данные пользователя */
			LEFT JOIN `".PREFIX_SPEC."users` user 
				ON user.id=post.user
			LEFT JOIN `keyword`
				ON keyword.id=post.kid
		");
		#Проверяем права на просмотр и редактирование поста
		$accessEdit=checkAccess($this->userControl,$post->user);
		if(empty($post) or ($post->published!='published' && !$accessEdit)){
			$this->headers->location=HREF;
			return;
		}
		# Обрабаываем данные в посте
		$post->tbl='shop_post';
		$post->shortTxt=strip_tags(cutText($post->txt));
		$post->stxt=substr($post->txt,strpos($post->txt,'<!-- pagebreak -->'));
		$post->authorName=setAuthorName($post);
		
		#получаем категории
		$post->cats=!empty($post->cids)?\shop_category\getCategoryData(explode(',', $post->cids),$this->userControl->rbac('showAllCat')):array();
		$post->cats=sortCats($post->cats);
		#получаем картинки
		list($post->imgcookie,$post->imgs)=getImages($post->tbl,$post->id,$imgfromcookie);
		#Получаем панель редактирования поста
		$post->funcPanel=module::exec('shop/posts',array('act'=>'editPanel','post'=>$post),1)->str;
		#получаем prev|next посты в рамках категории поста
		$post->prevnext=getPrevNext($post,isset($post->cats[0])?$post->cats[0]:'');
		# Записываем статистику просмостров поста
		statViews($post,'shop_post');
		return array(
			'post'=>$post,
			'keyword'=>$post->keyword,
			# получаем данные категорий
			'subCats'=>module::exec('shop/category',array('act'=>'subList','url'=>isset($post->cats[0])?$post->cats[0]->url:false,'tbl'=>'shop_post'),1)->str,
			'topLevelCats'=>module::exec('shop/category',array('act'=>'mlist','tbl'=>$post->tbl),1)->str,
			'access'=>(object)array(
				'publish'=>$this->userControl->rbac('publishPost'),
				'edit'=>$accessEdit,
			),
			'related'=>$ex=relatedByCat($post,6),
			'otherPosts'=>randomPosts(4,array_merge($ex,array("{$post->id}"=>$post))),
		);
	}
	/*
		выводит панель удаления/редактирования поста
	*/
	function editPanel($post){
		$rbac=false;
		if(!$rbac=$this->userControl->rbac('editNews')){
			if($this->user->id==$post->user && $post->published != 'published') 
				$rbac=$this->userControl->rbac('editNewsMy');
		}
		$accessEdit=$rbac;
		$accessPub=$this->userControl->rbac('publishPost');
		$accessDel=($rbacDel=$this->userControl->rbac('delNews'))?$rbacDel:($post->published!='published'&&$this->userControl->rbac('delMyUnpublishedNews'));
		$accessHistory=$this->userControl->rbac('viewHistory');
		return array('post'=>$post,'accessEdit'=>$accessEdit,'accessPub'=>$accessPub,'accessHistory' => $accessHistory,'accessDel'=>$accessDel);
	}
}
