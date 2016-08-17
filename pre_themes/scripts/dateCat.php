<?
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../index.php';#подключаем движок

#Обновляем даты постов в пределах 4 недель
require_once(module::$path.'/posts/admin/func.php');
\posts_admin\updateDates();

db::query("SELECT url,datePublish FROM post WHERE published='published'");
while($d=db::fetch()){
	@$dates[date("Y-m-d",strtotime($d->datePublish))][]=$d->url;
}
db::query("INSERT INTO `category` set url='dates',title='dates',view='0'"); 

foreach($dates as $date=>$posts){
	print "insert $date\n";
	db::query("INSERT INTO `category` set url='$date',title='$date',parentId='dates'");
	#Записываем связь постов с категорией
	$sql=array();
	foreach($posts as $url){
		$sql[]="('$date','$url')";
	}
	db::query("INSERT INTO `".PREFIX_SPEC."category2post` (cid,pid) VALUES ".implode(",",$sql));
}

module::exec("category",array('act'=>'updateCount','tbl'=>'post','cats'=>'all'),1);
print "DONE!!!\n";

