<?php
/*
	- выполняется в shell
	- получает данные и парсит выдачу по кейвордам (получает замену)
	- скачанные с google данные для текущего дня здесь не удаляются (где-то в cron)
		- то есть существует возможность повторного запуска и перепарсинга/докачки данных
	вход: [tmp_dir]/gglreplparse.input
*/
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../../index.php';#подключаем движок
error_reporting(-1); ini_set('display_errors', 'On');
set_time_limit(0);
ini_set('memory_limit', '512M');
$tmp=TMP.'admin_traffic';
@mkdir($tmp);
define('START_DATE',date('Y-m-d'));
define('INPUT',"{$tmp}/gglreplparse.input");
define('OUTPUT',"{$tmp}/gglreplparse.store");
define('TMP_DIR', "{$tmp}/gglreplparse_".START_DATE.".tmp");
define('TRAFFIC_STAT_DIR', "{$tmp}/stat");
define('IP_COUNT_DIR', "{$tmp}/ip.tmp");
define('STREAMS_MAX', 5);#максимальное количество запросов к google за раз
define('STREAMS_INTERVAL', 7);#ожидание между партиями потоков

$ps=shell_exec($t="ps aux|grep ".escapeshellarg(__FILE__)."|grep -v ' grep '");
$running=explode("\n", trim($ps));
if(count($running)>1) die("already running\n");

if(start()){
	store();
	culcSum();
	culcSumByCats();
	traffic();
}

function start(){
	#должен быть уровень вложенности
	if(!file_exists(INPUT)) {return false;}
	if(!filesize(INPUT)) {unlink(INPUT); return false;}
	@mkdir(TMP_DIR);
	$wget="nohup /usr/bin/wget -t3 --no-check-certificate --connect-timeout=30 --random-wait -U'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36' -q -O %s %s > /dev/null 2>&1 & echo $!";
	$google='http://www.google.com/search?q=%s&hl=en&tbm=isch';
	#забирает данные из файла и удаляет входной файл
	rename(INPUT, $input=INPUT.".2");

	$Pids=array();
	$fh=fopen($input,'r');
	$c=0;
	print "creting streams:\n";
	while ($str=fgets($fh)) {
		$data=unserialize($str);
		$c++;
		echo "$c ";
		if(empty($data->title)) continue;
		if(file_exists($tfile=TMP_DIR.'/'.$data->id)) {
			parse($data);
		}else{
			$tfile=escapeshellarg($tfile);
			echo " [{$data->title}]\n";
			$keyword=escapeshellarg(sprintf($google,urlencode($data->title)));
			$pid=shell_exec(sprintf($wget,$tfile,$keyword));
			#this pid is process ID, is not post ID
			if($pid=trim($pid)){
				$Pids[$data->id]=(object)array('pid'=>$pid,'data'=>$data);
			}
		}
		pidchk($Pids);
	}
	pidchk($Pids,1);
	fclose($fh);
	#удаляем использованный входной файл
	unlink($input);

	#запускаем докачку, если что-то не скачалось
	if(!revision())
		start();
	echo "done\n";
	return true;
}

function parse($data){
	static $num;
	@$num++;
	if(!$html=@file_get_contents($fname=TMP_DIR.'/'.$data->id)) {
		echo "[{$data->title}] FAIL - not downloaded\n";
		@unlink($fname);
		file_put_contents(INPUT.'.rev', serialize($data)."\n",FILE_APPEND);
		return;
	}else
		echo "[{$data->title}] parsing";
	preg_match_all('!/imgres\?imgurl=([^\&]+)[^\s]*imgrefurl=([^\&]+).*?<div class="rg_meta">(.+?)</div>!is',$html,$m);# дает ссылки на ~100 последних картинок
	if(isset($m[0])){
		$data->imgserp=array();
		foreach($m[1] as $k=>$imglink){
			$imgserp=new stdClass;
			$t_data=json_decode(str_replace("\n", ' ', $m[3][$k])); # js данные для картинки от гугла
			$imgserp->tu=isset($t_data->tu)?preg_replace('!^.+?tbn\:(.+?)$!si','$1',$t_data->tu):'';
			$imgserp->ref=$m[2][$k];
			$data->imgserp[]=$imgserp;
		}
		if(!empty($data->imgserp)){
			file_put_contents(OUTPUT, serialize($data)."\n",FILE_APPEND);
			echo " | OK\n";
		}else
			echo " | parsing fail\n";	
	}else
		echo " | parsing fail\n";
}

function store(){
	if(!$fh=fopen(OUTPUT, 'r')){
		echo "fail - can not open ".OUTPUT."\n"; return;
	}
	$fhre=fopen(INPUT, 'a');
	flock($fhre, LOCK_EX);

	if(!db::ping())
		new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	$sql="INSERT IGNORE INTO `".PREFIX_SPEC."grepl` (`date`,`keyword`,`pid`,`url`,`top5`,`top100`,`repl`,`tbnMatchTop5`,`tbnMatchTop100`) VALUES";
	$sqlVals=array();
	$c=0;
	while ($str=fgets($fh)) {
		$c++;
		$data=unserialize($str);
		#получаем замену
		$repl=replCheck($data);
		#топ 5
		$topF=array_filter($repl,function($a){
			if(isset($a->pid)||isset($a->url))
				return ($a->pos<=5);
			else return false;
		});
		#топ 100
		$topH=array_filter($repl,function($a){
			return (isset($a->pid)||isset($a->url));
		});
		#сопоставляем google thumbnail ID
		$tbnMatch=gtbnCheck($data);
		#thumbnail есть в топ 5
		$tbnTopF=count(
			array_filter($tbnMatch,function($a){
				return ($a->pos<=5);
			})
		);
		#thumbnail есть в топ 100
		$tbnTopH=count($tbnMatch);
		#строим значения для БД
		$vals=array(
			START_DATE,
			db::escape($data->title),
			$data->pid,
			$data->url,
			!empty($topF)?db::escape(serialize($topF)):'',
			!empty($topH)?db::escape(serialize($topH)):'',
			db::escape(serialize($data->imgserp)),
			$tbnTopF,
			$tbnTopH
		);
		$sqlVals[]="('".implode("','",$vals)."')";
		if($sqlVals>=50){
			db::query($sql.implode(',', $sqlVals),1);
			$sqlVals=array();
		}
		echo "{$c} [{$data->title}] | stored\n";
	}
	#дозаписываем остатки
	if(count($sqlVals))
		db::query($sql.implode(',', $sqlVals),1);
	flock($fhre, LOCK_UN);
	fclose($fhre);
	unlink(OUTPUT);
	echo "save done\n";
}

/*
	записывает данные по трафику поста, за предыдущий день относительно текущего запуска парсинга
*/
function traffic(){
	echo 'count traffic';
	global $router;
	$date=date('Y-m-d',strtotime(START_DATE)-86400);
	if(!db::ping())
		new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	list($exists)=db::qrow("SELECT COUNT(*) FROM `".PREFIX_SPEC."traffic` WHERE `date`='".START_DATE."'");
	if(!empty($exists)) return;
	@mkdir(IP_COUNT_DIR);
	$file=TRAFFIC_STAT_DIR."/{$date}.txt";
	$fh=fopen($file, 'r');
	$postPids=$postUrls=array();
	while ($str=fgets($fh)) {
		list(,$ip,$url,,$ua)=explode("\t", $str);
		if(file_exists($ipfile=IP_COUNT_DIR."/{$ip}")||botdetect($ua)) continue;
		touch($ipfile);
		$router=route::get($url);
		if($router->module=='posts'||$router->module=='gallery'){
			if(isset($router->params['pid'])){
				$postPids[]=$router->params['pid'];
			}elseif(isset($router->params['url'])){
				$postUrls[]=$router->params['url'];
			}
		}
		if(count($postUrls)>=50){
			trafficSave('urls',$postUrls);
			$postUrls=array();
		}
		if(count($postPids)>=50){
			trafficSave('pids',$postPids);
			$postPids=array();
		}
	}
	#дозапись
	if(count($postUrls)){
		trafficSave('urls',$postUrls);
	}
	if(count($postPids)){
		trafficSave('pids',$postPids);
	}
	#суммы трафика по категориям
	trafficSaveCats();
	fclose($fh);
	if(strstr(IP_COUNT_DIR, PATH)){
		shell_exec("rm -rf ".IP_COUNT_DIR);
	}
	echo " - done\n";
}

/*
	расчитывает и запичывает трафик по категориям
*/
function trafficSaveCats(){
	db::query("SELECT * FROM `".PREFIX_SPEC."traffic` WHERE `date`='".START_DATE."'");
	$sum=array();
	while ($d=db::fetch()) {
		$cids=explode(',', $d->cid);
		foreach ($cids as $c) {
			if(!isset($sum[$c]))
				$sum[$c]=0;
			$sum[$c]+=$d->uniq;
		}
	}
	$sqlInsert="INSERT INTO `".PREFIX_SPEC."trafficCats` (`date`,`cid`,`uniq`) 
		VALUES %s";
	$sqlVals=array();
	foreach ($sum as $cid => $traf) {
		$sqlVals[]="('".START_DATE."','{$cid}','{$traf}')";
		if(count($sqlVals)>=50){
			db::query(sprintf($sqlInsert,implode(',',$sqlVals)),1);
			$sqlVals=array();
		}
	}
	if(count($sqlVals))
		db::query(sprintf($sqlInsert,implode(',',$sqlVals)),1);
}
/*
	расчитывает трафик по постам
*/
function trafficSave($type,$arr){
	if(!db::ping())
		new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	$sql="INSERT INTO `".PREFIX_SPEC."traffic` (`date`,`cid`,`pid`,`type`,`url`,`uniq`) 
		VALUES %s ON DUPLICATE KEY UPDATE `uniq`=`uniq`+1";
	if($type=='urls'){
		#обрабатываем данные где pid не получен из URL
		$arr=array_map(function($a){
			return db::escape($a);
		}, $arr);
		db::query(
			"SELECT post.url,post.id,GROUP_CONCAT(cp.cid) AS cids FROM `post` 
				LEFT OUTER JOIN `".PREFIX_SPEC."category2post` cp 
				ON post.url=cp.pid
			WHERE post.url IN('".implode("','", $arr)."') GROUP BY post.id",1);
		while ($d=db::fetch()) {
			$sqlVals[]="('".START_DATE."','{$d->cids}','{$d->id}','post','{$d->url}','1')";
		}
	}elseif($type=='pids'){
		db::query(
			"SELECT post.url,post.id,GROUP_CONCAT(cp.cid) AS cids FROM `post` 
				LEFT OUTER JOIN `".PREFIX_SPEC."category2post` cp 
				ON post.url=cp.pid
			WHERE post.id IN(".implode(",", $arr).")",1);
		while ($d=db::fetch()) {
			$sqlVals[]="('".START_DATE."','{$d->cids}','{$d->id}','gallery','{$d->url}','1')";
		}
	}
	if(!empty($sqlVals))
		db::query(sprintf($sql,implode(',', $sqlVals)),1);
}
/*
	- определяет работающие процессы
	- убивает зависшие процессы
	- запускает парсинг для завершенных процессов
*/
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
		echo date("H:i:s")." [{$c}] streams running - waiting\n";
		sleep(STREAMS_INTERVAL); pidchk($Pids);
	}else return 1;
}
/*
	получает совпадения URL`ов поста(или галереи) с URL в images SERP
*/
function replCheck($d){
	global $router;
	$matched=array();
	foreach ($d->imgserp as $pos => $v) {
		$subject=parse_url($v->ref,PHP_URL_HOST);
		$search=parse_url(HREF,PHP_URL_HOST);
		if($search==$subject){
			$query=str_replace($subject, '', preg_replace('!^(?:https?\:)?//!si', '', $v->ref));
			#замена есть, прогоняем через роутер [разбираем URL на параметры]
			$router=route::get($query);
			if($router->module=='posts'||$router->module=='gallery'){
				#сопоставляем с постом
				$matched[$pos]=new stdClass;
				$matched[$pos]->query=$query;
				$matched[$pos]->pos=$pos;
				if(isset($router->params['pid'])){
					#если принадлежит текущему посту
					if($d->pid==$router->params['pid'])
						$matched[$pos]->pid=$router->params['pid'];
				}elseif(isset($router->params['url'])){
					#если принадлежит текущему посту
					if($d->url==$router->params['url'])
						$matched[$pos]->url=$router->params['url'];
				}
			}
		}
	}
	reset($matched);
	return $matched;
}

function gtbnCheck($d){
	$matched=array();
	$tbnsSearch=array_flip($d->tbns);
	foreach ($d->imgserp as $pos => $v) {
		if(isset($tbnsSearch[$v->tu])){
			$matched[$pos]=new stdClass;
			$matched[$pos]->pos=$pos;
			$matched[$pos]->tbn=$v->tu;
		}
	}
	return $matched;
}
/*
	проверяет наличие нескачанных данных
*/
function revision(){
	echo "revision";
	static $try;
	$lim=3;
	if(!isset($try)) $try=0;
	if(!file_exists($revinput=INPUT.'.rev')||$try>$lim) {
		echo " - done";
		return true;
	}else{
		$try++;
		echo " - false, reload [try {$try}/{$lim}]";
		rename($revinput, INPUT);
		return false;
	}
}
/*
	считает суммы за текущую дату
*/
function culcSum(){
	echo "culc summary";
	if(!db::ping())
		new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	db::query("SELECT * FROM `".PREFIX_SPEC."grepl` WHERE `date`='".START_DATE."'");
	while ($d=db::fetch()) {
		#суммы по кейвордам и по постам
		if(!isset($sumByPids)){
			$sumByPids=new \stdClass;
			$sumByPids->top5=array();
			$sumByPids->top100=array();
		}
		if(!isset($sumByKw)){
			$sumByKw=new \stdClass;
			$sumByKw->top5=array();
			$sumByKw->top100=array();
		}
		if($d->top5!=''){
			$sumByPids->top5[$d->pid]=1;
			$sumByKw->top5[$d->keyword]=1;
		}
		if($d->top100!=''){
			$sumByPids->top100[$d->pid]=1;
			$sumByKw->top100[$d->keyword]=1;
		}
		#суммы постов и кейвордов без совпадений thumbnail картинки
		if(!isset($sumTbnMatchByPids)){
			$sumTbnMatchByPids=new \stdClass;
			$sumTbnMatchByPids->top5=array();
			$sumTbnMatchByPids->top100=array();
		}
		if(!isset($sumTbnMatchByKW)){
			$sumTbnMatchByKW=new \stdClass;
			$sumTbnMatchByKW->top5=array();
			$sumTbnMatchByKW->top100=array();
		}
		if($d->tbnMatchTop5==0)
			$sumTbnMatchByPids->top5[$d->pid]=1;
		if($d->tbnMatchTop100==0)
			$sumTbnMatchByPids->top100[$d->pid]=1;
		
		if($d->tbnMatchTop5==0)
			$sumTbnMatchByKW->top5[$d->keyword]=1;
		if($d->tbnMatchTop100==0)
			$sumTbnMatchByKW->top100[$d->keyword]=1;
	}
	db::query(
		"INSERT IGNORE INTO `".PREFIX_SPEC."greplSum` 
		(`date`,`p_top5`,`p_top100`,`p_tbnMatchTop5`,`p_tbnMatchTop100`,`k_top5`,`k_top100`,`k_tbnMatchTop5`,`k_tbnMatchTop100`) 
		VALUES(
			'".START_DATE."',
			'".count($sumByPids->top5)."',
			'".count($sumByPids->top100)."',
			'".count($sumTbnMatchByPids->top5)."',
			'".count($sumTbnMatchByPids->top100)."',
			'".count($sumByKw->top5)."',
			'".count($sumByKw->top100)."',
			'".count($sumTbnMatchByKW->top5)."',
			'".count($sumTbnMatchByKW->top100)."'
		)"
	,1);
	echo " - done\n";
}
/*
	считает суммы за текущую дату по категориям
*/
function culcSumByCats(){
	if(!db::ping())
		new db(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	echo "culc categories summary";
	db::query(
		"SELECT gr.*,cp.cid AS cid,c.parentId AS parent FROM `".PREFIX_SPEC."grepl` gr
			INNER JOIN `".PREFIX_SPEC."category2post` cp 
				ON cp.pid=gr.url
			INNER JOIN `category` c
				ON c.url=cp.cid
		WHERE `date`='".START_DATE."'");
	$sum=$sumByPids=$sumByKw=array();
	$sumTbnMatchByPids=$sumTbnMatchByKW=array();
	while ($d=db::fetch()) {
		$cids[$d->cid]=1;
		#количество постов и кейвордов
		setSumByCatsData($d,$sum,'pcount','pid');
		setSumByCatsData($d,$sum,'kcount','keyword');
		#суммы замены по кейвордам и по постам
		if($d->top5!=''){
			setSumByCatsData($d,$sumByPids,'top5','pid');
			setSumByCatsData($d,$sumByKw,'top5','keyword');
		}
		if($d->top100!=''){
			setSumByCatsData($d,$sumByPids,'top100','pid');
			setSumByCatsData($d,$sumByKw,'top100','keyword');
		}
		#суммы постов и кейвордов без совпадений thumbnail картинки
		if($d->tbnMatchTop5==0){
			setSumByCatsData($d,$sumTbnMatchByPids,'top5','pid');
			setSumByCatsData($d,$sumTbnMatchByKW,'top5','keyword');
		}
		if($d->tbnMatchTop100==0){
			setSumByCatsData($d,$sumTbnMatchByPids,'top100','pid');
			setSumByCatsData($d,$sumTbnMatchByKW,'top100','keyword');
		}
	}
	$sql="INSERT IGNORE INTO `".PREFIX_SPEC."greplSumCats` 
		(`cid`,`date`,`pcount`,`kcount`,`p_top5`,`p_top100`,`p_tbnMatchTop5`,`p_tbnMatchTop100`,`k_top5`,`k_top100`,`k_tbnMatchTop5`,`k_tbnMatchTop100`) 
		VALUES";
	$sqlVals=array();
	foreach ($cids as $cid => $v) {
		$sqlVals[]="(
			'{$cid}',
			'".START_DATE."',
			'".count(@$sum[$cid]->pcount)."',
			'".count(@$sum[$cid]->kcount)."',
			'".count(@$sumByPids[$cid]->top5)."',
			'".count(@$sumByPids[$cid]->top100)."',
			'".count(@$sumTbnMatchByPids[$cid]->top5)."',
			'".count(@$sumTbnMatchByPids[$cid]->top100)."',
			'".count(@$sumByKw[$cid]->top5)."',
			'".count(@$sumByKw[$cid]->top100)."',
			'".count(@$sumTbnMatchByKW[$cid]->top5)."',
			'".count(@$sumTbnMatchByKW[$cid]->top100)."'
		)";
		if(count($sqlVals)>=50){
			db::query($sql.implode(',', $sqlVals),1);
			$sqlVals=array();
		}
	}
	if(count($sqlVals))
		db::query($sql.implode(',', $sqlVals),1);
	echo " - done [memory peak ".round(memory_get_peak_usage()/1024/1024,2)."M]\n";
}

function botdetect($userAgent){
	$ar=array('Googlebot','bingbot','Slurp','facebook','Yahoo','YandexBot','Baiduspider','YesupBot','proximic');
	foreach ($ar as $bot) {
		if(stristr($userAgent,$bot))
			return true;
	}
	return false;
}

function setSumByCatsData($data,&$arr,$key,$val){
	if(!isset($arr[$data->cid]))
		$arr[$data->cid]=new patternObj;
	$arr[$data->cid]->{$key}[$data->{$val}]=1;
	#прибавляем данные к родительской категории
	if($data->parent!=''){
		if(!isset($arr[$data->parent]))
			$arr[$data->parent]=new patternObj;
		$arr[$data->parent]->{$key}[$data->{$val}]=1;
	}
}

class patternObj{
	public $top5=array();
	public $top100=array();
}
?>
