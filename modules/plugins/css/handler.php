<?php
/*
	Использование файла стилей
		//example.com/style.css
		//example.com/style.css?nocache=1 - показывает актуальную версию файла, обновляет cache
		//example.com/style.css?debug=1 - показывает файл с метками о подключенных файлах, без компрессии
		//example.com/style.css?default=1 - подключает только стили из modules/*, без сохранения
		//example.com/style.css?theme=1 - подключает только стили из theme/[THEME_NAME]/*, без сохранения
*/
namespace plugins_css;
use module,db,url,cache;

include_once module::$path.'/admin/themes/handler.php';

class handler{
	function __construct(){
		$this->template='';
		$this->headers=new \stdClass;
	}
	function index($debug,$default,$theme){
		header("Content-Type: text/css");
		if($default)
			$mode='default';
		elseif($theme)
			$mode='theme';
		else
			$mode=false;
		$css=new css($debug,$mode);
		#print_r($css->included);
		return (object)array(
			'str'=>$css->style,
			'time'=>$css->store(true),
		);
	}
}

/* 
	сборщик стилей
	- подключает рядом лежащие css файлы в tpl директориях (например: для example.php подключит example.css)
*/
class css{
	public $included=array();
	public $debug;
	public $mode;
	
	function __construct($debug=false,$mode=false){
		$this->debug=$debug;
		$this->mode=$mode;
		if($mode=='default') $this->modulesInc(module::$path);
		elseif($mode=='theme') $this->themesInc(substr(THEME_PATH, 0,-1));
		else{
			$this->modulesInc(module::$path);
			$this->themesInc(substr(THEME_PATH, 0,-1));
		}
		$this->style=$this->inc();
		$this->tmp=TMP.__NAMESPACE__;
	}
	/*
		находит tpl директории
	*/
	function modulesInc($basedir,$findcss=false,$themeinc=false){
		$dh=opendir($basedir);
		while ($dirName=readdir($dh)) {
			if(substr($dirName,0,1)=='.'||in_array($dirName, array('example','images'))) continue;
			if(is_dir($path="$basedir/$dirName")){
				if($dirName=='tpl') $findcss=true;
				elseif($dirName=='files') continue;
				$this->modulesInc($path,$findcss,$themeinc);
			}elseif($findcss)
				$this->collect($path,$themeinc);
		}
		closedir($dh);
	}
	/*
		находит tpl директории, для файлов темы
	*/
	function themesInc($basedir,$findcss=false){
		if(THEME_CURRENT=='') return;
		$this->modulesInc($basedir,$findcss,true);
	}
	/*
		подключает css файлы
		- либо из темы, либо основной
	*/
	function collect($path,$themeinc){
		$css=\admin_themes\cssExists($path);
		if($css===false) return;
		elseif($css->exists){
			#если находится в файлах темы
			if($themeinc){
				$extpath=str_replace(THEME_PATH, module::$path.'/', $css->path);
				if(isset($this->included[$extpath])){
					unset($this->included[$extpath]);
				}
			}
			$this->included[$css->path]=1;
		}
	}
	/*
		убирает лишние пробелы, переносы строк, комментарии
	*/
	function compress($str){
		if(!$this->debug) $str=preg_replace(
			array('!/\*.*?\*/!si','!\r?\n!','!\t!','!\s{2,}!','!\s*([\}\{\:\;])\s*!'), 
			array('','',' ',' ','$1'),
			$str
		);
		return $str;
	}
	/*
		сохраняет файл стилей, если он не существует
	*/
	function store($force=false){
		if($this->mode=='default'||$this->mode=='theme') return '';
		$time=false;
		$filename=$this->tmp.'/style.css';
		if($force||!file_exists($filename)){
			@mkdir($this->tmp);
			if(is_writable($this->tmp)){
				$time=date('Y-m-d H:i:s');
				if(!$this->debug) file_put_contents($filename, "/* {$time} */".$this->style);
			}
		}
		return $time;
	}
	/*
		подключает содержимое css файлов
	*/
	function inc(){
		ob_start();
		if(!empty($this->included))
			foreach ($this->included as $p => $v) {
				if($this->debug) echo "\n/* ".str_replace(PATH,'',$p)." */\n";
				include_once $p;
			}
		return $this->compress(ob_get_clean());
	}
}