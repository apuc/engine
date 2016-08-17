<?php
namespace admin_keywords;
use module,db,url,cache;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
		if(!$this->uhandler->rbac(@$this->user->rbac,'parseKeywords')){
			$this->headers->location=HREF;
			return;
		}
	}
	/*
		получает список постов со списком их кейвордов
	*/
	function postsList($page,$num,$sort){
		#проверки ресурсов
		@mkdir($tmp=__DIR__.'/daemon/tmp/');

		if(!in_array($sort,array('datePublish asc','datePublish desc')))
			$sort='datePublish desc';
			
		$offset=($page-1)*$num;
		$res=db::query("SELECT SQL_CALC_FOUND_ROWS id, url, datePublish, title FROM post WHERE published='published' ORDER BY {$sort} LIMIT {$offset}, {$num}");
		$total=db::qfetch("SELECT FOUND_ROWS() as total")->total;
		$posts=array();
		while($d=db::fetch($res)){
			$d->keywords=array();
			$posts[$d->id]=$d;
		}
		if($posts){
			db::query("SELECT k2p.pid, k.id as kid, k.title FROM keyword k "
					. "LEFT OUTER JOIN ".PREFIX_SPEC."keyword2post k2p ON k2p.kid=k.id && k2p.tbl='post'"
					. "WHERE k2p.pid IN (".implode(',',array_keys($posts)).") "
					. "GROUP BY k.id");
			while($d=db::fetch()){
				$d->kidpid="{$d->kid}_{$d->pid}";
				$posts[$d->pid]->keywords[]=$d;
			}
		}
		return (object)array(
			'paginator'=>module::exec('plugins/paginator',array(
				'page'=>$page,'num'=>$num,'count'=>$total,
				'uri'=>url::keywords_PostList().($sort!='datePublish desc'?'&sort='.urlencode($sort):'').'&page=%d'
			),1)->str,
			'posts'=>$posts,
			'tmpwritable'=>is_writable($tmp),
			'tmp'=>$tmp,
		);
	}
	/*
		запускает парсинг
			- с привязкой новых кейвордов к постам (type='keyword')
			- или с созданием новых постов на базе новых кейвордов (type='post')
	*/
	function initParsing($kidpids,$nested,$addword){
		$dGglkwparse=__DIR__."/daemon/gglkwparse.daemon.php";

		if($kidpids){
			$daemonInput=__DIR__."/daemon/tmp/gglkwparse.input.txt";
			$fh=fopen($daemonInput, 'w');
			$kids=array();
			foreach ($kidpids as $val) {
				list($kid,$pid)=explode('_', $val);
				$kids[(int)$kid]=1;
				$pids[(int)$pid]=1;
			}

			if(!empty($kids)){
				db::query("SELECT k.id AS kid,k.title,k2p.pid FROM 
					(SELECT * FROM `keyword` WHERE id IN (".implode(',',array_keys($kids)).")) k 
					JOIN `".PREFIX_SPEC."keyword2post` k2p ON k.id=k2p.kid && k2p.tbl='post'
					WHERE k2p.pid IN (".implode(',',array_keys($pids)).") 
					GROUP BY k.id");
				while ($d=db::fetch()) {
					$d->uid=$this->user->id;
					$d->nested=$nested;
					$d->addword=$addword;
					fwrite($fh, serialize($d)."\n");
				}
			}else return;
			fclose($fh);
			/*
				запуск демонов (если не были запущены ранее)
				1. качает выдачу google, парсит кейворды
			*/
			shell_exec("nohup php {$dGglkwparse} > ".__DIR__."/daemon/tmp/gglkwparse.log 2>&1 &");
		}
		return (object)array(
			'dGglkwparse'=>processRunning($dGglkwparse),
		);
	}
	/*
		отвечает на ajax запрос, читает указанные логи
	*/
	function showLog($log,$tail){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location='/';
			return;
		}
		if($log&&file_exists($file=__DIR__.'/daemon/tmp/'.$log))
			echo shell_exec("tail ".($tail?"-n{$tail} ":'').$file);
		die;
	}
	/*
		останавливает демоны, стирает логи
	*/
	function stopDaemons(){
		if(!$this->uhandler->rbac(@$this->user->rbac,'downloadImages')){
			$this->headers->location=HREF;
			return;
		}
		$dGglkwparse=__DIR__."/daemon/gglkwparse.daemon.php";
		$pid=trim(shell_exec($t="ps aux|grep ".escapeshellarg($dGglkwparse)."|grep -v ' grep '|awk '{print $2}'"));	
		if(is_numeric($pid)){
			$shout=shell_exec("kill {$pid}");
			echo "$dGglkwparse ".($shout?$shout:'stoped')."\n";
		}		
		#clear logs
		if($dh=opendir($tmpDir=__DIR__."/daemon/tmp/")){
			while ($file=readdir($dh)) {
				if(!preg_match('!\.log$!i', $file)) continue;
				unlink($tmpDir.$file);
			}
		}
		die;
	}
}

/*
	определяет запущен ли процесс по пути к исполняемому файлу
*/
function processRunning($pname){
	$ps=shell_exec($t="ps aux|grep ".escapeshellarg($pname)."|grep -v ' grep '");
	if(empty($ps))
		return 0;
	else{
		$running=explode("\n", trim($ps));
		return (count($running));
	}
}
