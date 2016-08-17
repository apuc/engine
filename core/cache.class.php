<?php
/*
	popularPosts($post,1000); - time 0.2
	cache::get(__NAMESPACE__.'\popularPosts',array($post,1000),$post->url); - time 0.02 (530kb file)

	examples:
	1)
	$data->makes=cache::get(
		'module::exec',
		array(
			'category',
			array('act'=>'makes','tbl'=>self::tbl)
			,1
		)
	)->str;

	2)
	cache::get(__NAMESPACE__.'\popularPosts',array($post,1000),$post->url);
*/
define('CACHE_DIR',PATH.'cache');
if(!defined('CACHE_DEBUG'))
	define('CACHE_DEBUG',0);

class cache{
	static $storeHandler=false;
	/*
		Получает данные из кэша

		(string) funcName - название функции/метода
		(mixed) params - аргументы функции/метода
		(int)(string) id - уникальный идентификатор кеша (например, если нужно закешировать функцию только с определенным набором)
		(int)(string) expire - формат "1 min" или timestamp
	*/
	static function get($funcName,$params='',$id='',$expire='10 min'){
		$time=array();
		$time['start']=microtime(1);
		$time['funcexec']=$time['extract']=0;
		$key=self::genKey($funcName,$id);
		$cached=self::extract($key);
		$time['extract']=microtime(1)-$time['start'];
		$expired=0;
		if(is_array($cached)){
			$data=current($cached);
			$expired=(key($cached)<time());
		}else $expired=1;
		if($expired){
			$tdata=is_array($params)?call_user_func_array($funcName, $params):call_user_func($funcName, $params);
			if(!empty(self::$storeHandler)){
				$data=new StdClass;
				$data->handler=$funcName[0];
				$data->result=$tdata;
			}else
				$data=$tdata;
			
			$time['funcexec']=microtime(1)-$time['start'];
			self::store($key,$data,$expire);
		}
		if(CACHE_DEBUG){
			echo "<!-- Cache summury\nkey: {$key}\ntime: ".(microtime(1)-$time['start'])."\ntime func exec: {$time['funcexec']}\nextract: {$time['extract']}\n -->\n";
		}
		return $data;
	}
	/*
		обертка для cache::get 
		позволяет сохранить объект handler вместе с результатом работы его указанного метода
	*/
	static function getHandler($funcName,$params='',$id='',$expire='10 min'){
		self::$storeHandler=true;
		$data=self::get($funcName,$params,$id,$expire);
		self::$storeHandler=false;
		return $data;
	}
	/*
		Сохраняет данные в файлы
	*/
	static function store($key,$data,$expire){
		$starttime=microtime(1);
		if(!file_exists(CACHE_DIR)) @mkdir(CACHE_DIR);
		if(!is_numeric($expire))
			$expval=strtotime($expire);
		else
			$expval=$expire;
		if(is_writable(CACHE_DIR))
			file_put_contents(CACHE_DIR."/{$key}", serialize(array("{$expval}"=>$data)));
		elseif(CACHE_DEBUG)
			echo "<!-- Cache WARNING: cache dir is not writable -->\n";
		if(CACHE_DEBUG){
			echo "<!-- Cache Store\nkey: {$key}\nexpire set: {$expire}\nexpire: ".date('Y-m-d H:i:s',$expval)."\nstore exec time: ".(microtime(1)-$starttime)."\n -->\n";
		}
	}
	/*
		Распаковывает данные и проверяет expire
	*/
	static function extract($key){
		if(file_exists($cachedFile=CACHE_DIR."/{$key}")){
			if($cached=unserialize(file_get_contents($cachedFile))){
				return $cached;
			}
		}
		return 1;
	}
	/*
		Генерирует базовый идентификатор кэша
		(string) funcName - название функции/метода
		(int)(string) id - уникальный идентификатор кеша [не обязательно]
	*/
	static function genKey($funcName,$id){
		if(is_array($funcName)){
			$class=get_class($funcName[0]);
			$funcName="{$class}::{$funcName[1]}";
		}
		return md5($funcName.$id);
	}
	/*
		очищает кэш с указанным ID
	*/
	static function clear($funcName,$id){
		$key=self::genKey($funcName,$id);
		@unlink(CACHE_DIR."/{$key}");
	}
}
?>
