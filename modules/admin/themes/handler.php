<?php
namespace admin_themes;
use module,db,url,cache;

require_once(module::$path.'/admin/themes/func.php');
require_once(module::$path.'/plugins/css/handler.php');

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=new \stdClass;
		$this->userHandler=module::exec('user',array(),1)->handler;
		$this->user=$this->userHandler->user;
	}
	function index(){#Делаем все обработки для вывода данных
		if(!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		$gencss=new \plugins_css\css;
		$gencss->store(1);
		$found=false;
		#получаем список тем
		$themes=themes();
		if(isset($themes[THEME_CURRENT])){
			unset($themes[THEME_CURRENT]);
			$found=true;
		}
		if(THEME_CURRENT!=''){
			$themes[]='default';
			$current=THEME_CURRENT;
		}else{
			$found=true;
			$current='default';
		}

		return (object)array(
			'found'=>$found,
			'current'=>$current,
			'themes'=>$themes,
			'prethemes'=>preThemes(),
		);
	}
	/*
		устанавливает новую текущую тему
	*/
	function set($settheme){
		if(!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		if($settheme===false) return;
		$sqlSettheme=db::escape($settheme);
		db::query("INSERT INTO `".PREFIX_SPEC."config` (`key`,`value`) VALUES('current_theme','{$sqlSettheme}')
			ON DUPLICATE KEY UPDATE value='{$sqlSettheme}'");
		$this->headers->location=url::admin_themes();
	}
	/*
		возвращает ошибки состояния модуля
	*/
	function status(){
		$status='';
		if(!is_writable($dir=PATH.'themes'))
			$status='notwritable';
		return (object)array('dir'=>$dir,'status'=>$status);
	}
	/*
		использование предустановленой темы
	*/
	function usePreTheme($prethemes){
		if(!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		if($prethemes===false) return;
		$src=PATH.'pre_themes/'.$prethemes;
		$tdst=$dst=PATH.'themes/'.$prethemes;
		$i=0;
		while (file_exists($tdst)) {
			$pfx='_'.date('Y-m-d').(isset($usedate)?"($i)":'');
			$tdst=$dst.$pfx;
			$usedate=1; $i++;
		}

		$shout=shell_exec("cp -ra ".escapeshellarg($src)." ".escapeshellarg($tdst)." 2>&1");
		if(empty($shout)){
			$this->headers->location=url::admin_themes();
		}else{
			$data=module::exec('admin/themes',array('act'=>'index'),'data')->data;
			$data->error=substr($shout, 0, 30).'...';
			return $data;
		}
	}
	/*
		страница редактирования темы
	*/
	function edit($theme,$ext,$clone){
		if(!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		if(empty($clone)&&$theme!='malecars')
			$clone='malecars';
		$struct=array();
		if(empty($theme)||!is_dir($dir=PATH.'themes/'.$theme)) return;
		$cloneStruct=array();
		$cloneThemeDir=array();
		if(!empty($clone)){
			$cloneThemeDir=makeDirTree(findTpl(PATH.'themes/'.$clone,$cloneStruct,$ext),$clone);	
		}
		
		$themesList=themes();
		unset($themesList[$theme]);
		return (object)array(
			'access'=>$this->userHandler->access(),
			'theme'=>$theme,
			'dir'=>makeDirTree(findTpl($dir,$struct,$ext),$theme),
			'themesList'=>$themesList,
			'ext'=>$ext,
			'cloneThemeDir'=>$cloneThemeDir,
			'clone'=>$clone,
		);
	}
	/*
		удаление темы
	*/
	function del($theme){
		if(!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		if(empty($theme)||!is_dir($dir=PATH.'themes/'.$theme)) return;
		$shout=shell_exec('rm -rf '.escapeshellarg($dir).' 2>&1');
		if(empty($shout)){
			$this->headers->location=url::admin_themes();
		}else{
			$data=module::exec('admin/themes',array('act'=>'index'),'data')->data;
			$data->error=substr($shout, 0, 30).'...';
			return $data;
		}
	}
	/*
		отправляет содержимое файла для редактирования
		вызывается ajax запросом
	*/
	function openFile($theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		$p=implode('/', $path);
		if(!$path=correctFile($p,$theme))
			die('path is not exists');
		return (object)array(
			'str'=>file_get_contents($path),
		);
	}
	/*
		отправляет содержимое файла для редактирования
		вызывается ajax запросом
	*/
	function openFileCss($theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		$p=implode('/', $path);
		if(!$path=correctFile($p,$theme))
			die('path is not exists');
		if(($css=cssExists($path))!==false)
			$str=$css->exists?file_get_contents($css->path):'';
		else
			$str='nocss';

		return (object)array(
			'str'=>$str,
		);
	}
	/*
		записывает в файл
	*/
	function saveFile($theme,$path,$text,$css){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		$p=implode('/', $path);
		if(!$path=correctFile($p,$theme))
			die('path is not exists');
		if(!is_writable($path))
			die('permission denied');
		#создаем backup файла
		if(file_exists($path))
			//copy($path, $path.'.'.time());
			copy($path, $path.'.'.date('Y-m-d'));
		if(file_put_contents($path, $text)!==false){
			#сохраняем стили, если есть
			$csspath=cssExists($path);
			if($css&&$csspath!==false){
				#создаем backup файла
				if($csspath->exists) {
					copy($csspath->path, $csspath->path.'.'.time());
				}
				echo file_put_contents($csspath->path, $css)!==false?'done':'styles save fail';
				#перегенерируем файл стилей
				$gencss=new \plugins_css\css;
				$gencss->store(1);
			}else
				echo 'done';
		}else
			echo 'save fail';
		die;
	}
	/*
		клонирует tpl из основной темы
		- вызывается ajax запросом
	*/
	function cloneTpl($theme,$path,$clone,$ext){
		$warning='';
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		if(substr($p=implode('/', $path), 0, 1)!='/')
			$p='/'.$p;
		$dir=PATH.'themes/'.$theme;
		$destpath=$dir.$p;
		if(!$srcpath=correctFile($p,$clone))
			die('path is not exists');
		else{
			if(is_dir($srcpath)){
				#копируем всю директорию
				if(file_exists($destpath)){
					$warning='directory updated';
				}
				rcopy($srcpath,$destpath);
			}else{
				#копируем файл
				if(!file_exists($destpath)){
					@mkdir(dirname($destpath),0755,true);
					copy($srcpath, $destpath);
					#копируем css
					if(($csssrc=cssExists($srcpath))!==false)
						if($csssrc->exists){
							$cssdest=cssExists($destpath);
							if(!$cssdest->exists) copy($csssrc->path,$cssdest->path);
						}
				}else
					$warning='file exists';
			}
		}
		$struct=array();
		$struct=findTpl($dir,$struct,$ext);
		return (object)array(
			'warning'=>$warning,
			'dir'=>makeDirTree($struct,$theme),
			'theme'=>$theme,
		);
	}/*
		удаляет tpl
		- вызывается ajax запросом
	*/
	function delTpl($theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		if(substr($p=implode('/', $path), 0, 1)!='/')
			$p='/'.$p;
		$dir=PATH.'themes/'.$theme;
		if(!$srcpath=correctFile($p,$theme))
			die('path is not exists');
		else{
			if(is_dir($srcpath)){
				#удаляем всю директорию
				$shout=shell_exec("rm -rf ".escapeshellarg($srcpath)." 2>&1");
				if(!empty($shout)){
					die(substr($shout, 0, 30).'...');
				}
			}else{
				#удаляем стили, если есть
				if(($css=cssExists($srcpath))!==false)
					if($css->exists) unlink($css->path);
				#удаляем файл
				unlink($srcpath);
			}
		}
		echo 'done';
		die;
	}
	/*
		загружает файл в каталог темы
	*/
	function uploadFile($file,$theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		$mes='';
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			$mes='path fail';
		if(substr($p=implode('/', $path), 0, 1)!='/')
			$p='/'.$p;
		$dest=PATH.'themes/'.$theme.$p.'/'.$file['name'];
		if(!move_uploaded_file($file['tmp_name'], $dest))
			$mes='move uploaded fail';
		return (object)array(
			'filename'=>$file['name'],
			'mes'=>$mes,
		);
	}
	/*
		генерирует базовую тему из tpl файлов движка
	*/
	function genBaseTheme($postCall,$internalCall){
		if(!$postCall&&!$internalCall) return;
		if(!$internalCall&&!$this->userHandler->rbac('themesSet')){
			$this->headers->location=HREF; return;
		}
		$this->template='';
		$tplFiles=array();
		$tplFiles=findTpl(module::$path,$tplFiles);

		if(!empty($tplFiles)){
			#удаляем старую тему
			if(file_exists($themedir=PATH.'themes/malecars'))
				shell_exec("rm -rf {$themedir}");
			#копируем новую версию
			foreach ($tplFiles as $file) {
				$dest=str_replace(module::$path, $themedir, $file);
				if(is_dir($file)) continue;
				@mkdir(dirname($dest),0755,true);
				if(!copy($file, $dest)){
					shell_exec("rm -rf {$themedir}");
					$mes='copy fail';
					break;
				}
				#копируем css
				if(($csssrc=cssExists($file))!==false)
					if($csssrc->exists){
						$cssdest=cssExists($dest);
						if(!$cssdest->exists) copy($csssrc->path,$cssdest->path);
					}
			}
		}else $mes='tpl empty';
		if(empty($mes)) $mes='done';
		if($postCall) die($mes);
		else return (object)array('message'=>$mes);
	}
	/*
		создание новой темы
		- вызывается post запросом из js 
	*/
	function newTheme($name){
		if(!$this->userHandler->rbac('themesSet')||empty($name))
			return;
		$data=new \stdClass;
		if(strstr($name, '/'))
			$data->mes='Invalid name';
		else{
			if(file_exists($themedir=PATH.'themes/'.$name))
				$data->mes='Theme exists';
			elseif(!mkdir($themedir,0755))
				$data->mes='create theme error';
			$data->html=module::exec('admin/themes',array('act'=>'edit','theme'=>$name),1)->str;
		}
		return $data;
	}

	function createDir($theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		$mes='';
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		if(substr($p=implode('/', $path), 0, 1)!='/')
			$p='/'.$p;
		$dir=PATH.'themes/'.$theme;
		preg_match('!\/([^\/]+)$!', $p, $m);
		$filename=!empty($m[1])?$m[1]:'';
		$targetDir=str_replace($filename, '', $p);
		mkdir($dir . $targetDir . $filename);
		return (object)array(
			'filename'=>$filename,
			'mes'=>$mes,
			'dir' => $targetDir
		);
	}

	/*
		создает новый tpl файл
	*/
	function createTpl($theme,$path){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		$mes='';
		#отключаем основной шаблон
		$this->template='';
		if(!$path||!is_array($path)||!$theme)
			die('path fail');
		if(substr($p=implode('/', $path), 0, 1)!='/')
			$p='/'.$p;
		$dir=PATH.'themes/'.$theme;
		preg_match('!\/([^\/]+)$!', $p, $m);
		$filename=!empty($m[1])?$m[1]:'';
		$targetDir=str_replace($filename, '', $p);		
		if(!strstr($p,'../')&&correctFile($targetDir,$theme)&&preg_match('!.+\.(?:php|js|css|html|txt)$!', $filename)){
			$destpath=$dir.$p;
			if(!touch($destpath))
				$mes="Can not create {$destpath}";
		}else
			$mes="Incorrect filename";
		
		return (object)array(
			'filename'=>$filename,
			'mes'=>$mes,
		);
	}
	/*
		export файла темы
			- создает архив и отправляет его в качестве ответа на запрос
	*/
	function exportTpl($theme){
		if(!$this->userHandler->rbac('themesSet')){
			die("Forbidden");
		}
		set_time_limit(15);
		$this->template='';
		if(($path=themeExists($theme))===false) die('theme is not exists');
		@mkdir($tmpDir=TMP.__NAMESPACE__.'/export',0755,true);
		if(!is_writable($tmpDir)) die('fail, tmp is not writable');

		$zip=new \ZipArchive;
		if($zip->open($zipfile=tempnam($tmpDir,''))===true){
			$files=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),\RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $f) {
				if(in_array(substr($f, strrpos($f, '/')+1), array('.', '..','.git'))||strstr($f,'/.git/'))
					continue;
				#пропускаем файлы истории
				if(preg_match('!/[^\/]+\.\d+$!', $f)) continue;
				if(is_dir($f))
					$zip->addEmptyDir(str_replace($path, $theme, $f));
				else
					$zip->addFile($f,str_replace($path, $theme, $f));
			}
		}else die('fail, create zip error');
		$zip->close();
		header('Content-Type: application/zip');
		header('Content-Length: '.filesize($zipfile));
		header('Content-Disposition: attachment; filename="'.$theme.'.zip"');
		readfile($zipfile);
		unlink($zipfile);
		die();
	}
	/*
		Импорт темы из zip архива
	*/
	function importTpl($file){
		$this->template='';
		if(!$this->userHandler->rbac('themesSet')) die("Forbidden");
		$msg='done';
		ini_set('memory_limit', '512M');
		if($file!==false){
			$zip=new \ZipArchive;
			if($zip->open($file['tmp_name'])===true){
				$zip->close();
				if(mkdir($tmpDir=sys_get_temp_dir().'/'.SITE.'_'.time())){
					exec("unzip {$file['tmp_name']} -d {$tmpDir}");					
					$sd=scandir($tmpDir);					
					if(count($sd)==3){
						foreach ($sd as $f) {
							if($f=='..'||$f=='.') continue;
							$tsrc=$tmpDir.'/'.$f;
						}
						if(file_exists($dest=PATH.'themes/'.$f))
							$dest.='_'.date('Y-m-d H:i:s');
						rcopy($tsrc, $dest);
					}else $msg='struct of theme archive is wrong';

					exec("rm -rf {$tmpDir}");
				}else $msg=sys_get_temp_dir().' is not writable';
			}else $msg='archive open error';
		}else $msg='upload error';

		die($msg);
	}
}
