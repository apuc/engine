<?php
namespace posts_comments_admin;
use module,db;

#Используем собственные функции
#require_once(PATH."modules/example/func.php");

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->tbl=PREFIX_SPEC."comments";
	}
	/*Значение поля moderate
	 * 0 - новый комент
	 * 1 - на модерации
	 * 2 - подозрительный комент
	 * 3 - прошедшие модерацию
	 * 4 - не прошедшие модерацию
	 */ 
	function index($type){#Делаем все обработки для вывода данных
		#Проверяем новые коменты на подозрительность (наличие ссылок)
		$q=db::query("SELECT * FROM `$this->tbl` WHERE moderate=0");
		while($d=db::fetch($q)){
			if(preg_match('!<a!is',$d->text) or preg_match('![a-z\d\-\_]\.[a-z\d\-\_]{2,}!is',$d->text)){
				$mod=2;
			}else{
				$mod=1;
			}
			db::query("UPDATE `$this->tbl` SET moderate='$mod' WHERE id=$d->id LIMIT 1");
		}
		#Считаем колличество разных типов коментариев.
		$count=array(0,0,0,0,0);
		db::query("SELECT count(*) as cn,moderate FROM `$this->tbl` GROUP BY moderate");
		while($d=db::fetch()){
			$count[$d->moderate]=$d->cn;
		}
		#Формируем список коментариев на вывод. 
		$comments=$user=array();
		db::query(
			"SELECT c.*,post.url FROM `$this->tbl` c 
				LEFT JOIN `post` ON post.id=c.pid
			WHERE c.moderate='$type'");
		while($d=db::fetch()){
			$comments[$d->id]=$d;
			$comments[$d->id]->user=&$user[$d->uid];
		}
		if(count($user)){
			db::query("SELECT * FROM `".PREFIX_SPEC."users` WHERE id in('".implode("','",array_keys($user))."')");
			while($d=db::fetch()){
				$user[$d->id]=$d;
			}
		}
		return (object)array(
			'count'=>$count,
			'comments'=>$comments,
			'type'=>$type,
		);
	}
	function save($com,$seen,$type){#Сохранить результаты модерации
		if(!empty($com))$com=array_flip(array_map('intval',$com));
		foreach($seen as $k=>$id){
			$seen[$k]=(int)$id;
			if(isset($com[$id])) unset($seen[$k]);
		}
		#Обновляем отмеченые
		if(count($com)){
			$conv=array(1=>3,2=>3,3=>4,4=>3);
			$mod=$conv[$type];
			db::query("UPDATE `$this->tbl` SET moderate='$mod' WHERE id in ('".implode("','",array_keys($com))."')");
		}
		#Обновляем неотмеченные но увиденные
		if(count($seen)){
			$conv=array(1=>4,2=>4);
			if(!empty($conv[$type])){
				$mod=$conv[$type];
				db::query("UPDATE `$this->tbl` SET moderate='$mod' WHERE id in ('".implode("','",$seen)."')");
			}
		}
		return (object)array(
			'html'=>module::exec('posts/comments/admin',array('type'=>$type),1)->str
		);
	}
	function install(){
		db::query(
			"CREATE TABLE IF NOT EXISTS `{$this->tbl}` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`uid` int(11) NOT NULL DEFAULT '0',
			`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`text` text NOT NULL,
			`pid` int(11) NOT NULL DEFAULT '0',
			`tbl` varchar(255) NOT NULL DEFAULT '',
			`cid`VARCHAR(255) NOT NULL DEFAULT '',
			`moderate` tinyint(1) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `moderate` (`moderate`),
			KEY `pid` (`pid`,`tbl`),
			KEY `date` (`date`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8",1);
	}
}
