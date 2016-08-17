<?php
class plugins_css extends control{
	function __construct($input=''){
		$this->data=new stdClass;
		if(empty($input->act)) $input->act='index';
		
		if($input->act=='index'){
			$this->data=(object)array(
				'debug'=>isset($_GET['debug'])?true:false,
				'default'=>isset($_GET['default'])?true:false,
				'theme'=>isset($_GET['theme'])?true:false,
			);
		}else
			$this->act=false;
	}
}