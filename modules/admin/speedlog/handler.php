<?php
namespace admin_speedlog;
use module,db,url,cache;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=new \stdClass;
	}
	/*
		обработка и вывод содержимого лога
	*/
	function index(){
		set_time_limit(60);
		$file=TMP.__NAMESPACE__.'/speed.log';
		if(!$fh=fopen($file, 'r')) return;

		$groupSpeedData=array('1'=>0,'0.5'=>0,'0.05'=>0,'0'=>0);
		$c=0;
		while ($str=fgets($fh)) {
			$c++;
			$data=explode("\t", $str);
			$data[1]=(float)trim($data[1]);
			foreach ($groupSpeedData as $key => $v) {
				if($data[1]>=(float)$key){
					$groupSpeedData[$key]++;
					break;
				}
			}
			#определяем период данных
			if(!isset($start)) $start=$data[0];
			$end=$data[0];
		}
		fclose($fh);

		return (object)array(
			'speeddata'=>$groupSpeedData,
			'start'=>$start,
			'end'=>$end,
			'count'=>$c,
		);
	}
	/*
		запись лога
	*/
	function write($moduleName){
		$this->template='';
		@mkdir($tmp=TMP.__NAMESPACE__);
		if(!is_writable($tmp)||preg_match('!^admin\/!i', $moduleName)) return;

		$file=$tmp.'/speed.log';
		$data[]=date('Y-m-d H:i:s');
		$data[]=$speed=timer(0);
		if($speed>=1)
			$data[]=$_SERVER['REQUEST_URI'];
		file_put_contents($file, implode("\t", $data)."\n", FILE_APPEND);
		return (object)array();
	}
}
