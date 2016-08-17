<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class posts_lists extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		#default
		if(empty($input->act)) $input->act='mainList';
		
		if($input->act=='mainList'){
			$this->data=(object)array(
				'category'=>empty($input->cat)?'':db::escape(urldecode($input->cat)),
				'prfxtbl'=>empty($input->prfxtbl)?'':$input->prfxtbl,
				'uid'=>@(int)$input->uid,
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
				'uri'=>empty($input->uri)?'':$input->uri,
				'popularPostsCount'=>empty($input->popularPostsCount)?4:$input->popularPostsCount,
				'excludeCat'=>empty($input->excludeCat)?false:$input->excludeCat,
			);
		}elseif($input->act=='subCatList'){
			$this->data=(object)array(
				'category'=>empty($input->cat)?'':db::escape(urldecode($input->cat)),
				'prfxtbl'=>empty($input->prfxtbl)?'':$input->prfxtbl,
				'num'=>empty($input->num)?5:$input->num,
			);
		}elseif($input->act=='listVote'){
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
			);
		}elseif($input->act=='listMyVote'){
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
			);
		}elseif($input->act=='listByUser'){
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
				'user'=>@(int)$input->uid,
			);
		}elseif($input->act=='random'){
			$this->data=(object)array(
				'num'=>empty($input->num)?20:$input->num,
			);
		}elseif($input->act=='top'){
			$this->data=(object)array(
				'page'=>empty($input->page)?1:$input->page,
				'num'=>empty($input->num)?20:$input->num,
			);
		}else{
			$this->act=false;
		}
	}
}