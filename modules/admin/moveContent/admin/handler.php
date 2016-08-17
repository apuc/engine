<?php
namespace admin_moveContent_admin;
use db;

class handler{
	function install(){
		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."config` (
		  `key` varchar(255) NOT NULL,
		  `value` varchar(255) DEFAULT NULL,
		  PRIMARY KEY (`key`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	}
}
