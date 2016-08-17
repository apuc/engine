<?php
/*
	Класс управления маршрутами
	примеры создания маршрутов (urls.php):
		route::set('[pregexp](.*?)',array('param1'));
		route::set('[pregexp](.*?)',array('module'=>'posts','param1'));
		route::set('[pregexp](.*?)(.*?)',array('module'=>'posts','act'=>'post','param1','param2'));
*/
class route{
	public static $routeList;
	/*
		Метод установки маршрута
			(string)mask - regular expression
			(array)params - аналог GET параметров
			(int)priority - приоритет проверки, как последовательность выполнения в .htaccess
				0 - самый низкий приоритет
				1 - по умолчанию
				если = 0 - url может попасть под другое правило, если оно существует
				если > 0 - зависит от величины
			Примечание: если существует маршрут для модуля custom, то он не переопределяется основным модулем
	*/
	static function set($mask,$params,$priority=1){
		if(empty($params['module'])) $params['module']='index';
		if(isset(self::$routeList[$priority][$mask])){
			if(
				substr($params['module'],0,7)!='custom/'&&
				substr(self::$routeList[$priority][$mask]['module'],0,7)=='custom/'
			) return;
		}
		self::$routeList[$priority][$mask]=$params;
	}
	/*
		Загружает установки для роутинга из всх модулей
			подключает файлы urls.php
	*/
	static function load($themePath=false){
		# маршрут для главной
		self::lookupRec(PATH.'modules');
		if($themePath){
			if(file_exists($themePath))
				self::lookupRec($themePath);
		}
		krsort(self::$routeList);
	}
	/*
		Рекурсивно подключает файлы urls.php в каталогах модулей
	*/
	static function lookupRec($basedir,$rec=false){
		global $urls;
		# ограничение вложенности каталогов
		static $limit;
		if($rec) $limit++;
		if($limit>=10) return;
		foreach (scandir($basedir) as $dirName) {
			if(substr($dirName,0,1)=='.'||in_array($dirName,array('files','tmp','tpl'))) continue;
			if(!is_dir($tdir="$basedir/$dirName")) continue;
			$inc="$tdir/urls.php";
			if(file_exists($inc)){
				include_once $inc;
			}
			if(!$rec) $limit=0;
			self::lookupRec($tdir,true);
		}
	}
	/*
		Парсит URI строку и получает маршрут
		return 
			(string) obj->module
			(array) obj->params
	*/
	static function get($uri){
		$uri=substr($uri,strlen(preg_replace("!^http://[^/]+!",'',HREF)));
		foreach (self::$routeList as $prior => $routes) {
			foreach ($routes as $mask => $params) {
				if(preg_match("!{$mask}!ui",$uri,$m)){
					array_shift($m);
					$res=new StdClass;
					$res->module=$params['module'];
					$res->params=array();
					unset($params['module']);
					foreach ($params as $key => $v) {
						if(is_numeric($key)){
							if(isset($m[$key])) $res->params[$v]=$m[$key];
							unset($params[$key]);
						}else
							$res->params[$key]=$v;
					}
					break(2);
				}
			}
		}
		return $res;
	}
}

?>
