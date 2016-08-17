<?php
 /*
  * Должен возвращать
  * $this->data - объект обработанных входных переменных
  * $this->act - какую функцию обработки используем
  */
class shop_posts_admin extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(@$input->act=='') $input->act='index';
		$this->act=$input->act;
		
		if($input->act=='del'){
			$this->data=(object)array(
				'pid'=>(int)@$_GET['pid']
			);
		}if($input->act=='dellPosts'){
			$this->data=(object)array(
				'pids'=>@$_POST['pids'],
			);
		}elseif($input->act=='edit'){
			if(!empty($input->save)){
				$this->act='save';
				$images=array();
				$images['files']=!empty($_FILES['images']['tmp_name'])?$_FILES['images']['tmp_name']:false;
				$images['urls']=!empty($_POST['images'])?$_POST['images']:'';
				$images['description']=!empty($_POST['imagesInputDescription'])?$_POST['imagesInputDescription']:'';
				$images['href']=!empty($_POST['imagesInputHref'])?$_POST['imagesInputHref']:'';
                $images['text_update']=!empty($_POST['image_description'])?$_POST['image_description']:'';
				$this->data=(object)array(
					'id'=>(int)$_POST['pid'],
					'title'=>@$_POST['title'],
					'price'=>empty($_POST['price'])?0:(int)$_POST['price'],
					'text'=>empty($_POST['post-text'])?'':$_POST['post-text'],
					'cats'=>empty($input->cat)?'':$input->cat,
					'pincid'=>empty($input->pincid)?'':$input->pincid,
					'sources'=>empty($input->sources)?'':db::escape($input->sources),
					'foruser'=>empty($_POST['foruser'])?0:(int)$_POST['foruser'],
					'images'=>$images,
					'site'=>empty($_POST['site'])?false:$_POST['site'],
					'formAction'=>$input->save,
				);
			}else{
				$this->data=(object)array(
					'id'=>isset($input->pid)?(int)$input->pid:0,
					'cat'=>empty($input->parentID)?array():array(db::escape($input->parentID)),
				);
			}
		}elseif($input->act=='editKeywords'){
			$this->data=(object)array(
				'pid'=>isset($input->pid)?(int)$input->pid:0
			);
		}elseif($input->act=='structSave'&&$input->easy){
			$this->data=(object)array(
				'title'=>$input->title,
				'cats'=>empty($input->cat)?'':$input->cat
			);
		}elseif($input->act=='listByUser'){
			$this->data=(object)array(
				'page'=>@$input->page>0?(int)$input->page:1,
				'num'=>empty($input->num)?5:(int)$input->num,
				'user'=>empty($input->user)&&$input->user!=='0'?false:(int)$input->user,
				'type'=>empty($input->type)?false:$input->type,
			);
		}elseif($input->act=='stat'){
			$this->data=(object)array(
				'user'=>empty($input->user)?0:(int)$input->user,
				'start'=>!empty($_GET['start'])?$_GET['start']:(!empty($_COOKIE['start'])?$_COOKIE['start']:date("Y-m-d",time()-604800)),
				'stop'=>!empty($_GET['stop'])?$_GET['stop']:(!empty($_COOKIE['stop'])?$_COOKIE['stop']:date("Y-m-d")),
			);
		}elseif($input->act=='socStat'){
			$this->data=(object)array(
				'start'=>!empty($_GET['start'])?$_GET['start']:(!empty($_COOKIE['start'])?$_COOKIE['start']:date("Y-m-d",time()-604800)),
				'stop'=>!empty($_GET['stop'])?$_GET['stop']:(!empty($_COOKIE['stop'])?$_COOKIE['stop']:date("Y-m-d")),
			);
		}elseif($input->act=='imgRecount'&&$input->easy){
			$this->data=(object)array(
				'pid'=>$input->pid,
			);
		}elseif($input->act=='imgRecountAll'){
			$this->data=(object)array();
		/*
		* Обновляем статус поста
		*/
		}elseif($input->act=='published_update'){
			$this->data=(object)array(
				'pid'=>(int)$_GET['pid'],
				'published'=>db::escape($_GET['published']),
			);
		}elseif($input->act=='diff'){
			$this->data=(object)array(
				'id'=>isset($input->pid)?(int)$input->pid:0,
				'from'=>isset($input->from)?(int)$input->from:0,
				'to'=>isset($input->to)?(int)$input->to:0,
			);
		}elseif($input->act=='archive'){
			$this->data=(object)array(
				'id'=>isset($input->pid)?(int)$input->pid:0,
			);
		}elseif($input->act=='setAuthors'){
			// получаем список id постов
			$ids = array_map(function($arg){
				return (int)$arg;
			}, array_keys($_POST['select_author_checkbox']));
						
			$this->data=(object)array(
				'ids'=>$ids,
				'author'=>(int)$_POST['select_author_options'],
			);
		}elseif($input->act=='updateLike'){
			$this->data=(object)array(
				'pid'=>(int)$_POST['pid'],
				'isLike'=>(int)$_POST['isLike'],
			);
		}elseif($input->act=='updateFBpublish'){
			$this->data=(object)array(
				'pid'=>(int)$_POST['pid'],
				'isDone'=>(int)$_POST['isDone'],
			);
		}elseif($input->act=='loadStruct'){
			$this->data=(object)array('file'=>!empty($_FILES['structfile']['tmp_name'])?$_FILES['structfile']:false);
		}else
			$this->act='';
	}
}
