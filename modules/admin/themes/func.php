<?
namespace admin_themes;
use module,db,url,cache,stdClass;
use SplFileInfo;

/*
	список предустановленных тем
*/
function preThemes(){
	$prethemes=array();
	$dh=opendir($dir=PATH.'pre_themes');
	while ($file=readdir($dh)) {
		if($file=='.'||$file=='..'||!is_dir($dir.'/'.$file)) continue;
		$prethemes[]=$file;
	}
	sort($prethemes);
	return $prethemes;
}
/*
	получает список тем
*/
function themes(){
	$themes=array();
	$dh=opendir($dir=PATH.'themes');
	while ($file=readdir($dh)) {
		if($file=='.'||$file=='..'||!is_dir($dir.'/'.$file)) continue;
		$themes[$file]=$file;
	}
	asort($themes);
	return $themes;
}

/*
	рекурсивно находит файлы темы
		- расширенный режим
			- содержимое всех каталогов темы
		- обычный режим
			- только содержимое каталогов tpl в теме
*/
function findTpl($dir,&$struct,$ext=false){
	$dh=opendir($dir);
	while($file=readdir($dh)){
		if($file=='.'||$file=='..'||preg_match('!^\..+$!', $file)) continue;
		$tdir=$dir.'/'.$file;
		#пропускаем admin директории
		if($file=='admin') continue;
		if($ext){
			if(is_dir($tdir))
				readTplDir($tdir,$struct,$ext);
			else
				$struct[]=$tdir;
		}else{
			#пропускаем все файлы до tpl
			if(!is_dir($tdir)) continue;
			if($file=='tpl')
				readTplDir($tdir,$struct);
			else
				findTpl($tdir,$struct,$ext);
		}
	}
	return $struct;
}
/*
	рекурсивно читает каталоги tpl в теме
*/
function readTplDir($dir,&$files,$ext=false){
	$dh=opendir($dir);
	while($file=readdir($dh)){
		if($file=='..'||preg_match('!^\..+!', $file)) continue;
		$tdir=$dir.'/'.$file;
		if(is_dir($tdir)&&$file!='.')
			readTplDir($tdir,$files,$ext);
		else{
			#пропускаем backup файлы и css файлы
			if(preg_match('!(?:\.[^\.]+\.\d+|\.css)$!', $file)&&!$ext)
				continue;
			$files[]=$tdir;
		}
	}
}
/*
	получает структуру файлов темы
*/
function makeDirTree($files,$theme){
	$struct=array();
	if(!empty($files)){
		foreach ($files as $f) {
			$file=str_replace(PATH.'themes/'.$theme.'/', '', $f);
			$arrPath=explode('/', $file);
			$struct=array_merge_recursive($struct,buildPathTree($arrPath));
		}
	}
	return $struct;
}
/*
	рекурсивно строит дерево по пути файла
*/
function buildPathTree($arr){
	$struct=array();
	$key=key($arr);
	$v=current($arr);
	unset($arr[$key]);
	if($v!='.'){
		if(!empty($arr))
			$struct[$v]=buildPathTree($arr);
		else
			$struct[$v]='';	
	}
	return $struct;
}
/*
	проверяет и возвращает путь к файлу
*/
function correctFile($path,$theme){
	if($path[0]=='/') $path=substr($path, 1);
	if(!file_exists($path=PATH."themes/{$theme}/{$path}"))
		return false;
	else
		return $path;
}
/*
	рекурсивно копирует файлы
		mode: merge - не копировать если существует
*/
function rcopy($src,$dst,$mode='merge'){
	$dir=opendir($src);
	@mkdir($dst);
	while(false!==($file=readdir($dir))) {
		if($file=='.'||$file=='..') continue;
		$s=$src.'/'.$file;
		$d=$dst.'/'.$file;
		if(is_dir($s)){
			rcopy($s,$d);
		}else{
			if($mode=='merge'&&file_exists($d)) continue;
			copy($s,$d);
		}
	}
	closedir($dir);
}
/*
	определдяет путь к css файлу
*/
function cssExists($path){
	if(!preg_match('!\.php$!', $path)) return false;
	$path=preg_replace('!\.php$!', '.css', $path);
	$obj=new stdClass;
	$obj->exists=file_exists($path)?true:false;
	$obj->path=$path;
	return $obj;
}
/*
	проверяет существование указанной темы
		return path
*/
function themeExists($name){
	if(strstr($name, '/')||empty($name)) return false;
	return file_exists($dir=PATH.'themes/'.$name)?$dir:false;
}
