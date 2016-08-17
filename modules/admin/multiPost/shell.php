<?
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../index.php';#подключаем движок
set_time_limit(0);
error_reporting(-1); ini_set('display_errors', 'On');
$dir=$argv[1];
$pids=json_decode(file_get_contents("$dir/input.txt"));

#Удаляем все неуникальные картинки
db::query("DELETE n1 FROM zspec_imgs n1,zspec_imgs n2 WHERE n1.id > n2.id AND n1.sha1 = n2.sha1");
#Получаем все картинки для постов
$q=db::query("SELECT id,pid,kid,url FROM `".PREFIX_SPEC."imgs` WHERE pid in ('".implode("','",$pids)."')");
while($d=db::fetch()){$imgs[$d->pid][]=$d;}
#Получаем все посты
$purls=array();
$q=db::query("SELECT id,title,url,user,published,datePublish FROM post WHERE id in ('".implode("','",$pids)."')");
while($d=db::fetch($q)){
	list($cats)=db::qrow("SELECT GROUP_CONCAT(cid) FROM `".PREFIX_SPEC."category2post` WHERE pid='$d->url'");
	$cats=explode(",",$cats);
	$i=0;
	#Записываем посты для каждой картинки
	if(!empty($imgs[$d->id]))foreach($imgs[$d->id] as $img){
		$i++;if($i==1)continue;
		$url=substr($img->url,0,-4);
		@$purls[$url]++;
		db::query("INSERT ignore INTO post 
			(date,url,title,user,published,datePublish,countPhoto) 
			VALUES 
			(NOW(),'$url','".db::escape($d->title)."','$d->user','$d->published','$d->datePublish',1)");
		print "<a href='".url::post($url)."' target=_blank>$url</a><br>";
		$nid=db::insert();
		#Записываем категории для новых постов
		$sqlCat=array();
		foreach($cats as $c){
			$sqlCat[]="('$url','$c')";
		}
		db::query("INSERT ignore INTO `".PREFIX_SPEC."category2post` (pid,cid) VALUES ".implode(",",$sqlCat));
		#Обновляем привязку картинки к посту
		db::query("UPDATE `".PREFIX_SPEC."imgs` SET pid='$nid' WHERE id='$img->id'");
		#Обновляем привязку кейворда к посту
		db::query("INSERT ignore INTO `".PREFIX_SPEC."keyword2post` SET kid='$img->kid',pid='$nid'");
	}
}
#Удаляем лишние связи кейворда и поста
db::query("DELETE k2p 
	from `".PREFIX_SPEC."keyword2post` k2p 
	left join `".PREFIX_SPEC."imgs` i 
		on k2p.kid=i.kid && k2p.pid=i.pid 
	WHERE i.id is NULL && k2p.pid in ('".implode("','",$pids)."')");
#Обновляем кол-во картинок для поста
db::query("UPDATE `post` SET `countPhoto`=(SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` 
	WHERE `pid`=`post`.`id` AND `tbl`='post')");

print "<BR>DONE!!!";
?>
<hr>
inserted new urls count:<?=count($purls)?>
