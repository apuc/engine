<?php
/*
	php daemon
	управляет закачкой результатов выдачи гугл по указанным кейвордам
	вход: /tmp/gglkwparse.daemon.input
*/
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../../index.php';#подключаем движок
error_reporting(-1); ini_set('display_errors', 'On');
set_time_limit(0);
ini_set('memory_limit', '512M');
$tmp=__DIR__.'/tmp';
@mkdir($tmp);
define('INPUT',"{$tmp}/gglkwparse.input.txt");
define('OUTPUT',"{$tmp}/gglkwparse.store.txt");
define('TMP_DIR', "{$tmp}/gglkwparse.tmp");
define('IMGPARSER_TMP_DIR', PATH."modules/images/admin/download/daemon/tmp/gglparse.tmp");
define('STREAMS_MAX', 5);#максимальное количество запросов к google за раз
define('STREAMS_INTERVAL', 7);#ожидание между партиями потоков

$ps=shell_exec($t="ps aux|grep ".escapeshellarg(__FILE__)."|grep -v ' grep '");
$running=explode("\n", trim($ps));
if(count($running)>1) die("already running\n");

while(1){
	if(!start()) {sleep(3); print date("H:i:s")." | all done restarting\n"; continue;}
	else store();
}

function start(){
	#должен быть уровень вложенности
	if(!file_exists(INPUT)) {return false;}
	if(!filesize(INPUT)) {unlink(INPUT); return false;}
	@mkdir(TMP_DIR);
	@mkdir(IMGPARSER_TMP_DIR);
	$wget="nohup /usr/bin/wget -t2 --no-check-certificate --connect-timeout=30 --random-wait -U'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36' -q -O %s %s >/dev/null 2> /dev/null & echo $!";
	$google='http://www.google.com/search?q=%s&hl=en&tbm=isch';
	#забирает данные из файла и удаляет входной файл
	copy(INPUT, $input=INPUT.".2");
	if(unlink(INPUT)===false) die('delete input error');
	$Pids=array();
	$fh=fopen($input,'r');
	$c=0;
	print "creting streams:\n";
	while ($str=fgets($fh)) {
		$data=unserialize($str);
		if(empty($data->title)) continue;
		if(file_exists($tfile=TMP_DIR.'/'.$data->kid)) {
			parse($data);
		}else{
			$tfile=escapeshellarg($tfile);
			if(!empty($data->addword))
				if(!strstr($data->title,$data->addword))
					$data->title.=" {$data->addword}";
			echo $data->title."\n";
			$keyword=escapeshellarg(sprintf($google,urlencode($data->title)));
			$pid=shell_exec(sprintf($wget,$tfile,$keyword));
			if($pid=trim($pid)){
				$Pids[$data->kid]=(object)array('pid'=>$pid,'data'=>$data);
			}
		}
		$c++;
		echo "$c ";
		pidchk($Pids);
	}
	pidchk($Pids,1);
	fclose($fh);
	#удаляем использованный входной файл
	unlink($input);
	#удаляем временные файлы - результаты парсинга google
	shell_exec('rm -rf '.TMP_DIR);
	echo "done\n";
	return true;
}

function parse($data){
	static $num;
	@$num++;
	echo "$num) [{$data->title}] parsing";
	if(!$html=@file_get_contents($fname=TMP_DIR.'/'.$data->kid)) {
		echo "fail - not downloaded kid:{$data->kid}\n"; return;
	}else
		copy($fname, IMGPARSER_TMP_DIR.'/'.$data->kid);#сохраняем html от google для дальнейших этапов
	
	preg_match_all('!<a[^<]+class="rg_fbl"[^<]+data-title="([^\"]+)"!si',$html,$m);# дает подсказки кейвордов ~6штук
	if(isset($m[0])){
		$data->keywords=array();
		foreach($m[1] as $k=>$kw){
			$kw=trim($kw);
			if(empty($kw)) continue;
			$data->keywords[]=$kw;
		}
		echo " found:".count($data->keywords);
		if(!empty($data->keywords))
			file_put_contents(OUTPUT, serialize($data)."\n",FILE_APPEND|LOCK_EX);
	}
	print "\n";
}

function store(){
	if(!$fh=fopen(OUTPUT, 'r')){
		echo "fail - can not open ".OUTPUT."\n"; return;
	}
	include_once PATH.'modules/posts/admin/handler.php';
	$fhre=fopen(INPUT, 'a');
	flock($fhre, LOCK_EX);

	while ($str=fgets($fh)) {
		$data=unserialize($str);
		foreach ($data->keywords as $k) {
			db::query("INSERT IGNORE INTO `keyword` SET `title`='".db::escape($k)."'");
			$kid=db::insert();
			if($kid){
				#собираем данные для следующего уровня вложенности
				if($data->nested>0){
					$newdata=clone $data;
					$newdata->title=$k;
					$newdata->kid=$kid;
					$newdata->nested--;
					unset($newdata->keywords);
					fwrite($fhre, serialize($newdata)."\n");
				}

				if(!empty($data->pid))
					db::query("INSERT INTO `".PREFIX_SPEC."keyword2post` SET `kid`='{$kid}',`pid`='{$data->pid}',`tbl`='post'");
				echo "[$k] inserted\n";
			}else
				echo "[$k] insert ignored\n";
		}
	}
	flock($fhre, LOCK_UN);
	fclose($fhre);
	unlink(OUTPUT);
	if(isset($data))
		if($data->nested>0&&isset($newdata))
			echo "prepare to next level - total:{$data->nested} left\n";
		else 
			echo "all done\n";
}

function pidchk(&$Pids,$last=0){
	static $runTimer;
	if(empty($runTimer)) $runTimer=array();
	$p=array();
	foreach ($Pids as $v) {
		$p[]=$v->pid;
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
