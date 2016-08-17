<?php
/*
* Должен возвращать
* $this->data - объект обработанных входных переменных
* $this->act - какую функцию обработки используем
*/
 class user extends control{
	function __construct($input=''){ # $input - объект входных переменных от других модулей
		$this->data=(object)array();
		if(empty($input->act)) $input->act='index';
		$this->act=$input->act;
		if($input->act=='index'){
			$this->data=(object)array(
				'mail'=>empty($_COOKIE['mail'])?'':$_COOKIE['mail'],
				'pas'=>empty($_COOKIE['pas'])?'':$_COOKIE['pas'],
			);
		}elseif($input->act=='panel'){
			$this->data=(object)array();
		}elseif($input->act=='menu'){
			$this->data=(object)array();
		}elseif($input->act=='data'){
			$this->data=(object)array();
		}elseif($input->act=='login'){
			$this->data=(object)array(
				'mail'=>db::escape(@$input->mail),
				'pas'=>@$input->pas,
				'remember'=>intval(@$input->remember),
				'submit'=>(bool)@$input->type,
				'easy'=>@$input->easy,
			);
		}elseif($input->act=='register'){
			$this->data=(object)array(
				'mail'=>@$input->mail,
				'pas'=>@$input->pas,
				'submit'=>@$input->submit,
				'from'=>@$input->from,
				'easy'=>@$input->easy,
				'phone'=>@$input->phone,
				'address'=>@$input->address,
				'comment'=>@$input->comment,
			);
		}elseif($input->act=='settings'){
			$this->data=(object)array(
				@$_POST['pas'],
				@$_POST['newPas'],
				db::escape(@$_POST['name']),
				db::escape(@$_COOKIE['pas']),
				(bool)@$_POST['submit']
			);
		}elseif($input->act=='activate'){
			$this->data=(object)array(
				db::escape(@$_GET['mail']),
				intval(@$_GET['code'])
			);
		}elseif($input->act=='restore'){
			$this->data=(object)array(
				db::escape(@$_POST['mail'])
			);
		}elseif($input->act=='logout'){
			$this->data=(object)array();
		}elseif($input->act=='rbac'){
			$this->data=(object)array(
				'user'=>@$input->user,
				'type'=>@$input->tipe,
			);
		/**
		 * Авторизация через соцсети
		 */
		}elseif($input->act=='auth'){
			$this->data=(object)array(
				'service'=>@$input->service,
				'redirect'=>isset($_GET['state'])?$_GET['state']:'/',
			);
		}elseif($input->act=='twitterAuth'){
			$this->data=(object)array();
		}elseif($input->act=='tplMail'){
			$this->data=(object)array('key'=>!empty($input->key)?$input->key:'');
		}else
			$this->act='';#вызывается только конструктор
	}
}