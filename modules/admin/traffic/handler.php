<?php
namespace admin_traffic;
use module,db,url,cache;

require_once __DIR__.'/func.php';
require_once PATH.'modules/category/handler.php';

class handler{
	function __construct(){
		set_time_limit(60);
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		@mkdir($this->tmp=TMP.__NAMESPACE__.'/');
		$this->tmpstat=$this->tmp.'stat/';
		$this->tmpcurrent=$this->tmpstat.date('Y-m-d').'.txt';
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
	}
	/*
		точка входа пользовательского интерфейса
	*/
	function index($start,$stop,$page,$num,$sort){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		$offset=($page-1)*$num;
		#определение сортировки
		$sort=explode('|', $sort);
		if(empty($sort[0])){
			$sort[0]='date';
			$sort[1]='desc';
		}
		db::query(
			"SELECT SQL_CALC_FOUND_ROWS gsum.*,SUM(tc.uniq) as traf
				FROM `".PREFIX_SPEC."greplSum` gsum
				LEFT JOIN `".PREFIX_SPEC."traffic` tc ON tc.date=gsum.date
			WHERE gsum.date>='".db::escape($start)."' && gsum.date<='".db::escape($stop)."'
			GROUP BY gsum.date ".mkSQLorder($sort)." LIMIT $offset,$num");
		$sum=array();
		while ($d=db::fetch()) {
			$sum[$d->date]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');
			
		#table status, size Gb
		$ts=db::qfetch("SHOW TABLE STATUS FROM `".DB_NAME."` WHERE `Name`='".PREFIX_SPEC."grepl'");

		#всего постов и кейвордов
		db::query("SELECT COUNT(DISTINCT pid) AS count,`date` FROM `".PREFIX_SPEC."grepl` gr 
			WHERE `date`>='".db::escape($start)."' && `date`<='".db::escape($stop)."' GROUP BY `date`");
		while ($d=db::fetch()) {
			$countPosts[$d->date]=$d->count;
		}
		list($countKw)=db::qrow("SELECT COUNT(*) FROM (SELECT DISTINCT kid FROM `".PREFIX_SPEC."keyword2post`) t");

		return (object)array(
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'admin/traffic',
						'act'=>'index',
						'sort'=>"{$sort[0]}|{$sort[1]}",
					)
				)
			,1)->str,
			'sum'=>$sum,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::admin_traffic("{$sort[0]}|{$sort[1]}").'&page=%d',
					true,
				)
			,1)->str,
			'tablesize'=>round(($ts->Data_length+$ts->Index_length)/1024/1024/1024,1),
			'sort'=>$sort,
			'countPosts'=>$countPosts,
			'countKw'=>$countKw,
		);
	}
	function posts($start,$stop,$page,$num,$sort,$cid){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		#данные категории
		$cat=new \category\category($cid,1);

		$offset=($page-1)*$num;
		#определение сортировки
		$sort=$tsort=explode('|', $sort);
		if(empty($sort[0])){
			$sort[0]='traf';
			$sort[1]='desc';
		}
		if(!empty($cid)){
			$sql=
			"SELECT SQL_CALC_FOUND_ROWS gr.pid,gr.url,SUM(tc.uniq) as traf,
				SUM(IF(top5='',0,1)) AS k_top5,SUM(IF(top100='',0,1)) AS k_top100,
				GROUP_CONCAT(tbnMatchTop5) AS tbnMatchTop5,GROUP_CONCAT(tbnMatchTop100) AS tbnMatchTop100,
				post.id AS postExists 
			FROM `".PREFIX_SPEC."grepl` gr
			LEFT JOIN `post` ON post.id=gr.pid
			INNER JOIN `".PREFIX_SPEC."category2post` cp ON cp.pid=gr.url && cp.cid='".db::escape($cid)."'
			LEFT JOIN `".PREFIX_SPEC."traffic` tc ON tc.pid=gr.pid && tc.date=gr.date
			WHERE gr.date='".db::escape($start)."' 
			GROUP BY gr.pid ".mkSQLorder($sort)." LIMIT $offset,$num";
		}else{
			$sql=
			"SELECT SQL_CALC_FOUND_ROWS gr.pid,gr.url,SUM(tc.uniq) as traf,
				SUM(IF(top5='',0,1)) AS k_top5,SUM(IF(top100='',0,1)) AS k_top100,
				GROUP_CONCAT(tbnMatchTop5) AS tbnMatchTop5,GROUP_CONCAT(tbnMatchTop100) AS tbnMatchTop100,
				post.id AS postExists 
			FROM `".PREFIX_SPEC."grepl` gr
			LEFT JOIN `post` ON post.id=gr.pid
			LEFT JOIN `".PREFIX_SPEC."traffic` tc ON tc.pid=gr.pid && tc.date=gr.date
			WHERE gr.date='".db::escape($start)."' 
			GROUP BY gr.pid ".mkSQLorder($sort)." LIMIT $offset,$num";
		}
		db::query($sql);
		$posts=array();
		while ($d=db::fetch()) {
			#количество кейвордов c картинками из топа
			$d->tbnMatchTop5=array_sum(explode(',', $d->tbnMatchTop5));
			$d->tbnMatchTop100=array_sum(explode(',', $d->tbnMatchTop100));
			$posts[$d->pid]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		#получаем количество кейвордов
		$pids=array_keys($posts);
		db::query("SELECT pid,COUNT(kid) as kcount FROM `".PREFIX_SPEC."keyword2post` 
			WHERE pid IN(".implode(',', $pids).") GROUP BY pid");
		while ($d=db::fetch()) {
			$posts[$d->pid]->kcount=$d->kcount;
		}
		#получаем главный кейворд
		db::query(
			"SELECT k.title,post.id AS pid FROM `post` 
				INNER JOIN `keyword` k ON k.id=post.kid
			WHERE post.id IN(".implode(',', $pids).")");
		while ($d=db::fetch()) {
			$posts[$d->pid]->ktitle=$d->title;
		}

		return (object)array(
			'date'=>$start,
			'posts'=>$posts,
			'cat'=>$cat,
			'sort'=>$sort,
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'admin/traffic',
						'act'=>'posts',
						'sort'=>"{$sort[0]}|{$sort[1]}",
						'cid'=>$cid,
					),
					'switchType'=>0,
				)
			,1)->str,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::admin_traffic_posts($start,"{$sort[0]}|{$sort[1]}",$cid).'&page=%d',
					true,
				)
			,1)->str,
		);
	}
	function cats($start,$stop,$page,$num,$sort,$parent){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		#данные родительской категории
		$parentCat=new \category\category($parent,1);

		$offset=($page-1)*$num;
		#определение сортировки
		$sort=explode('|', $sort);
		if(empty($sort[0])){
			$sort[0]='traf';
			$sort[1]='desc';
		}
			
		db::query(
			"SELECT SQL_CALC_FOUND_ROWS grc.*,c.title AS title,SUM(tc.uniq) as traf,grc.date AS `date`
			FROM `".PREFIX_SPEC."greplSumCats` grc
				INNER JOIN `category` c ON c.url=grc.cid
				LEFT JOIN `".PREFIX_SPEC."trafficCats` tc ON tc.cid=grc.cid && tc.date=grc.date
			WHERE c.parentId='".db::escape($parent)."' && grc.date='".db::escape($start)."' 
			GROUP BY grc.cid ".mkSQLorder($sort)." LIMIT $offset,$num");
		$sum=array();
		while ($d=db::fetch()) {
			if(isset($sum[$d->cid])){
				$sum[$d->cid]->pcount+=$d->pcount;
				$sum[$d->cid]->kcount+=$d->kcount;
				$sum[$d->cid]->p_top5+=$d->p_top5;
				$sum[$d->cid]->p_top100+=$d->p_top100;
				$sum[$d->cid]->p_tbnMatchTop5+=$d->p_tbnMatchTop5;
				$sum[$d->cid]->p_tbnMatchTop100+=$d->p_tbnMatchTop100;
				$sum[$d->cid]->k_top5+=$d->k_top5;
				$sum[$d->cid]->k_top100+=$d->k_top100;
				$sum[$d->cid]->k_tbnMatchTop5+=$d->k_tbnMatchTop5;
				$sum[$d->cid]->k_tbnMatchTop100+=$d->k_tbnMatchTop100;
				$d->traf=0;
			}else
				$sum[$d->cid]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		$cids=array_keys($sum);

		return (object)array(
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'admin/traffic',
						'act'=>'cats',
					),
					'switchType'=>0,
				)
			,1)->str,
			'sum'=>$sum,
			'sort'=>$sort,
			'parent'=>$parentCat,
			'childs'=>checkCatChilds($cids),
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::admin_traffic_cats($start,"{$sort[0]}|{$sort[1]}",$parent).'&page=%d',
					true,
				)
			,1)->str,
		);
	}
	function itemPost($pid,$start,$stop,$page,$num,$sort){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')||!$pid){
			$this->headers->location=HREF; return;
		}
		#определение сортировки
		$sort=$tsort=explode('|', $sort);
		if(empty($sort[0])){
			$sort[0]='date';
			$sort[1]='desc';
		}

		$post=db::qfetch("SELECT id,title,url FROM `post` WHERE id='".db::escape($pid)."' LIMIT 1");
		$offset=($page-1)*$num;
		db::query(
			"SELECT SQL_CALC_FOUND_ROWS gr.date AS `date`,gr.pid,gr.url,
				SUM(IF(top5='',0,1)) AS k_top5,SUM(IF(top100='',0,1)) AS k_top100,
				GROUP_CONCAT(tbnMatchTop5) AS tbnMatchTop5,GROUP_CONCAT(tbnMatchTop100) AS tbnMatchTop100,
				SUM(tc.uniq) as traf
			FROM `".PREFIX_SPEC."grepl` gr
			LEFT JOIN `".PREFIX_SPEC."traffic` tc ON tc.pid=gr.pid && tc.date=gr.date
			WHERE gr.pid='".db::escape($pid)."' && gr.date>='".db::escape($start)."' && gr.date<='".db::escape($stop)."' 
			GROUP BY gr.pid,gr.date ".mkSQLorder($sort)." LIMIT $offset,$num");
		$dates=array();
		while ($d=db::fetch()) {
			#количество кейвордов без замены
			$d->tbnMatchTop5=count(
				array_filter(explode(',', $d->tbnMatchTop5),function($a){
					return ($a==0);
				})
			);
			$d->tbnMatchTop100=count(
				array_filter(explode(',', $d->tbnMatchTop100),function($a){
					return ($a==0);
				})
			);
			$dates[$d->date]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		return (object)array(
			'post'=>$post,
			'dates'=>$dates,
			'sort'=>$sort,
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'admin/traffic',
						'act'=>'itemPost',
						'pid'=>$pid,
						'sort'=>"{$sort[0]}|{$sort[1]}",
					)
				)
			,1)->str,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::admin_traffic_itemPost($pid,"{$sort[0]}|{$sort[1]}").'&page=%d',
					true,
				)
			,1)->str,
		);
	}
	function itemCat($cid,$start,$stop,$page,$num,$sort){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')||!$cid){
			$this->headers->location=HREF; return;
		}
		#определение сортировки
		$sort=$tsort=explode('|', $sort);
		if(empty($sort[0])){
			$sort[0]='date';
			$sort[1]='desc';
		}

		$cat=db::qfetch("SELECT * FROM `category` WHERE `url`='".($cidSQLclear=db::escape($cid))."'");
		$offset=($page-1)*$num;
		db::query(
			"SELECT SQL_CALC_FOUND_ROWS grc.*,SUM(tc.uniq) as traf
				FROM `".PREFIX_SPEC."greplSumCats` grc
				LEFT JOIN `".PREFIX_SPEC."trafficCats` tc ON tc.cid=grc.cid && tc.date=grc.date
			WHERE grc.cid='{$cidSQLclear}' && grc.date>='".db::escape($start)."' && grc.date<='".db::escape($stop)."'
			GROUP BY grc.date ".mkSQLorder($sort)." LIMIT $offset,$num");
		$cats=array();
		while ($d=db::fetch()) {
			$cats[$d->date]=$d;
		}
		list($count)=db::qrow('SELECT FOUND_ROWS()');

		return (object)array(
			'dateForm'=>module::exec('plugins/dateForm',
				array(
					'start'=>$start,
					'stop'=>$stop,
					'params'=>array(
						'module'=>'admin/traffic',
						'act'=>'itemCat',
						'cid'=>$cid,
						'sort'=>"{$sort[0]}|{$sort[1]}",
					),
				)
			,1)->str,
			'cat'=>$cat,
			'cats'=>$cats,
			'sort'=>$sort,
			'paginator'=>module::exec('plugins/paginator',
				array(
					'page'=>$page,
					'num'=>$num,
					'count'=>$count,
					'uri'=>url::admin_traffic_itemCat($cid,"{$sort[0]}|{$sort[1]}").'&page=%d',
					true,
				)
			,1)->str,
		);
	}
	/*
		функция записи статистики трафика
	*/
	function write($module){#Делаем все обработки для вывода данных
		$this->template='';
		if(!is_writable($this->tmp)) return;
		if($module=='posts'||$module=='gallery'){
			@mkdir($this->tmpstat);
			$data[]=date('H:i:s');
			$data[]=isset($_SERVER['HTTP_X_REAL_IP'])?$_SERVER['HTTP_X_REAL_IP']:$_SERVER['REMOTE_ADDR'];
			$data[]=$_SERVER['REQUEST_URI'];
			$data[]=@$_SERVER['HTTP_REFERER'];
			$data[]=$_SERVER['HTTP_USER_AGENT'];
			file_put_contents($this->tmpcurrent, implode("\t", $data)."\n",8);
		}
	}
	/*
		определяет запущен ли другой подобный процесс
	*/
	function anotherRunning(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		$this->template='';
		$commonPath=str_replace(PATH, '', __DIR__).'/sh/gglreplparse.php';
		$ps=trim(shell_exec("ps aux|grep '{$commonPath}'|grep -v ' grep '|awk '{print $12}'"));
		if($ps!=PATH.$commonPath)
			echo $ps;
		die;
	}
	function initParsing($run){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		$processScript=__DIR__.'/sh/gglreplparse.php';
		$processInput="{$this->tmp}gglreplparse.input";
		if($run){
			if(!$fh=fopen($processInput, 'w')){
				trigger_error("Create input file fail"); die;
			}
			db::query("SET SESSION group_concat_max_len=1000000");
			db::query(
				"SELECT keyword.*,k2p.pid,post.url,GROUP_CONCAT(img.gtbn) tbns FROM `keyword` 
					INNER JOIN `".PREFIX_SPEC."keyword2post` k2p
						ON k2p.kid=keyword.id
					INNER JOIN `post`
						ON post.id=k2p.pid
					INNER JOIN `".PREFIX_SPEC."imgs` img
						ON img.pid=post.id && img.tbl='post'
					GROUP BY img.pid"
			);
			while ($d=db::fetch()) {
				$d->tbns=explode(',', $d->tbns);
				$d->tbns=array_filter($d->tbns,function($a){
					if(!empty($a)) return true;
					else return false;
				});
				fwrite($fh, serialize($d)."\n");
			}
			fclose($fh);
			/*
				запуск процесса (запуститься если, нет запущенного экземпляра)
				1. качает выдачу google, парсит и записывает данные
			*/
			shell_exec("nohup php {$processScript} > {$this->tmp}/gglreplparse.log 2>&1 &");	
		}
		return (object)array(
			'status'=>processRunning($processScript),
		);
	}
	/*
		отвечает на ajax запрос, читает указанные логи
	*/
	function showLog($log,$tail){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		if($log&&file_exists($file=$this->tmp.$log))
			echo shell_exec("tail ".($tail?"-n{$tail} ":'').$file);
		die;
	}
	/*
		стирает лог
	*/
	function clearlog(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		if($dh=opendir($this->tmp)){
			while ($file=readdir($dh)) {
				if(!preg_match('!\.log$!i', $file)) continue;
				unlink($this->tmp.$file);
			}
		}
		die;
	}
	/*
		устанавливает cron
		$type='' - установить задачу
		$type='unset' -  удалить задачу
	*/
	function setCron($type){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		$cronexec=__DIR__."/sh/cron.php";
		exec('crontab -l',$shout);
		foreach ($shout as $k=>$str) {
			#стираем задачу, если существует для этого же исполняемого файла
			if(strstr($str, $cronexec)){
				unset($shout[$k]);
				$rewrite=true;
			}
		}
		if($type=='unset'){
			if(empty($shout)){
				exec("crontab -r");
				$rewrite=false;
			}
		}else{
			$shout[]="10 0 * * * php {$cronexec}";
			$rewrite=true;
		}
		if($rewrite){
			$shin=implode("\n", $shout);
			#create task file
			if(!file_put_contents($taskfile=$this->tmp.'crontask.txt', $shin.PHP_EOL)){
				die('error cron setup');
			}
			exec("crontab {$taskfile}");
			echo 'success';
		}
		die;
	}
	/*
		проверяет наличие задачи в cron
	*/
	function statusCron(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		$cronexec=__DIR__."/sh/cron.php";
		exec('crontab -l',$shout);
		if(empty($shout)){
			$status='off';
		}else{
			foreach ($shout as $k=>$str) {
				if(strstr($str, $cronexec)){
					$status='on'; break;
				}
			}
			if(empty($status)) $status='off';
		}
		echo $status;
		die;
	}
	/*
		показать список конкурентов
	*/
	function getRivals($date,$cacheclear=false){
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseRepl')){
			$this->headers->location=HREF; return;
		}
		set_time_limit(60);
		ini_set('memory_limit','128M');
		$cacheID=$date;
		if($cacheclear){
			$this->template='';
			cache::clear(__NAMESPACE__.'\serpRivals',$cacheID);
		}
		return (object)array(
			'rivals'=>cache::get(__NAMESPACE__.'\serpRivals',array($date),$cacheID,'+1 year'),
			'cacheclear'=>$cacheclear,
		);
	}
}
