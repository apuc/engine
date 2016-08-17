<?php
/*
	php daemon
	управляет закачкой картинок
	вход: /tmp/imgdownload.daemon.input
	выход: записанные в БД данные о картинках
*/
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../../../index.php';#подключаем движок
error_reporting(6135); ini_set('display_errors', 'On');
set_time_limit(0);
ini_set('memory_limit', '1024M');
$tmp=__DIR__.'/tmp';
@mkdir($tmp);
define('INPUT',"{$tmp}/imgdownload.input.txt");
define('LOG',"{$tmp}/imgdownload.summary.log");
define('TMP_DIRIMGS', "{$tmp}/gglparseImgs.tmp");
define('STREAM_SCRIPT',__DIR__.'/imgdownload.stream.php');# stream script name
define('STREAM_LIMIT','100');# limit stream

@chmod(STREAM_SCRIPT, 0755);
if(!is_executable(STREAM_SCRIPT)) die(STREAM_SCRIPT." execute permission denied\n");

$ps=shell_exec("ps aux|grep ".escapeshellarg(__FILE__)."|grep -v ' grep '");
$running=explode("\n", trim($ps));
if(count($running)>1) die("already running\n");

while(1){
	if(!start()) {sleep(3); print date("H:i:s")." | all done restarting\n"; continue;}
}

function start(){
	if(!file_exists(INPUT)) return false;
	#подключаем модуль images/admin
	include_once PATH.'modules/images/admin/handler.php';
	$imgDir=PATH.'modules/images/files/images/';
	#Переносим входные данные: tmp/imgdownload.input.txt -> tmp/imgdownload.input.txt.2
	copyInput($input=INPUT.".2");
	$Pids=array();
	$fh=fopen($input,'r');
	print "Creating strems:\n";
	while ($str=fgets($fh)){
		$d=unserialize($str);
		$images=unserialize(file_get_contents($imgsParseResults=TMP_DIRIMGS.'/'.$d->kid."_imgs"));
		if(empty($images)) continue;
		echo (++$knum).") pid:$d->pid key:[{$d->title}] count ".count($images)."\n";
		$i=0;
		foreach ($images as $k=>$v) {
			$v->url=\images_admin\getImageName($d->title,$imgDir);
			if(!touch($v->dest=$imgDir.$v->url)) continue;
			# формируем данные уходящие в поток закачки картинок
			$obj=new stdClass;
			$obj->kid=$d->kid;
			$obj->pid=$d->pid;
			$obj->uid=$d->uid;
			$obj->manimsize=$d->manimsize;
			$obj->skipExists=$d->skipExists;
			$obj->allowgallery=$d->allowgallery;
			$obj->image=$v;
			$Pids[]=runstream($obj);
			pidchk($Pids);
			unset($images[$k]);
			if(++$i>=$d->need) break;
		}
		#перезаписываем результат парсинга картинок, сохраняем оставшиеся (невостребованные)
		if(!empty($images))
			file_put_contents($imgsParseResults, serialize($images)."\n");
	}
	pidchk($Pids,1);
	print "\nAll streams executed\n";
	#ревизия записанных в БД картинок и докачка из того что осталось
	echo "revision start\n";
	$kids=$pids=array();
	rewind($fh);
	while ($str=fgets($fh)) {
		$d=unserialize($str);
		$images=unserialize(file_get_contents(TMP_DIRIMGS.'/'.$d->kid."_imgs"));
		if(empty($images)) continue;
		$kids[(int)$d->kid]=$d;
		$pids[(int)$d->pid]=1;
		#делим по 300 
		if(count($kids)>=300){
			revision($kids,$pids);
			$kids=$pids=array();		
		}
	}
	if(count($pids)){
		revision($kids,$pids);
	}
	echo "revision done\n";

	if(!filesize($reinput))
		echo "All done\n";

	fclose($fh);
	unlink($input);
}

function revision($kids,$pids){
	$fhin=fopen(INPUT, 'a');
	flock($fhin, LOCK_EX);
	db::query("SELECT k.id AS kid,k.title,k2p.pid,COUNT(imgs.kid) AS total FROM 
	(SELECT * FROM `keyword` WHERE id IN (".implode(',',array_keys($kids)).")) k 
	JOIN `".PREFIX_SPEC."keyword2post` k2p ON k.id=k2p.kid && k2p.tbl='post'
	LEFT OUTER JOIN ".PREFIX_SPEC."imgs imgs ON imgs.kid=k2p.kid AND imgs.pid=k2p.pid && img.tbl='post'
	WHERE k2p.pid IN (".implode(',',array_keys($pids)).") 
	GROUP BY k.id,k2p.pid");
	while ($d=db::fetch()) {
		if(!isset($kids[$d->kid])) continue;
		$k=$kids[$d->kid];
		#определяем сколько еще нужно
		$k->need=$k->must-$d->total;#[сейчас нужно]=[должно быть]-[есть сейчас]
		if($k->need<=0) continue;
		fwrite($fhin, serialize($k)."\n");
		echo "[{$k->title}] else need {$k->need}\n";
	}
	flock($fhin, LOCK_UN);
	fclose($fhin);
}

function runstream($obj){
	if(!is_object($obj)) return;
	$shellStr=base64_encode(serialize($obj));
	$runStr="nohup ".STREAM_SCRIPT." '{$shellStr}' >> ".__DIR__."/tmp/imgdownload.stream.log 2>&1 & echo $!";
	$pid=shell_exec($runStr);
	if($pid=trim($pid))
		return $pid;
	else
		return false;
}

function pidchk(&$Pids,$last=0){#проверка состояния процессов
	static $runTimer;
	if(empty($runTimer)) $runTimer=array();
	if(count($Pids)==0) return 0;
	$ps=shell_exec("ps ".implode(' ',$Pids));
	preg_match_all('!^\s*(\d+)\s!m',$ps,$m);
	foreach($Pids as $k=>$v){
		if(!isset($runTimer[$v]))
			$runTimer[$v]=time();
		if(!in_array($v,$m[1])){
			unset($Pids[$k]);
			unset($runTimer[$v]);
		}else{
			if(((time()-$runTimer[$v])/60)>=2)
				shell_exec("kill {$v}");
		}
	}
	$cpids=count($Pids);
	if(($cpids>=STREAM_LIMIT)||($last&&$cpids>0)){
		echo "\n".date("i:s")." ".$cpids." streams running - waiting";
		sleep(1); pidchk($Pids,$last);
	}else return 1;
}

function copyInput($input){
	if(file_exists($input))unlink($input);
	$fhin=fopen(INPUT, 'r');
	flock($fhin, LOCK_EX);
	if(!rename(INPUT, $input))die('rename input error');
	flock($fhin, LOCK_UN);
	fclose($fhin);
}
