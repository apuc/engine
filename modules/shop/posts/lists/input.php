<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
class shop_posts_lists extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		#default
		if(empty($input->act)) $input->act='mainList';
		$this->act=$input->act;
		
		if($input->act=='mainList'){
			$this->data=(object)array(
				'category'=>empty($input->cat)?'':db::escape(urldecode($input->cat)),
				'uid'=>@(int)$input->uid,
				'page'=>empty($input->page)?1:$input->page,
				'num'=>cookiePage($input,'num',10),
				'uri'=>empty($input->uri)?'':$input->uri,
				'popularPostsCount'=>empty($input->popularPostsCount)?4:$input->popularPostsCount,
				'excludeCat'=>empty($input->excludeCat)?false:$input->excludeCat,
			);
		}else{
			$this->act='';
		}
	}
}