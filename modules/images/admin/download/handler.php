<?php
namespace images_admin_download;
use module,db,url;

class handler{
	function __construct(){
		#Определяем в какой шаблон будем вписывать
		$this->template='template';
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
	}
	/*
		Страница скачивания новых картинок для постов
		- настройки
		- выбор постов
	*/
	function downloadImages($page,$num,$sort,$mainkey=false){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location='/';
			return;
		}
		$data=new \StdClass;
		#проверки ресурсов
		@mkdir($data->tmp=__DIR__.'/daemon/tmp/');
		$data->tmpwritable=is_writable($data->tmp);
		$data->freespace=round(disk_free_space(PATH)/1024/1024/1024,0);
		$data->mainkey=$mainkey;

		$sqlWhere=($mainkey)?"&& post.kid!=''":'';
		$offset=($page-1)*$num;
		$res=db::query(
			"SELECT SQL_CALC_FOUND_ROWS 
				post.id, 
				post.url, 
				post.datePublish, 
				post.title,
				COUNT(DISTINCT imgs.id) AS countPhoto 
			FROM post
			LEFT JOIN ".PREFIX_SPEC."imgs imgs 
				ON imgs.tbl='post' && imgs.pid=post.id
			WHERE 1 {$sqlWhere} 
			GROUP BY post.id ORDER BY {$sort} LIMIT {$offset}, {$num}");
		$total=db::qfetch("SELECT FOUND_ROWS() as total")->total;
		$posts=array();
		while($d=db::fetch($res)){
			$d->keywords=array();
			$posts[$d->id]=$d;
		}
		if($posts){
			if($mainkey){
				db::query(
					"SELECT 
						k2p.pid, 
						k.id as kid, 
						k.title, 
						COUNT(DISTINCT imgs.id) AS countPhoto 
					FROM keyword k 
					JOIN ".PREFIX_SPEC."keyword2post k2p 
						ON k2p.kid=k.id && k2p.tbl='post'
					INNER JOIN `post` 
						ON post.id=k2p.pid && post.kid=k2p.kid
					LEFT JOIN ".PREFIX_SPEC."imgs imgs 
						ON imgs.kid=k2p.kid AND imgs.pid=k2p.pid && imgs.tbl='post'
					WHERE k2p.pid IN (".implode(',',array_keys($posts)).")
					GROUP BY k.id,k2p.pid"
				);
			}else{
				db::query(
					"SELECT 
						k2p.pid, 
						k.id as kid, 
						k.title, 
						COUNT(DISTINCT imgs.id) AS countPhoto 
					FROM keyword k 
					JOIN ".PREFIX_SPEC."keyword2post k2p 
						ON k2p.kid=k.id && k2p.tbl='post'
					LEFT JOIN ".PREFIX_SPEC."imgs imgs 
						ON imgs.kid=k2p.kid AND imgs.pid=k2p.pid && imgs.tbl='post'
					WHERE k2p.pid IN (".implode(',',array_keys($posts)).")
					GROUP BY k.id,k2p.pid"
				);
			}
			while($d=db::fetch()){
				$d->kidpid="{$d->kid}_{$d->pid}";
				$posts[$d->pid]->keywords[]=$d;
			}
		}
		$data->paginator=module::exec('plugins/paginator',array(
			'page'=>$page,'num'=>$num,'count'=>$total,
			'uri'=>url::downloadImages().($sort!='datePublish desc'?'&sort='.urlencode($sort):'').($mainkey?'&mainkey=1':'').'&page=%d'
		),1)->str;
		$data->posts=$posts;
		if(!is_dir($dir=module::$path."/images/files/images")){mkdir($dir);}

		$data->access=$this->uhandler->access();

		return $data;
	}
	/*
		Запуск закачки картинок
	*/
	function status($is_new,$count,$kidpids,$allowgallery,$word,$gglimsize,$manimsize,$skipExists,$mainkey){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location=url::dowloadImages();
			return;
		}
		$this->template='template';
		$dGglparse=__DIR__."/daemon/gglparse.daemon.php";
		$dImgdownload=__DIR__."/daemon/imgdownload.daemon.php";

		if($kidpids){
			#строим параметры "search tools" для парсинга выдачи
			$isw=!empty($gglimsize['ex']['w']);
			$ish=!empty($gglimsize['ex']['h']);
			if($isw||$ish){
				$gglparam='tbs=isz:ex,';
				if($isw&&!$ish) $gglparam.="iszw:{$gglimsize['ex']['w']},iszh:{$gglimsize['ex']['w']}";
				if(!$isw&&$ish) $gglparam.="iszw:{$gglimsize['ex']['h']},iszh:{$gglimsize['ex']['h']}";
				if($isw&&$ish) $gglparam.="iszw:{$gglimsize['ex']['w']},iszh:{$gglimsize['ex']['h']}";
			}elseif(!empty($gglimsize['lth'])){
				$gglparam="tbs=isz:lt,islt:{$gglimsize['lth']}";
			}else{
				$gglparam=($gglimsize['natsize']=='a')?'':"tbs=isz:{$gglimsize['natsize']}";
			}

			$kids=array();
			foreach (explode(',', $kidpids) as $val) {
				list($kid,$pid)=explode('_', $val);
				$kids[(int)$kid]=1;
				$pids[(int)$pid]=1;
			}
			if(!empty($kids)){
				$daemonInput=__DIR__."/daemon/tmp/gglparse.input.txt";
				#if(file_exists($daemonInput)) unlink($daemonInput);
				$fh=fopen($daemonInput, 'a');
				if($mainkey){
					db::query(
						"SELECT k.id AS kid,k.title,k2p.pid,COUNT(imgs.kid) AS total FROM 
							(SELECT * FROM `keyword` WHERE id IN (".implode(',',array_keys($kids)).")) k 
						JOIN `".PREFIX_SPEC."keyword2post` k2p 
							ON k.id=k2p.kid && k2p.tbl='post'
						INNER JOIN `post` 
							ON post.id=k2p.pid && post.kid=k2p.kid
						LEFT OUTER JOIN ".PREFIX_SPEC."imgs imgs 
							ON imgs.kid=k2p.kid AND imgs.pid=k2p.pid
						WHERE k2p.pid IN (".implode(',',array_keys($pids)).") 
						GROUP BY k.id"
					);
				}else{
					db::query(
						"SELECT k.id AS kid,k.title,k2p.pid,COUNT(imgs.kid) AS total FROM 
							(SELECT * FROM `keyword` WHERE id IN (".implode(',',array_keys($kids)).")) k 
						JOIN `".PREFIX_SPEC."keyword2post` k2p 
							ON k.id=k2p.kid && k2p.tbl='post'
						LEFT OUTER JOIN ".PREFIX_SPEC."imgs imgs 
							ON imgs.kid=k2p.kid AND imgs.pid=k2p.pid
						WHERE k2p.pid IN (".implode(',',array_keys($pids)).") 
						GROUP BY k.id,k2p.pid
					");
				}
				while ($d=db::fetch()) {
					if($is_new){ // докачать ещё count
						$d->need=$count;
					}else{ // докачать до count
						if($count<=$d->total){ // пропускаем, те, у которых достаточно
							continue;
						}
						$d->need=$count-$d->total;
					}
					$d->must=$d->need+$d->total;
					if(!empty($word))
						$d->title.=" {$word}";
					$d->gglparam=$gglparam;
					$d->uid=$this->user->id;
					$d->skipExists=$skipExists;
					$d->allowgallery=$allowgallery;
					$d->manimsize=$manimsize;
					fwrite($fh, serialize($d)."\n");
				}
				fclose($fh);
				/*
					запуск демонов (если не были запущены ранее)
						1. качает выдачу google, парсит
						2. качает картинки
				*/
				shell_exec("nohup php {$dGglparse} > ".__DIR__."/daemon/tmp/gglparse.log 2>&1 &");
				shell_exec("nohup php {$dImgdownload} > ".__DIR__."/daemon/tmp/imgdownload.log 2>&1 &");
			}	
		}
		
		return (object)array(
			'dGglparse'=>processRunning($dGglparse),
			'dImgdownload'=>processRunning($dImgdownload),
		);
	}
	/*
		отвечает на ajax запрос, читает указанные логи
	*/
	function showLog($log,$tail,$start){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location='/';
			return;
		}
		if($log&&file_exists($file=__DIR__.'/daemon/tmp/'.$log)){
			$length=1024*10;
			if($start<1){
				$size=filesize($file);
				$start=$size-$length;
				if($start<0)$start=0;
			}
			$fp=fopen($file,'r');
			fseek($fp,$start);
			$str=fread($fp,$length);
			echo (strlen($str)+$start)."\n$str";
		}
		die;
	}
	/*
		останавливает демоны, стирает логи
	*/
	function stopDaemons(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location='/';
			return;
		}
		$dGglparse=__DIR__."/daemon/gglparse.daemon.php";
		$dImgdownload=__DIR__."/daemon/imgdownload.daemon.php";
		$pid=trim(shell_exec($t="ps aux|grep ".escapeshellarg($dGglparse)."|grep -v ' grep '|awk '{print $2}'"));	
		if(is_numeric($pid)){
			$shout=shell_exec("kill {$pid}");
			echo "$dGglparse ".($shout?$shout:'stoped')."\n";
		}
		$pid=trim(shell_exec($t="ps aux|grep ".escapeshellarg($dImgdownload)."|grep -v ' grep '|awk '{print $2}'"));	
		if(is_numeric($pid)){
			$shout=shell_exec("kill {$pid}");
			echo "$dImgdownload ".($shout?$shout:'stoped')."\n";
		}
		#clear logs
		if($dh=opendir($tmpDir=__DIR__."/daemon/tmp/")){
			while ($file=readdir($dh)) {
				if(!preg_match('!\.log$!i', $file)) continue;
				unlink($tmpDir.$file);
			}
		}
		#remove images parse results
		shell_exec('rm -rf '.__DIR__.'/daemon/tmp/gglparseImgs.tmp');
		die;
	}
	/*
		удаляет скаченные html с результатами от google
	*/
	function clearCache(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location='/';
			return;
		}
		shell_exec('rm -rf '.__DIR__.'/daemon/tmp/gglparse.tmp');
		echo 'done';
		die;
	}
	function anotherRunning(){
		$commonPath='modules/images/admin/download/daemon/gglparse.daemon.php';
		$ps=trim(shell_exec("ps aux|grep '/{$commonPath}'|grep -v ' grep '|awk '{print $12}'"));
		if($ps!=PATH.$commonPath)
			echo $ps;
		die;
	}
}

/*
	определяет запущен ли процесс по пути к исполняемому файлу
*/
function processRunning($pname){
	$ps=shell_exec("ps aux|grep ".escapeshellarg($pname)."|grep -v ' grep '");
	if(empty($ps))
		return 0;
	else{
		$running=explode("\n", trim($ps));
		return (count($running));
	}
}
