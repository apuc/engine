<?php
/*
	в шаблонах доступны константы:
	THEME_CURRENT - имя текущей темы
	THEME_PATH - путь к корню тем (Например: [DOCUMENT_ROOT]/themes/themename/)
	THEME_HREF - host и часть пути (Например: http://example.com/themes/themename)
*/
#темы для поста, default значение константы
if(!defined('THEME_POST_ALLOW'))
	define('THEME_POST_ALLOW', 1);

class theme{
	public $name;
	public $path;
	public $href;
	function __construct($name=false){
		list($mname)=db::qrow("SELECT `value` FROM `".PREFIX_SPEC."config` WHERE `key`='current_theme' LIMIT 1");
		$this->mname=!empty($mname)?$mname:'';
		if($this->mname!=''){
			$this->mpath=PATH.'themes/'.$this->mname.'/';
		}else{
			$this->mpath=PATH;
		}
			
		if($name!==false)
			$this->name=$name;
		elseif(defined('THEME_SET'))
			$this->name=THEME_SET;
		else{
			$this->name=$this->mname;
		}
	
		if($this->name==''){
			$this->path=PATH;
			$this->href=HREF.'/modules';
		}else{
			$this->path=PATH.'themes/'.$this->name.'/';
			$this->href=HREF.'/themes/'.$this->name;
		}	
	}
	/*
		механизм отслеживания:
			- нужен для определения темы только для одной страницы
			- должен быть определен модуль который следует отслеживать
	*/
	function tracking($module,$params){
		if(!THEME_POST_ALLOW) return false;
		if($module=='posts'&&!empty($params['url'])){
			list($theme)=db::qrow("SELECT `theme` FROM `post` WHERE `url`='".db::escape(urldecode($params['url']))."' LIMIT 1");
		}elseif($module=='posts/lists'&&!empty($params['cat'])){
			list($theme)=db::qrow("SELECT `theme` FROM `category` WHERE `url`='".db::escape(urldecode($params['cat']))."' LIMIT 1");
		}
		return empty($theme)?false:$theme;
	}
	/*
		устанавливает константы тем для испольхования в шаблонах
	*/
	function setconsts(){
		define('THEME_CURRENT', $this->name);
		define('THEME_PATH', $this->path);
		define('THEME_MCURRENT', $this->mname);
		define('THEME_MPATH', $this->mpath);
		define('THEME_HREF', $this->href);
	}
}
?>
