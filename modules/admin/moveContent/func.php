<?php
namespace admin_moveContent;
use module,db,url;


/*
	копирует посты
*/
function copyPosts($handler,$postsFilter){
	$handler->switch_db("src_db");
	$posts=db::qall("SELECT * FROM post WHERE id IN $postsFilter");
	$handler->switch_db("dest_db");
	# параметры автопостинга
	list($per_day)=db::qrow("SELECT `value` FROM ".PREFIX_SPEC."config WHERE `key`='autopost_per_day'");
	if(empty($per_day))$per_day=5;
	$fields_list=$values_list=array();
	$countPosts=count($posts);
	if(isset($posts[0])){
		foreach ($posts[0] as $key => $v){
			$fields_list[]=$key;
		}
		foreach($posts as $k=>$post){
			if($post->published!='published') continue;
			# переопределяем некоторые поля
			$post->published='autoposting';
			$post->datePublish=getDatePublish($per_day);
			$postValues=array();
			foreach ($fields_list as $field) {
				$post->$field=db::escape($post->$field);
				$postValues[]="'{$post->$field}'";
			}
			$values_list[]="(".implode(",", $postValues).")";

			unset($posts[$k]);
			if(count($values_list)>=50||count($posts)<50){
				db::query("INSERT IGNORE INTO post (`".implode('`,`', $fields_list)."`) VALUES ".join(', ', $values_list),1);
				$values_list=array();
			}
	    }
	}
    return $countPosts;
}
/*
	копирует category
*/
function copyCategory($handler,$postsFilter,$cats2post){
	$handler->switch_db("src_db");
	$catsFilter=array(''=>'""');
	foreach($cats2post as $cat2post){
		$catsFilter[$cat2post->cid]='"'.db::escape($cat2post->cid).'"';
	}
	$cats=db::qall("SELECT * FROM category WHERE url IN (".join(', ', $catsFilter).")");
	$handler->switch_db("dest_db");
	$fields_list=$values_list=array();
	if(isset($cats[0]))
		foreach ($cats[0] as $key => $v){
			$fields_list[]=$key;
		}
	foreach($cats as $k=>$cat){
		$postValues=array();
		foreach ($fields_list as $field) {
			$cat->$field=db::escape($cat->$field);
			$postValues[]="'{$cat->$field}'";
		}
		$values_list[]="(".implode(",", $postValues).")";

		unset($cats[$k]);
		if(count($values_list)>=50||count($cats)<50){
			db::query("INSERT IGNORE INTO category (`".implode('`,`', $fields_list)."`) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
	}
}
/*
	копирует category2post
*/
function copyCategory2post($handler,$postsFilter){
	$handler->switch_db("src_db");
	$cats2post=db::qall("SELECT c2p.cid, c2p.pid FROM ".PREFIX_SPEC."category2post AS c2p
		INNER JOIN post AS p ON (c2p.pid = p.url)
		WHERE p.id IN $postsFilter");

	$handler->switch_db("dest_db");
	$values_list=array();
	$t_cats2post=$cats2post;
	foreach($cats2post as $k=>$cat2post){
		$values_list[]=sprintf('("%s", "%s")',
			db::escape($cat2post->cid),
			db::escape($cat2post->pid)
		);
		unset($t_cats2post[$k]);
		if(count($values_list)>=50||count($t_cats2post)<50){
			db::query("INSERT IGNORE INTO ".PREFIX_SPEC."category2post (cid, pid) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
	}
	return $cats2post;
}
/*
	Копирует записи из zpec_imgs, которые принадлежат выбранным постам из post
*/
function copyImages($handler,$postsFilter){
	$handler->switch_db("src_db");
	$imgs=db::qall("SELECT * FROM ".PREFIX_SPEC."imgs WHERE tbl='post' AND pid IN $postsFilter");

	$handler->switch_db("dest_db");
	$fields_list=$values_list=array();
	if(isset($imgs[0]))
		foreach ($imgs[0] as $key => $v){
			# выкидываем неучитываемые поля
			if($key=='id') continue;
			$fields_list[]=$key;
		}
	$imgs4dl=array();
	foreach($imgs as $k=>$img){
		$postValues=array();
		foreach ($fields_list as $field) {
			$img->$field=db::escape($img->$field);
			$postValues[]="'{$img->$field}'";
		}
		$values_list[]="(".implode(",", $postValues).")";

		# сбор каринок для скачивания и формирование ссылки для скачивания
		$img4dl=new \StdClass;
		$img4dl->id=$img->id;
		$img4dl->url=$img->url;
		$img4dl->link=SRC_IMG_HREF.$img->url;
		$imgs4dl[]=$img4dl;

		unset($imgs[$k]);
		if(count($values_list)>=50||count($imgs)<50){
			db::query("INSERT IGNORE INTO ".PREFIX_SPEC."imgs (`".implode('`,`', $fields_list)."`) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
	}

	return $imgs4dl;
}
/*
	копирует таблицу keyword
*/
function copyKeywords($handler,$postsFilter){
	$handler->switch_db("src_db");
	#переносим keyword2post
	$k2p=db::qall("SELECT * FROM `".PREFIX_SPEC."keyword2post` WHERE pid IN $postsFilter");
	if(isset($k2p[0])){
		$handler->switch_db("dest_db");
		$keywordsFilter=$values_list=array();
		foreach ($k2p as $v) {
			$keywordsFilter[]=$v->kid;
			$values_list[]="('{$v->kid}','{$v->pid}')";
			if(count($values_list)>=50){
				db::query("INSERT IGNORE INTO ".PREFIX_SPEC."keyword2post (kid, pid) VALUES ".join(', ', $values_list),1);
				$values_list=array();
			}
		}
		if(count($values_list)){
			db::query("INSERT IGNORE INTO ".PREFIX_SPEC."keyword2post (kid, pid) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
		#переносим keyword
		$handler->switch_db("src_db");
		$keywords=db::qall("SELECT * FROM `keyword` WHERE id IN(".join(',',$keywordsFilter).")");
		if(isset($keywords[0])){
			$handler->switch_db("dest_db");
			$fields_list=$values_list=array();
			foreach ($keywords[0] as $key => $v) {
				$fields_list[]=$key;
			}
			$sql="INSERT IGNORE INTO keyword (".join(',',$fields_list).") VALUES ";
			foreach ($keywords as $values) {
				$t_values=array();
				foreach ($fields_list as $field) {
					$t_values[]="'".db::escape($values->$field)."'";
				}
				$values_list[]="(".join(',',$t_values).")";
				if(count($values_list)>=50){
					db::query($sql.join(', ', $values_list),1);
					$values_list=array();
				}
			}
			if(count($values_list)){
				db::query($sql.join(', ', $values_list),1);
				$values_list=array();
			}
		}
	}
}
/*
	сводит посты
	return array((int)oldID => (int)newID)
*/
function mergePosts($handler,$postsFilter){
	$newpid=array();
	$handler->switch_db("src_db");
	$posts=db::qall("SELECT * FROM post WHERE id IN $postsFilter");
	$handler->switch_db("dest_db");
	# параметры автопостинга
	list($per_day)=db::qrow("SELECT `value` FROM ".PREFIX_SPEC."config WHERE `key`='autopost_per_day'");
	if(empty($per_day))$per_day=5;
	$fields_list=array();
	if(isset($posts[0])){
		foreach ($posts[0] as $key => $v){
			if($key=='id') continue;
			$fields_list[]=$key;
		}
		foreach($posts as $post){
			if($post->published!='published') continue;
			# переопределяем некоторые поля
			$post->published='autoposting';
			$post->datePublish=getDatePublish($per_day);
			$postValues=array();
			foreach ($fields_list as $field) {
				$post->$field=db::escape($post->$field);
				$postValues[]="'{$post->$field}'";
			}

			if(db::query("INSERT INTO post (`".implode('`,`', $fields_list)."`) VALUES "."(".implode(",", $postValues).")"))
				$newpid[$post->id]=db::insert();
	    }
	}
    return $newpid;
}
/*
	сводит таблицу keyword
	return array((int)oldID => (int)newID)
*/
function mergeKeywords($handler,$postsFilter,&$old2newPids){
	$newkid=array();
	$handler->switch_db("src_db");
	$k2p=db::qall("SELECT * FROM `".PREFIX_SPEC."keyword2post` WHERE pid IN $postsFilter");
	if(isset($k2p[0])){
		$keywordsFilter=array();
		foreach ($k2p as $v) {
			$keywordsFilter[]=$v->kid;
		}
		#переносим keyword
		$handler->switch_db("src_db");
		$keywords=db::qall("SELECT * FROM `keyword` WHERE id IN(".join(',',$keywordsFilter).")");
		if(isset($keywords[0])){
			$handler->switch_db("dest_db");
			#проверяем наличие тех же кейвордов в результирующей таблице
			foreach ($keywords as $values) {
				$t_exists[]=db::escape($values->title);
			}
			if(isset($t_exists)){
				db::query("SELECT `id`,`title` FROM `keyword` WHERE `title` IN('".join("','",$t_exists)."')");
				while ($d=db::fetch()) {
					$exists[strtolower($d->title)]=$d->id;
				}
			}

			$fields_list=$values_list=array();
			foreach ($keywords[0] as $key => $v) {
				if($key=='id') continue;
				$fields_list[]=$key;
			}
			$sql="INSERT INTO keyword (".join(',',$fields_list).") VALUES ";
			foreach ($keywords as $values) {
				$lwtitle=strtolower($values->title);
				if(isset($exists[$lwtitle])){
					$newkid[$values->id]=$exists[$lwtitle];
				}else{
					$t_values=array();
					foreach ($fields_list as $field) {
						$t_values[]="'".db::escape($values->$field)."'";
					}
					db::query($sql."(".join(',',$t_values).")",1);
					$newkid[$values->id]=db::insert();
				}
			}
		}
		#записываем новые связи
		$values_list=array();
		foreach ($k2p as $v) {
			if($newkid[$v->kid]&&isset($old2newPids[$v->pid]))
				$values_list[]="('{$newkid[$v->kid]}','{$old2newPids[$v->pid]}')";
			if(count($values_list)>=50){
				db::query("INSERT INTO ".PREFIX_SPEC."keyword2post (kid, pid) VALUES ".join(', ', $values_list),1);
				$values_list=array();
			}
		}
		if(count($values_list)){
			db::query("INSERT INTO ".PREFIX_SPEC."keyword2post (kid, pid) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
	}
	return $newkid;
}
/*
	Сводит записи из zpec_imgs, которые принадлежат выбранным постам из post
*/
function mergeImages($handler,$postsFilter,&$old2newPids,&$old2newKids){
	$handler->switch_db("src_db");
	$imgs=db::qall("SELECT * FROM ".PREFIX_SPEC."imgs WHERE tbl='post' AND pid IN $postsFilter");

	$handler->switch_db("dest_db");
	$fields_list=$values_list=array();
	if(isset($imgs[0]))
		foreach ($imgs[0] as $key => $v){
			# выкидываем неучитываемые поля
			if($key=='id') continue;
			$fields_list[]=$key;
		}
	$imgs4dl=array();
	foreach($imgs as $k=>$img){
		$postValues=array();
		if(!isset($old2newPids[$img->pid])||!isset($old2newKids[$img->kid])) continue;
		$img->pid=$old2newPids[$img->pid];
		$img->kid=$old2newKids[$img->kid];
		foreach ($fields_list as $field) {
			if($field=='id') continue;
			$img->$field=db::escape($img->$field);
			$postValues[]="'{$img->$field}'";
		}
		if(!empty($postValues))
			$values_list[]="(".implode(",", $postValues).")";

		# сбор каринок для скачивания и формирование ссылки для скачивания
		$img4dl=new \StdClass;
		$img4dl->id=$img->id;
		$img4dl->url=$img->url;
		$img4dl->link=SRC_IMG_HREF.$img->url;
		$imgs4dl[]=$img4dl;

		unset($imgs[$k]);
		if(count($values_list)>=1||count($imgs)<50&&!empty($values_list)){
			db::query("INSERT INTO ".PREFIX_SPEC."imgs (`".implode('`,`', $fields_list)."`) VALUES ".join(', ', $values_list),1);
			$values_list=array();
		}
	}

	return $imgs4dl;
}
/*
	расчет времени автопостинга поста
*/
function getDatePublish($per_day=5){
	static $time;
	$start=strtotime('tomorrow');
	$end=strtotime('tomorrow')-1+24*60*60;
	$delta=($end-$start)/$per_day; #количество секунд между постами
	if(!$time)$time=$start;
	else $time+=$delta;
	return date('Y-m-d H:i:s',$time+mt_rand(0,$delta));
}
?>