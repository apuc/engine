<?php
namespace admin_traffic_admin;
use module,db,url;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function install(){#Делаем все обработки для вывода данных
		db::query("CREATE TABLE `".PREFIX_SPEC."grepl` (
		  `date` date NOT NULL,
		  `keyword` varchar(255) NOT NULL,
		  `pid` int(11) NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `top5` text NOT NULL,
		  `top100` text NOT NULL,
		  `repl` text NOT NULL,
		  `tbnMatchTop5` text NOT NULL,
		  `tbnMatchTop100` text NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8",1);
		#индексы
		db::query("ALTER TABLE `".PREFIX_SPEC."grepl`
			 ADD UNIQUE KEY `date` (`date`,`keyword`,`pid`), ADD KEY `url` (`url`), ADD KEY `keyword` (`keyword`), ADD KEY `pid` (`pid`), ADD KEY `date_2` (`date`), ADD KEY `date_3` (`date`,`pid`)",1);
		
		db::query("CREATE TABLE `".PREFIX_SPEC."greplSum` (
		  `date` date NOT NULL,
		  `p_top5` int(11) NOT NULL,
		  `p_top100` int(11) NOT NULL,
		  `p_tbnMatchTop5` int(11) NOT NULL,
		  `p_tbnMatchTop100` int(11) NOT NULL,
		  `k_top5` int(11) NOT NULL,
		  `k_top100` int(11) NOT NULL,
		  `k_tbnMatchTop5` int(11) NOT NULL,
		  `k_tbnMatchTop100` int(11) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8",1);
		#индексы
		db::query("ALTER TABLE `".PREFIX_SPEC."greplSum`	ADD PRIMARY KEY (`date`);",1);

		db::query("CREATE TABLE `".PREFIX_SPEC."greplSumCats` (
		  `date` date NOT NULL,
		  `cid` varchar(255) NOT NULL,
		  `pcount` int(11) NOT NULL,
		  `kcount` int(11) NOT NULL,
		  `p_top5` int(11) NOT NULL,
		  `p_top100` int(11) NOT NULL,
		  `p_tbnMatchTop5` int(11) NOT NULL,
		  `p_tbnMatchTop100` int(11) NOT NULL,
		  `k_top5` int(11) NOT NULL,
		  `k_top100` int(11) NOT NULL,
		  `k_tbnMatchTop5` int(11) NOT NULL,
		  `k_tbnMatchTop100` int(11) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8",1);
		#индексы
		db::query("ALTER TABLE `".PREFIX_SPEC."greplSumCats` ADD UNIQUE KEY `date_2` (`date`,`cid`), ADD KEY `cid` (`cid`), ADD KEY `date` (`date`);",1);

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."traffic` (
		  `date` date NOT NULL,
		  `cid` text NOT NULL,
		  `pid` int(11) NOT NULL,
		  `type` varchar(255) NOT NULL,
		  `url` varchar(255) NOT NULL,
		  `uniq` int(11) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;",1);
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` ADD UNIQUE KEY `date_pid_type` (`date`,`pid`,`type`), ADD KEY `date_3` (`date`,`pid`)",1);

		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."trafficCats` (
		  `date` date NOT NULL,
		  `cid` varchar(255) NOT NULL,
		  `uniq` int(11) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;",1);
		db::query("ALTER TABLE `".PREFIX_SPEC."trafficCats` ADD UNIQUE KEY `date_cid` (`date`,`cid`), ADD KEY `date` (`date`)",1);
		return (object)array();
	}
}
