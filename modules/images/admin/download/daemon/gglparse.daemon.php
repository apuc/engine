<?php
/*
	php daemon
	управляет закачкой результатов выдачи гугл по указанным кейвордам
	вход: /tmp/gglparse.daemon.input
	выход: файл с результатами парсинга
*/
error_reporting(-1); ini_set('display_errors', 'On');
set_time_limit(0);
$tmp=__DIR__.'/tmp';
@mkdir($tmp);
define('INPUT',"{$tmp}/gglparse.input.txt");
define('OUTPUT',"{$tmp}/imgdownload.input.txt");
define('TMP_DIR', "{$tmp}/gglparse.tmp");
define('TMP_DIRIMGS', "{$tmp}/gglparseImgs.tmp");
define('STREAMS_MAX', 5);#максимальное количество запросов к google за раз
define('STREAMS_INTERVAL', 7);#ожидание между партиями потоков

include_once __DIR__.'/func.php';

$ps=shell_exec($t="ps aux|grep ".escapeshellarg(__FILE__)."|grep -v ' grep '");
$running=explode("\n", trim($ps));
if(count($running)>1) die("already running\n");

while(1){
	if(!start()) {sleep(3); print date("H:i:s")." | all done restarting\n"; continue;}
}

function start(){
	if(!file_exists(INPUT)) {return false;}
	@mkdir(TMP_DIR);
	@mkdir(TMP_DIRIMGS);
	$wget="nohup /usr/bin/wget -t2 --no-check-certificate --connect-timeout=30 --random-wait -U'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36' -q -O %s %s >/dev/null 2> /dev/null & echo $!";
	$google='http://www.google.com/search?q=%s&hl=en&tbm=isch';
	#забирает данные из файла и удаляет входной файл
	copy(INPUT, $input=INPUT.".2");
	if(unlink(INPUT)===false) die('delete input error');
	echo "start google download:\n";
	$Pids=array();
	$fh=fopen($input,'r');
	while ($str=fgets($fh)) {
		$data=unserialize($str);
		if(empty($data->title)) continue;
		$Pids[$data->kid]=(object)array('pid'=>false,'data'=>$data);
		if(!file_exists($tfile=TMP_DIR.'/'.$data->kid)){
			touch($tfile);
			$tfile=escapeshellarg($tfile);
			$keyword=escapeshellarg(sprintf($google.(!empty($data->gglparam)?"&{$data->gglparam}":''),urlencode($data->title)));
			$pid=shell_exec(sprintf($wget,$tfile,$keyword));
			echo "<b>download:</b> $data->title\n";
			if($pid=trim($pid)){
				$Pids[$data->kid]->pid=$pid;
			}
		}
		pidchk($Pids);
	}
	pidchk($Pids,1);
	fclose($fh);
	#удаляем использованный входной файл
	unlink($input);
	#удаляем временные файлы - результаты парсинга google
	#shell_exec('rm -rf '.TMP_DIR);
	echo "done\n";
	return true;
}

function parse($data){
	static $num;
	@$num++;
	echo "$num) parsing pid:$data->pid\n";
	if(!$html=@file_get_contents(TMP_DIR.'/'.$data->kid)) {
		echo "<b style='color:red'>download fail</b> \n"; 
		@unlink(TMP_DIR.'/'.$data->kid);
		return;
	}
	if(!file_exists($imFile=TMP_DIRIMGS.'/'.$data->kid."_imgs")){
		# парсим json объекты с данными картинки для каждой картинки
		preg_match_all('!<div class="rg_meta">(.+?)</div>!is',$html,$m);# дает ссылки на ~100 последних картинок

		$images=array();
		if(isset($m[0])){
			foreach($m[1] as $k=>$jsonEnc){
				$obj = json_decode($jsonEnc);
				if(!is_object($obj)) continue;
				
				$obj->imglink=$obj->ou;
				$obj->ref=$obj->ru;
				if(!sizeFilter($data->manimsize,$obj)) continue;
				$images[]=$obj;
			}
			if(!empty($images)){
				file_put_contents($imFile, serialize($images)."\n",LOCK_EX);
			}
			print "<b>found images:".count($images)."</b> key:$data->title \n";
		}
	}
	file_put_contents(OUTPUT, serialize($data)."\n",FILE_APPEND|LOCK_EX);
}

function pidchk(&$Pids,$last=0){
	static $runTimer;
	if(empty($runTimer)) $runTimer=array();
	$p=array();
	foreach ($Pids as $k=>$v) {
		if($v->pid!==false)
			$p[]=$v->pid;
		else{
			parse($v->data);
			unset($Pids[$k]);
		}
	}
	$ps=shell_exec("ps ".implode(' ',$p));
	preg_match_all('!^\s*(\d+)\s!m',$ps,$m);
	foreach($Pids as $k=>$v){
		if(!isset($runTimer[$v->pid]))
			$runTimer[$v->pid]=time();
		if(!in_array($v->pid,$m[1])){
			parse($v->data);
			unset($Pids[$k]);
			unset($runTimer[$v->pid]);
		}else{
			if(((time()-$runTimer[$v->pid])/60)>=2)
				shell_exec("kill {$v->pid}");
		}
	}
	$c=count($Pids);
	if(($c>=STREAMS_MAX)||($last&&$c>0)){
		echo date("i:s")." ".$c." streams running - waiting\n";
		sleep(STREAMS_INTERVAL); pidchk($Pids);
	}else return 1;
}
?>
