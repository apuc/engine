<?php
/*
	обновление для истории постов
*/
function update1(){
	$tbl=PREFIX_SPEC."postHistory";
	db::query("CREATE TABLE IF NOT EXISTS `{$tbl}` (
	  `indexId` int(11) NOT NULL AUTO_INCREMENT,
	  `id` int(11) NOT NULL,
	  `date` datetime NOT NULL,
	  `url` varchar(255) NOT NULL,
	  `title` varchar(255) NOT NULL,
	  `txt` text NOT NULL,
	  `sources` text NOT NULL,
	  `user` int(11) NOT NULL,
	  `published` enum('unpublished','published','readytopublish','researchdone','remakesearch','remakecontent','waitcopywrite','researchchecked') NOT NULL,
	  `site` varchar(255) NOT NULL,
	  `statViews` int(11) NOT NULL,
	  `statViewsShort` int(11) NOT NULL,
	  `statShortFlag` date NOT NULL,
	  PRIMARY KEY (`indexId`),
	  FULLTEXT KEY `txt` (`txt`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
	list($id)=db::qrow("SELECT `indexId` FROM {$tbl} LIMIT 1");
	if(empty($id))
		db::query("INSERT INTO `{$tbl}` (`id`, `date`, `url`, `title`, `txt`, `sources`, `user`, `published`, `site`, `statViews`, `statViewsShort`, `statShortFlag`) (SELECT * FROM `post`)");
}
/*
	ограничение длины поля url
		изменения для таблиц:
		post, category, category2post
	уникализация category2post по cid, pid
*/
function update2(){
	$tbl=PREFIX_SPEC."category2post";
	list($check)=db::qrow("SELECT COUNT(TABLE_NAME) FROM information_schema.COLUMNS 
		WHERE TABLE_SCHEMA='".DB_NAME."' && (((TABLE_NAME='post' OR TABLE_NAME='category') && COLUMN_NAME='url') OR (TABLE_NAME='{$tbl}' && COLUMN_NAME='cid' OR COLUMN_NAME='pid')) && COLUMN_TYPE='varchar(255)'");
	if(@$check==4){
		db::query("ALTER TABLE `post` CHANGE `url` `url` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		db::query("ALTER TABLE `".PREFIX_SPEC."postHistory` CHANGE `url` `url` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		db::query("ALTER TABLE `category` CHANGE `url` `url` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		db::query("ALTER TABLE `{$tbl}` 
			CHANGE `cid` `cid` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'category ID', 
			CHANGE `pid` `pid` VARCHAR(125) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'post ID'");
		db::query("ALTER TABLE `{$tbl}` ADD UNIQUE (`cid`,`pid`)");
	}
}
/*
	создание индекса
*/
function update3(){
	list($exist)=db::qrow("SHOW INDEX FROM `post` WHERE `Column_name`='published'");
	if(empty($exist)){
		db::query("ALTER TABLE `post` ADD INDEX(`published`)");
	}
}
/* 
	обновление количества постов в базе
*/
function update4(){
	/* Получаем в цикле все категории */
	$res=db::query("SELECT `url` FROM `category`");
	while ($obj=db::fetch($res)){
		/* Получаем список всех постов в категории */
		list($count)=db::qrow("SELECT COUNT( `post`.`url` ) FROM `post` post
		LEFT OUTER JOIN `".PREFIX_SPEC."category2post` rel ON rel.pid=post.url
		WHERE rel.cid='$obj->url' && post.published IN('published')");
		/* Сохраняем значение счетчика в базу */
		db::query("UPDATE `category` SET `count`='$count' WHERE `url`='$obj->url' LIMIT 1");
	}
}
/*
	изменение таблицы post, добавляем поле datePublish
*/
function update5(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='datePublish'");
	if(empty($check)){
		db::query("ALTER TABLE `post` add column datePublish DATETIME NULL after statShortFlag");
		db::query("UPDATE `post` SET datePublish=`date`");
	}
}
/*
	Новое поле text в таблице zspec_imgs для записи снипитов с гугла
*/
function update6(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='text'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD `text` VARCHAR(255) NOT NULL AFTER `title`");
	}
}
/*
 * добавление полей для просмотров картинок в галереи
 */
function update7(){
	$tbl=PREFIX_SPEC."imgs";
	$fields=array("statViews"=>"INT NOT NULL","statViewsShort"=>"INT NOT NULL","statShortFlag"=>"DATE NOT NULL");
	$isset_fields=db::query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
							 WHERE (column_name = 'statViewsShort' OR column_name = 'statViews' OR column_name = 'statShortFlag')
							 AND table_name = '".$tbl."' AND table_schema = '".DB_NAME."'");
	while($row=db::fetch($isset_fields)){
		$isset[$row->COLUMN_NAME]=1;
	}
	foreach($fields as $k=>$v){
		if(!isset($isset[$k])){
			db::query("ALTER TABLE `".$tbl."` ADD ".$k." ".$v."");
		}
	}
}
/*
	Удаляем битые ссылки в контенте
*/
function update8(){
   $pattern='#<a class="overlay-enable" href="/image-post/([0-9]+)-(.*?).html" data-tbl="post" data-pid="([0-9]+)" data-url="(.*?)"><img src="(.*?)" alt="(.*?)" /></a>#si';

   $q=db::query("SELECT `id`, `txt` FROM `post` WHERE `txt` LIKE '%<a class=\\\"overlay-enable\\\"%'");
   while($row=db::fetch($q)){
      preg_match_all($pattern,$row->txt,$matches);
      if(count($matches[0])){
         $count_replaces = 0;
         foreach($matches[0] as $k=>$v){
            if($matches[1][$k]!=$row->id){
               $row->txt=preg_replace($pattern,'<a class="overlay-enable" href="/image-post/'.$row->id.'-$2.html" data-tbl="post" data-pid="'.$row->id.'" data-url="$4"><img src="$5" alt="$6" /></a>',$row->txt);
               $count_replaces++;
               break;
            }
         }
         if($count_replaces>0) {
            db::query("UPDATE `post` SET `txt` = '".db::escape($row->txt)."' WHERE `id` = '".$row->id."'");
         }
      }
   }

}
/*
	изменение таблицы post, добавляем статус autoPosting
*/
function update9(){
	list($check)=db::qrow("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME='post' AND COLUMN_NAME='published' AND COLUMN_TYPE LIKE '%autoPosting%'");
	if(empty($check)){
		db::query("ALTER TABLE `post` CHANGE COLUMN `published` `published` ENUM('unpublished','published','readytopublish','researchdone','remakesearch','remakecontent','waitcopywrite','researchchecked','autoposting') NOT NULL");
	}
}
/*
	добавляем таблицу конфигурации zspec_config
*/
function update10(){
	list($check)=db::qrow("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME='".PREFIX_SPEC."config'");
	if(empty($check)){
		db::query("CREATE TABLE `".PREFIX_SPEC."config` 
			(`key` VARCHAR(255) NOT NULL, `value` VARCHAR(255) NULL, PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	}
}
/*
	изменение таблицы zspec_imgs, добавляем поле 
*/
function update11(){
	list($check)=db::qrow("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME='".PREFIX_SPEC."imgs' AND COLUMN_NAME='sourcePage'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD COLUMN `sourcePage` VARCHAR(255) NULL AFTER `source`");
	}
	list($check)=db::qrow("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' AND TABLE_NAME='".PREFIX_SPEC."imgs' AND COLUMN_NAME=text'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD COLUMN `text` VARCHAR(255) NULL AFTER `title`");
	}
}
/*
 * Добавление поля countPhoto в таблицу post
 */
function update12(){
	list($check)=db::qrow("SELECT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' TABLE_NAME='post' and COLUMN_NAME='countPhoto'");
	if(empty($check)){
		db::query("ALTER TABLE `post` add column `countPhoto` INT NOT NULL");
	}
	db::query("UPDATE `post` SET `countPhoto`=(SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` WHERE `pid`=`post`.`id` AND `tbl`='post')");
}

function update13(){
	db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."socStat` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`buttonID` varchar(100) NOT NULL,
		`date` date NOT NULL,
		`like` int(11) NOT NULL,
		`unlike` int(11) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `buttonID` (`buttonID`,`date`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
	");
}
/*
	изменение таблицы post, добавляем поля Like/Dislike
*/
function update14(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='FBpublished'");
	if(empty($check)){
		db::query("ALTER TABLE `post` 
			ADD COLUMN `FBpublished` ENUM('ban','done','proc') NOT NULL DEFAULT 'proc' AFTER `countPhoto`,
			ADD COLUMN `like` INT SIGNED NOT NULL DEFAULT '0' AFTER `FBpublished`,
			ADD COLUMN `dislike` INT SIGNED NOT NULL DEFAULT '0' AFTER `like`");
		db::query("CREATE TABLE `".PREFIX_SPEC."user2vote` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`userID` int(10) unsigned NOT NULL,
			`postID` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `vote` (`userID`,`postID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	}
}
/*
	изменение таблицы users, добавляем поля service,token для авторизации в соц сетях
*/
function update15(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."users' && COLUMN_NAME='token'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."users` ADD COLUMN `service` ENUM('facebook','twitter','google') NULL AFTER `rating`, ADD COLUMN `token` VARCHAR(255) NULL AFTER `service`");
	}
}
/*
	новые таблицы keywords, zspec_keywords2post; новая структура zspec_imgs
*/
function update16(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='kid'");
	if($check){
		return;
	}
	db::query("CREATE TABLE IF NOT EXISTS `keyword` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`title` varchar(255) NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `title` (`title`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8
	");
	db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."keyword2post` (
		`kid` int(11) unsigned NOT NULL,
		`pid` int(11) unsigned NOT NULL,
		UNIQUE KEY `kid2pid` (`kid`,`pid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	");
	// конвертация данных
	$imgs=array();
	$res=db::query("SELECT id, title, pid FROM `".PREFIX_SPEC."imgs`");
	while($d=db::fetch($res)){
		if(empty($d->title)) continue;
		if(!isset($imgs[$d->title])){
			$imgs[$d->title]=array('ids'=>array(),'pids'=>array());
		}
		$imgs[$d->title]['ids'][]=(int)$d->id;
		$imgs[$d->title]['pids'][]=(int)$d->pid;
	}
	// добавляем поле kid
	db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD kid int(11) unsigned AFTER title");
	db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD INDEX(`kid`)");
	foreach($imgs as $title=>$data){
		db::query('INSERT INTO keyword (title) VALUES ("'.db::escape($title).'")');
		$kid=(int)db::insert();
		if($kid){
			$values=array();
			foreach(array_unique($data['pids']) as $pid){
				$values[]="($kid,$pid)";
			}
			db::query('INSERT INTO `'.PREFIX_SPEC.'keyword2post` (kid,pid) VALUES '.implode(',',$values));
			db::query('UPDATE `'.PREFIX_SPEC.'imgs` SET kid='.$kid.' WHERE id IN ('.implode(',',$data['ids']).')');
		}
	}
	// удаляем поле title
	db::query("ALTER TABLE `".PREFIX_SPEC."imgs` DROP title");
}
/*
	изменение таблицы post, добавляем поле pincid
*/
function update17(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='pincid'");
	if(empty($check)){
		db::query("ALTER TABLE `post` ADD COLUMN pincid VARCHAR(255) NOT NULL DEFAULT ''");
	}
}
/*
	изменение таблицы post, добавляем FULLTEXT индекс для поля title
	изменение таблицы zspec_imgs, добавляем filesize
*/
function update18(){
	list($check)=db::qrow("SELECT `COLUMN_KEY` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='title'");
	if($check=='')
		db::query("ALTER TABLE `post` ADD FULLTEXT(`title`,`txt`)");
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='filesize'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD `filesize` INT NOT NULL AFTER `height`");
	}
}
/*
	добавляет index для поля statViewsShort таблицы post
*/
function update19(){
	list($check)=db::qrow("SELECT `COLUMN_KEY` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='statViewsShort'");
	if($check=='')
		db::query("ALTER TABLE `post` ADD INDEX(`statViewsShort`)");
}
/*
	добавляет поле kid - основной кейворд в таблицу post
*/
function update20(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='kid'");
	if(empty($check)){
		db::query("ALTER TABLE `post` ADD `kid` INT NOT NULL , ADD INDEX (`kid`)");
	}
}
function update21(){
	list($check)=db::qrow("SELECT `COLUMN_KEY` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='text'");
	if($check==''){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD FULLTEXT (`text`);");
	}
}
/*
	добавляет новое поле gtitle в zspec_imgs
*/
function update22(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='gtitle'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD `gtitle` VARCHAR(255) NOT NULL DEFAULT '' AFTER `kid`");
	}
}
/*
	исправляет таблицу комментариев
*/
function update23(){
	list($check)=db::qrow("SELECT `COLUMN_KEY` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."comments' && COLUMN_NAME='date'");
	if($check==''){
		db::query("ALTER TABLE `".PREFIX_SPEC."comments` ADD INDEX(`date`)");
	}
	db::query("ALTER TABLE `".PREFIX_SPEC."comments` DROP INDEX text");
	list($check)=db::qrow("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."comments' && COLUMN_NAME='text'");
	if($check!='text'){
		db::query("ALTER TABLE `".PREFIX_SPEC."comments` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
	}
	list($check)=db::qrow("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."comments' && COLUMN_NAME='module'");
	if(!empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."comments` DROP `module`");
	}
}
/*
	добавляет категорию к таблице комментариев
*/
function update24(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."comments' && COLUMN_NAME='cid'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."comments` ADD `cid` VARCHAR(255) NOT NULL DEFAULT '' AFTER `tbl`");
	}
}
/*
	добавляет admin count в таблицу категорий
*/
function update25(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='category' && COLUMN_NAME='countAdmin'");
	if(empty($check)){
		db::query("ALTER TABLE `category` ADD `countAdmin` INT NOT NULL COMMENT 'cache admin count' AFTER `count`");
	}
}
function update26(){
	list($check)=db::qrow("SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."imgs' && COLUMN_NAME='gtbn'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD `gtbn` VARCHAR(255) NOT NULL AFTER `gtitle`, ADD INDEX (`gtbn`)");
	}
}
/*
	добавляет таблицы для модуля admin/traffic
*/
function update27(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."grepl'");
	if(empty($check))
		module::exec('admin/traffic/admin',array('act'=>'install'),1);
}
/*
	исправляет структуру таблиц модуля admin/traffic
*/
function update28(){
	list($check)=db::qrow("SELECT `DATA_TYPE` FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."traffic' && COLUMN_NAME='cid'");
	if($check=='varchar'){
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` ADD UNIQUE KEY `date_pid_type` (`date`, `pid`, `type`)");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` DROP INDEX date_2");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` DROP INDEX date_4");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` DROP INDEX date_6");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` DROP INDEX date_5");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` CHANGE `cid` `cid` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		db::query("ALTER TABLE `".PREFIX_SPEC."traffic` ENGINE = MyISAM");
		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."trafficCats` (
		  `date` date NOT NULL,
		  `cid` varchar(255) NOT NULL,
		  `uniq` int(11) NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;",1);
		db::query("ALTER TABLE `".PREFIX_SPEC."trafficCats` ADD UNIQUE KEY `date_cid` (`date`,`cid`), ADD KEY `date` (`date`)",1);
	}
}
/*
	дополняет структуру таблиц модуля admin/traffic
*/
function update29(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."greplSumCats' && COLUMN_NAME='pcount'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."greplSumCats` ADD `pcount` int(11) NOT NULL AFTER `cid`");
		db::query("ALTER TABLE `".PREFIX_SPEC."greplSumCats` ADD `kcount` int(11) NOT NULL AFTER `pcount`");
	}
}
/*
	дополняет структуру таблицы post
*/
function update30(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='theme'");
	if(empty($check)){
		db::query("ALTER TABLE `post` ADD `theme` VARCHAR(255) NOT NULL DEFAULT '' AFTER `kid`",1);
	}
}
/*
	дополняет структуру таблицы category
*/
function update31(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='category' && COLUMN_NAME='subCatList'");
	if(empty($check)){
		db::query("ALTER TABLE `category` ADD `subCatList` int(11) NOT NULL AFTER `view`",1);
	}
}
/*
	улучшение производиьельности запросов к таблице zspec_imgs
*/
function update32(){
	list($check)=db::qrow("SHOW INDEX FROM `".PREFIX_SPEC."imgs` WHERE key_name='tbl_priority'");
	if(empty($check))
		db::query("ALTER TABLE `".PREFIX_SPEC."imgs` ADD INDEX `tbl_priority` (`tbl`,`priority`)",1);
}
/*
	дополняет структуру таблицы category
*/
function update33(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='category' && COLUMN_NAME='theme'");
	if(empty($check)){
		db::query("ALTER TABLE `category` ADD `theme` VARCHAR(255) NOT NULL DEFAULT '' AFTER `view`",1);
	}
}
/*
	дополняет структуру таблицы post
*/
function update34(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='data'");
	if(empty($check)){
		db::query("ALTER TABLE `post` ADD `data` TEXT NOT NULL AFTER `txt`",1);
	}
}
/*
	дополняет структуру таблицы keyword2post
*/
function update35(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."keyword2post' && COLUMN_NAME='tbl'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."keyword2post` DROP INDEX kid2pid");
		db::query("ALTER TABLE `".PREFIX_SPEC."keyword2post` ADD `tbl` VARCHAR(255) NOT NULL AFTER `pid`",1);
		db::query("ALTER TABLE `".PREFIX_SPEC."keyword2post` ADD UNIQUE KEY `kid2pid` (`kid`,`pid`,`tbl`), ADD INDEX(`pid`, `tbl`)",1);
		db::query("UPDATE `".PREFIX_SPEC."keyword2post` SET `tbl`='post'");
	}
}
/*
	добавляем поле phone к пользователя
*/
function update36(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."users' && COLUMN_NAME='phone'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."users` ADD `phone` int(11) NOT NULL AFTER `mail`",1);
	}
}
/*
	добавляем поле adress,comment к пользователям
*/
function update37(){
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."users' && COLUMN_NAME='address'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."users` ADD `address` TEXT NOT NULL AFTER `phone`",1);
	}
	list($check)=db::qrow("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='".PREFIX_SPEC."users' && COLUMN_NAME='comment'");
	if(empty($check)){
		db::query("ALTER TABLE `".PREFIX_SPEC."users` ADD `comment` TEXT NOT NULL AFTER `address`",1);
	}
}