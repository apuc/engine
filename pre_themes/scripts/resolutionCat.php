<?
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../index.php';#подключаем движок
 
db::query("SELECT img.width,img.height,post.url FROM 
	`zspec_imgs` img
	join post
		on post.id=img.pid
");
while($d=db::fetch()){
	@$cnRes["{$d->width}x{$d->height}"]++;
	@$resPids["{$d->width}x{$d->height}"][]=$d->url;
}

arsort($cnRes);
$cnRes=array_slice($cnRes,0,20);

db::query("INSERT INTO `category` set url='resolutions',title='resolutions',view='0'");

foreach($cnRes as $res=>$cn){
	print "insert $res\n";
	db::query("INSERT INTO `category` set url='$res',title='$res',parentId='resolutions'");
	#Записываем связь постов с категорией
	$sql=array();
	foreach($resPids[$res] as $url){
		$sql[]="('$res','$url')";
	}
	db::query("INSERT INTO `".PREFIX_SPEC."category2post` (cid,pid) VALUES ".implode(",",$sql));
}
module::exec("category",array('act'=>'updateCount','tbl'=>'post','cats'=>'all'),1);
print "DONE!!!\n";
