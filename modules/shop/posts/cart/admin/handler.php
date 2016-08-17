<?php
namespace shop_posts_cart_admin;
use module,db,url,cache;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function install(){
		db::query("
			CREATE TABLE `shop_cart` (
			`id` int(11) NOT NULL,
			  `uid` int(11) NOT NULL,
			  `ckid` varchar(255) NOT NULL,
			  `data` text NOT NULL,
			  PRIMARY KEY (`id`), 
			  UNIQUE KEY `uid_2` (`uid`,`ckid`), 
			  KEY `ckid` (`ckid`), 
			  KEY `uid` (`uid`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
		",1);
		return;
	}
}
