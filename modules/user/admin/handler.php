<?php
namespace user_admin;
use module,db,url;

class handler{
	function __construct(){
		$this->template='template';
		$this->uhandler=module::exec('user',array(),'data')->handler;
		$this->user=$this->uhandler->user;
	}
	function install(){
		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."users` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(30) NOT NULL DEFAULT '',
			`mail` varchar(255) NOT NULL DEFAULT '',
			`phone` int(11) NOT NULL,
			`address` TEXT NOT NULL DEFAULT '',
			`comment` TEXT NOT NULL DEFAULT '',
			`pas` varchar(30) NOT NULL DEFAULT '',
			`regdate` date NOT NULL DEFAULT '0000-00-00',
			`visit` date NOT NULL DEFAULT '0000-00-00',
			`code` varchar(255) NOT NULL DEFAULT '',
			`hash` varchar(255) NOT NULL,
			`rbac` int(11) NOT NULL,
			`rating` int(11) NOT NULL,
			`service` enum('facebook','twitter','google') DEFAULT NULL,
			`token` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `mail` (`mail`),
		  KEY `code` (`code`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8",1);
		db::query("INSERT INTO `zspec_users` (`id`, `name`, `mail`, `pas`, `regdate`, `visit`, `code`, `hash`, `rbac`, `rating`) VALUES
			(1, 'admin', 'engine@gmail.com', 'qwerty', '2011-12-02', '2013-04-01', '791954228', 'd8578edf8458ce06fbc5bb76a58c5ca4', 1, 0)",1);
		return array('instaled');
	}
	/*
		Список всех пользователей и установка прав автора
	*/
	function allUsers($users){
		if(!$this->uhandler->rbac('privilegeSet')){
			$this->headers->location=HREF;
			return;
		}
		#Записываем полученные изменения
		if(!empty($users)){
			foreach($users as $userId => $rbac){
				db::query("UPDATE `".PREFIX_SPEC."users` SET rbac=".(int)$rbac." WHERE id=".(int)$userId);
			}
		}
		$users=array();
		db::query("SELECT * FROM `".PREFIX_SPEC."users` WHERE rbac IN (0,2,4,5) && id!='{$this->user->id}' ORDER BY mail DESC");
		while($d=db::fetch()){
			$users[]=$d;
		}
		return array(
			'users'=>$users
		);
	}
}
