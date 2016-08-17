<?
/*
 * Подключение и выполнение модуля
 * $input - объект переданный извне для управления и вывода данных в модуле
 * $easy =1 - не выводить основной шаблон и не обрабатывать глобальные переменныи
 */
module::$path=PATH."modules";

class module{
	public static $path;
	private static $entry;
	private static $admin;
	/*
		params:
			(string)$moduleName - имя модуля
			(object)$input - входные переменные
			(mixed)easy - Параметры представления данных
				$easy = 0 вывод основного HTML шаблона 
				$easy = 'data' вывод массива данных, без HTML
			(int)$denycustom - запрет на подключение custom модуля
	*/
	static function init($moduleName){
		#default moduleName
		if($moduleName=='') $moduleName='index';
		#set module path if not set
		if(empty(self::$path)) self::$path=PATH."modules";
		if(!is_dir(self::$path."/$moduleName")&&!self::customExist($moduleName,''))
			return false;
		#set entry point
		if(empty(self::$entry)){
			self::$entry=$moduleName;
			self::$admin=self::isAdminModule($moduleName);
		}
		return $moduleName;
	}
	static function exec($moduleName='',$input=array(),$easy=0,$denycustom=0){
		if(!$moduleName=self::init($moduleName)) return false;
		#Обработка входных переменных
		if(!is_object($input)) $input=(object)$input;
		@$input->easy=$easy;
		if(self::$admin)
			$input->denycustom=1;
		else
			$input->denycustom=empty($denycustom)?0:$denycustom;
		$control=self::control($moduleName,$input,$easy);
		#Преобразование данных для вывода
		$input=(object)$input;
		list($handler,$data)=self::handler($moduleName,$control,$input);
		#Вывод из шаблона модуля
		if($easy!=='data'){
			if($easy===0){
				$data->ads=module::exec('ads',array('page'=>$moduleName),'data')->data;
			}
			$tpl=self::view($moduleName,@$control->act,$data,$input->denycustom);
			#Вывод общего шаблона
			if(!empty($handler->template) && $easy===0){
				$str=self::exec($handler->template,$tpl,1,$input->denycustom)->str;
			}else{
				$str=@$tpl->body; $tpl->body='';
			}
			return (object)array(
				'handler'=>$handler,
				'str'=>$str,
				'tpl'=>$tpl,
			);
		}else{
			return (object)array(
				'handler'=>$handler,
				'data'=>$data,
			);
		}
	}
	#Обработка входных переменных
	static function control($moduleName,$input,$easy){
		if(empty($input))$input=(object)array();
		if(!is_object($input))$input=(object)$input;
		$class=str_replace("/",'_',$moduleName);
		if(($path=self::customExist($moduleName,'input.php'))&&!$input->denycustom){
			include_once($path);
		}elseif(file_exists($path=self::$path."/$moduleName/input.php")&&!class_exists($class)){
			include_once($path);
		}
		if(class_exists($class)){
			if(empty($easy)){
				$get=add2obj($input,$_GET);
				$get=add2obj($get,$_POST);
				$get=add2obj($get,@$_SESSION);
				$get=add2obj($get,$_COOKIE);
			}else $get=$input;
			$control=new $class($get);
		}else{
			trigger_error("System error: input [class \"{$class}\"] is not exists, check input.php",E_USER_NOTICE);
			$control=(object)array();
		}
		if($control->act===false) $control->act='';
		elseif(empty($control->act) && !empty($input->act)) $control->act=$input->act;
		return $control;
	}
	/*
		Преобразование данных для вывода
		Если существует custom`ный модуль то используется его namespace
	*/
	static function handler($moduleName,$control,$input){
		if(file_exists($path=self::$path."/$moduleName/handler.php")){
			include_once($path);
			$moduleNameHandler=str_replace("/",'_',$moduleName);
		}
		if(($path=self::customExist($moduleName))&&!$input->denycustom){
			include_once($path);
			$moduleNameHandler='custom_'.str_replace("/",'_',$moduleName);
		}
		if(!empty($moduleNameHandler)){
			$class="\\{$moduleNameHandler}\handler";
			if(!class_exists($class)){
				trigger_error("System error: Handler [{$class}] is not exists ",E_USER_NOTICE);
			}
			$handler=new $class;
			$act=(empty($control->act))?$moduleNameHandler:$control->act;
			if(empty($control->data))$control->data=(object)array();
			$data=add2obj($control->data,$input);
			if(method_exists($handler,$act)){
				#enable cache
				if(class_exists('cache')&&property_exists($class, 'cache')&&!$input->denycustom){
					if(in_array($act,array_keys($handler->cache))){
						#create cache ID 
						$cacheid=false;
						if(isset($handler->cache[$act][0])){
							$id=$handler->cache[$act][0];
							if(is_array($id)){
								foreach ($id as $valId) {
									if(isset($data->{$valId}))
										$tid[]=$data->{$valId};
								}
								$cacheid=implode('/', $tid);
							}else
								$cacheid=isset($data->{$id})?$data->{$id}:0;
						}
						$exp=empty($handler->cache[$act][1])?'10 min':$handler->cache[$act][1];
						$cachedData=cache::getHandler(array($handler,$act),(array)$data,$cacheid,$exp);
						#если закеширован сам объект handler, то достаем и его
						if($cachedData->handler)
							$handler=$cachedData->handler;
					}
				}
				$data=isset($cachedData->result)?$cachedData->result:call_user_func_array(array($handler,$act),(array)$data);				
			}else{
				trigger_error("System error: method [{$act}] for [\\{$moduleNameHandler}\Handler] is not exists. [PS. Возможно нужно наследовать родительский Handler]",E_USER_NOTICE);
			}
			$data=add2obj($data,$input);
			if(!empty($control) && @$handler->headers)$control->__stop($handler->headers);
		}else $data=(object)array();
		return array($handler,$data);
	}
	#Делаем вывод данных модуля
	static function view($moduleName,$act,$data,$denycustom){
		$template=new subTemplate($moduleName,$denycustom);
		$tpl=new stdClass;
		$filename="tpl/$act.php";
		#подключаем шаблоны описанные в одном файле, в функциях
		if(file_exists($path=self::$path."/$moduleName/tpl/tplFunc.php")){
			include_once($path);
		}
		if(($path=self::customExist($moduleName,'tpl/tplFunc.php'))&&!$denycustom){
			include_once($path);
		}
		#Подключаем custom/tpl-$act.php
		if(($path=self::customExist($moduleName,$filename))&&!$denycustom){
			ob_start();
			include($path);
			$tpl->body=ob_get_contents();
			ob_end_clean();
		#Выполняем функцию из custom/$module/tplFunc.php
		}elseif(class_exists($moduleNameTplCustom='custom_'.str_replace("/",'_',$moduleName)."Tpl")&&!$denycustom){
			$view=new $moduleNameTplCustom;
			if(method_exists($view,$act)){
				ob_start();
				$tpl=call_user_func_array(array($view,$act),(array)$data);
				$tpl->body=ob_get_contents();
				ob_end_clean();
			}
		#Подключаем $module/tpl-$act.php
		}elseif(file_exists($path=self::$path."/$moduleName/$filename")){
			ob_start();
			include($path);
			$tpl->body=ob_get_contents();
			ob_end_clean();
		#Выполняем функцию из $module/tplFunc.php
		}elseif(class_exists($moduleNameTpl=str_replace("/",'_',$moduleName)."Tpl")){
			$view=new $moduleNameTpl;
			if(method_exists($view,$act)){
				ob_start();
				$tpl=call_user_func_array(array($view,$act),(array)$data);
				$tpl->body=ob_get_contents();
				ob_end_clean();
			}
		}
		#Если не один из спец шаблонов не создан выполняем общий шаблон для всего модуля
		if(!isset($tpl->body)){
			$filename="tpl/tpl.php";
			ob_start();
			if(($file=self::customExist($moduleName,$filename))&&!$denycustom){
				include($file);
			}elseif(file_exists($file=self::$path."/$moduleName/$filename")){
				include($file);
			}else{print_r($data);}
			$tpl->body=ob_get_contents();
			ob_end_clean();
		}
		return $tpl;
	}
	/*
		проверяет существует ли custom`ный файл для модуля
	*/
	static function customExist($name,$file='handler.php'){
		if(file_exists($path=THEME_PATH."/$name/$file"))
			return $path;
		else
			return false;
	}
	/*
		определяет яввляется ли модуль административным
	*/
	static function isAdminModule($moduleName){
		return (preg_replace(array('!^admin/?.*$!','!^.+/admin$!','!^.+/admin/.*$!'), '', $moduleName)=='')?true:false;
	}
}
#Общий клас для обработки входных параметров и передачи их в заголовки
class control{
	public $act='';
	function __stop($headers){#Для установки заголовков. Все переменные для обработки лежат в $this->headers
		if(!empty($headers->cookie))foreach($headers->cookie as $k=>$v){
			@setcookie($k,(string)$v[0], (int)strtotime((string)$v[1]),"/",COOKIE_HOST);
			#print "$k - ".((string)$v[0])." -- ".(string)$v[1]." --- ".COOKIE_HOST."<br>\n";
		}
		if(!empty($headers->location)) location($headers->location);
	}
}

/*
	класс для работы с подшаблонами
		-зависит от модуля theme.class.php
*/
class subTemplate{
	function __construct($moduleName,$denycustom=0){
		$this->moduleName=$moduleName;
		$this->denycustom=$denycustom;
	}
	/*
		для подключения файлов подшаблонов
			- либо из шаблона текущей темы, если существует
			- либо из главного шаблона
	*/
	function inc($path){
		if($path[0]=='/') $path=substr($path, 1);
		$path=$this->moduleName.'/tpl/'.$path;
		$mfile=PATH.'modules/'.$path;
		if($this->denycustom||THEME_CURRENT=='')return $mfile;
		if(file_exists($file=THEME_PATH.$path)){
			return $file;
		}elseif(file_exists($file=THEME_MPATH.$path)){
			return $file;
		}else{
			$file=$mfile;
		}
		return $file;
	}
}
