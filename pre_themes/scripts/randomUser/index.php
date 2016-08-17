<?
define('SITEOFF',1);#отключаем все кроме инициализации движка
include_once __DIR__.'/../../../index.php';#подключаем движок

$need=800;

$count=db::qfetch("SELECT COUNT(id) as cn FROM `".PREFIX_SPEC."users`")->cn;
if($count<$need){
	$fl=file("usernames.txt");
	shuffle($fl);
	foreach($fl as $v){
		$v=trim($v);
		if($v=='')continue;
		@$count++;if($count>800)break;
		$name=$v;
		$regDate=date("Y-m-d",time()-mt_rand(0,12*31*24*3600));
		$pas=mt_rand();
		$hash=db::escape(hash('md5',$pas));
		db::query("INSERT INTO `".PREFIX_SPEC."users` SET name='$name',mail='$name@$purl[host]',pas='$pas',regDate='$regDate',hash='$hash'");
		if($uid=db::insert()){
			$users[]=$uid;
		}
	}
}else{
	db::query("SELECT id from `".PREFIX_SPEC."users`");
	while($d=db::fetch()){
		$users[]=$d->id;
	}
}
$q=db::query("SELECT id FROM post WHERE user=1",2);
while($d=db::fetch($q)){
	$ruid=$users[mt_rand(0,count($users))];
	db::query("UPDATE post SET user='$ruid' WHERE id='$d->id'");
}
