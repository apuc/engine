<?php
namespace admin_loadstruct;
use module,db,url,cache;
#Используем сторонние модули
require_once(module::$path.'/posts/admin/handler.php');
#подключаем функции
require_once(module::$path.'/admin/loadstruct/func.php');

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=new \stdClass;
		$this->uhandler=module::exec('user',array('easy'=>1),1)->handler;
		$this->user=$this->uhandler->user;
	}
	/*
		Загрузка структуры категорий и постов на базе директорий и файлов
	*/
	function loadStruct($file,$ftpfile,$repeatKeys,$tblprefix,$kwastitle,$autoposting){
		set_time_limit(0);
		ini_set('memory_limit', '512M');
		if(!$this->uhandler->rbac('loadStruct')) die('forbidden');
		$ftpStructName=PATH.'struct.zip';

		$tbl=\posts\tables::init($tblprefix);
		if(!empty($tblprefix))
			module::exec('posts/admin',array('act'=>'install'),1);
			module::exec('category/admin',array('act'=>'install'),1);
		$log=$msg='';
		if($file!==false||$ftpfile!==false){
			$fname=$file?$file['tmp_name']:$ftpStructName;
			$zip=new \ZipArchive;
			if($zip->open($fname)===true){
				$zip->close();
				if(mkdir($tmpDir=sys_get_temp_dir().'/'.SITE.'_'.time())){
					exec("unzip {$fname} -d {$tmpDir}");
					#Проверяем записана ли база сразу или только в подпапке
					$base=scandir($baseDir=$tmpDir);
					if(count($base)==3)
						$base=scandir($baseDir=$tmpDir.'/'.$base[2]);
					# построение структуры
					$struct=searchStruct($baseDir);
					if(!empty($struct)){
						#запись данных
						$log=(object)array();
						storeStruct($struct,$this->user->id,$log,$repeatKeys,$kwastitle,$autoposting,$tbl);
						#print_r($log);exit;
						# обновляем количество постов в категориях
						module::exec('category',array('act'=>'updateCount','cats'=>'all','tbl'=>$tbl->post),'data');
					}
					exec("rm -rf {$tmpDir}");
				}else $msg=sys_get_temp_dir().' is not writable';
			}else $msg='Archive open error';
		}
		#print_r($log);exit;
		return array(
			'log'=>@$log,
			'message'=>$msg,
			'sizelimit'=>ini_get('upload_max_filesize'),
			'tblprefix'=>$tblprefix,
			'prefixlist'=>\posts_admin\getPrefixList(),
			'detectFTPupload'=>file_exists($ftpStructName),
		);
	}
}
