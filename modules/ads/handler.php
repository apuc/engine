<?php
namespace ads;
use module,db,url;
class handler{
	function __construct(){
		$this->template='';
	}
	function index($channel){
		$ads=new \stdClass;
		if(file_exists($path=THEME_PATH."/ads/tpl/{$channel}.php"))
			include_once($path);
		elseif(file_exists($path=module::$path."/ads/tpl/{$channel}.php"))
			include_once($path);
		return $ads;
	}
}
