<?php
/*
 * Позволяет создать посты по одному на каждую картинку из одного поста
*/ 
namespace admin_multiPost;
use module,db,url;
#Используем сторонние модули
require_once(module::$path.'/posts/admin/handler.php');

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 * $this->cache - array([methodName1],...) - определяет список методов для кэширования
 */
class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
		$this->tmpdir='/tmp/'.str_replace('http://','',HREF).'/multiPost';
	}
	function index($page,$num,$order){#Делаем все обработки для вывода данных
		if(!$this->uhandler->rbac(@$this->user->rbac,'multiPost')){
			$this->headers->location='/';
			return;
		}
		$offset=($page-1)*$num;
		$orderSql='';
		if($order=="DESC")$orderSql="ORDER by countPhoto DESC";
		elseif($order=="ASC")$orderSql="ORDER by countPhoto ASC";
		$q=db::query("SELECT 
				SQL_CALC_FOUND_ROWS post.id, post.url, post.datePublish, post.title, countPhoto
			FROM post 
			WHERE published='published' $orderSql LIMIT {$offset}, {$num}");
		$total=db::qfetch("SELECT FOUND_ROWS() as total")->total;
		$posts=array();
		while($d=db::fetch($q)){
			$d->kcn=0;
			$posts[$d->id]=$d;
		}
		db::query("SELECT COUNT(kid) as cn,pid from `".PREFIX_SPEC."keyword2post` WHERE pid in('".implode("','",array_keys($posts))."') group by pid");
		while($d=db::fetch()){
			$posts[$d->pid]->kcn=$d->cn;
		}
		return (object)array(
			'paginator'=>module::exec('plugins/paginator',array(
					'page'=>$page,'num'=>$num,'count'=>$total,
					'uri'=>url::admin_multiPost().'&page=%d'
				),1)->str,
			'posts'=>$posts,
			'cnPosts'=>db::qfetch("SELECT COUNT(id) as cn from post")->cn,
		);
	}
	function save($pids){
		if(!$this->uhandler->rbac(@$this->user->rbac,'multiPost')){
			$this->headers->location='/';
			return;
		}
		
		#Создаем папку для записи логов
		recmkdir($this->tmpdir);$log=$this->tmpdir.'/status.txt';
		#Записываем входняе данные для скрипта
		file_put_contents($this->tmpdir."/input.txt",json_encode($pids));
		#Запускаем скрипт
		$script=module::$path."/admin/multiPost/shell.php";
		shell_exec("nohup php {$script} $this->tmpdir > $log 2>&1 &");
		
		header("location: ".url::admin_multiPostStatus());exit;
	}
	function status($ajax){
		if($ajax==1){#отвечает на ajax запрос, читает указанные логи
			$tail=100;
			$file=$this->tmpdir.'/status.txt';
			if(!$this->uhandler->rbac(@$this->user->rbac,'multiPost')){
				$this->headers->location='/';
				return;
			}
			if(file_exists($file))
				echo shell_exec("tail ".($tail?"-n{$tail} ":'').$file);
			die;
		}
	}
}

function recmkdir($dir){
	$e=explode("/",$dir);
	foreach($e as $v){
		$vv.="$v/";
		if(in_array($vv,array('/','/tmp/')))continue;
		@mkdir($vv);
	}
}
