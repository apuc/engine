<?
#Таймер времени
function timer($type=2,$int=0){
	static $time,$last;
	if(!isset($time))$time=microtime(2);
	if($int)$p=round(microtime(2)-$last,4);
	else $p=round(microtime(2)-$time,4);
	$last=microtime(2);
	if($type==3) $str="<h1>$p</h1>";
	elseif($type==2) $str="<!--$p-->";
	else $str=$p;
	if(in_array($type,array(1,3))) print $str;
	return $str;
}
#редирект
function location($loc){
	if($loc){header("location: ".$loc,true,301);exit;}
}
#Объеденить два объекта
function add2obj($o1,$o2){
	if(empty($o1))$o1=(object)array();
	if(!is_object($o1))$o1=(object)$o1;
	if(count($o2))foreach($o2 as $k=>$v){
		if(!isset($o1->$k))$o1->$k=$v;
	}
	return $o1;
}
#Объеденить два массива
function add2arr(&$ar1,&$ar2){
	foreach($ar2 as $k=>$v){
		if(isset($ar1[$k])){
			if(is_array($ar1[$k]) && is_array($v)){
				add2arr($ar1[$k],$v);
			}elseif(is_numeric($ar1[$k]) && is_numeric($v)){
				$ar1[$k]+=$v;
			}else{
				$ar1[$k]=$v;
			}
		}else{
			$ar1[$k]=$v;
		}
	}
}
#Рекурсивно создать директорию
function mkdirRec($dir,$mod=0777){
	if(!is_dir($bdir=dirname($dir))){
		mkdirRec($bdir);
	}
	if(!is_dir($dir)){
		mkdir($dir);
		chmod($dir,$mod);
	}
}
#Обработка урлов
class url{
	static function __callStatic($meth,$args){#Преобразуем урл
		global $urls;
		$url=$urls->$meth;
		#Для случаев когда нужна обработка данных в функции урла
		if(function_exists("url_$meth")){
			return call_user_func_array("url_$meth",$args);
		}
		if(is_array($url)) return url::convert($url[1],$url[0],$args);
		else{
			#Обрабатываем и возвращаем урл
			if(preg_match_all("!\\$[a-z0-9]+!i",$url,$r)){
				return url::convert($url,implode(",",$r[0]),$args);
			}
			return $url;
		}
	}
	static function convert($url,$replace,$args){#Заменяем в строке переменные на нужные параметры
		return str_replace(explode(",",$replace),$args,$url);
	}
}
#получить переменную из $input или из $_COOKIE
function cookiePage($input,$key,$val){
	return !empty($input->$key)?db::escape($input->$key):(!empty($_COOKIE[$key])?db::escape($_COOKIE[$key]):$val);
}
