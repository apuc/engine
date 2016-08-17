<?php
/*
	Закачка картинок
		1. Закачивает картинки на диск
		2. Записывает в БД скачавшиеся
	Вход:
		pid, tbl, uid, source, link, url

	url - имя картинки на диске
	link - URL откуда нужно скачать
*/
if(isset($argv)){
	define ('SITEOFF',1);
	include __DIR__.'/../../../../index.php';
	set_time_limit(3600*3);	
	# подключаем функции модулей
	include_once PATH.'modules/images/handler.php';
	include_once PATH.'modules/posts/admin/handler.php';
	include_once PATH.'modules/images/admin/handler.php';

	# обрабатываем варианты запуска, проверяем входные параметры
	if(empty($argv[2])) die('no params');
	$params=unserialize(base64_decode($argv[2]));
	print_r($params);
	if(empty($params->uid)||empty($params->pid)||empty($params->tbl)||empty($params->link)||empty($params->url)) die('wrong params');

	$DBsave=Download($params);# закачиваем и делаем resize
	DBsave($DBsave);# запись в базу	
}

function Download($img){
	$DBsave=new StdClass;
	
	$imgDir=PATH.'modules/images/files/images/';
	# resize options
	$moduleImages=new \images\Handler;

	$urlData=parse_url($img->link);
	print "\nfile exist:\n";var_dump(file_exists($imgFile=$imgDir.$img->url));print "\n$imgFile\n\n";
	if(!file_exists($imgFile=$imgDir.$img->url)||@filesize($imgFile)==0){
		$ch=curl_init($img->link);
		curl_setopt_array($ch,array(
				CURLOPT_REFERER=>$urlData['scheme'].'://'.$urlData['host'].'/',
				CURLOPT_FOLLOWLOCATION=>1,
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_HEADER=>0,
				CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
			)
		);
		# сохранение и resize
		file_put_contents($imgFile,curl_exec($ch));
		print "\ngetimagesize:\n";var_dump(getimagesize($imgFile));print "\n\n";
		$imgData=getimagesize($imgFile);
		if($imgData&&!\images_admin\storeDBOnly($imgFile,$img->pid,$img->tbl,$img->uid,$img->kid)){
			$img->sha1=sha1_file($imgFile);
			foreach($moduleImages::$resolution as $r){
				$moduleImages->mkThumb($img->url,$r[0],$r[1],1);
			}
			$img->source=$img->link;
			$img->width=$imgData[0];
			$img->height=$imgData[1];
			$img->filesize=filesize($imgFile);
			$DBsave=$img;
			# генерируем сниппет
			$img->text=getImageDescription($img->sourcePage, $img->text);
		}else{
			@unlink($imgFile);
		}
		curl_close($ch);
	}

	return $DBsave;
}

function DBsave($DBsave){
	# записываем картинки в основную таблицу
	$sql="INSERT INTO `".PREFIX_SPEC."imgs` SET ";
	foreach ($DBsave as $key => $val) {
		if($key=='link') continue;
		$sqlField[]="`".db::escape($key)."`='".db::escape($val)."'";	
	}
	
	print "\n\nSave:\n";print_r($DBsave);
	
	if(!empty($sqlField)){
		$sqlField[]="`date`=NOW()";
		$sqlField[]="`approve`=1";
		db::query($sql.implode(',', $sqlField),2);
		# пересчитываем количество картинок
		module::exec('posts/admin',array('act'=>'imgRecount','pid'=>$DBsave->pid,'tbl'=>$DBsave->tbl),'data');
	}
}
/*
	преобразования сниппета
*/
function getImageDescription($href,$description){
	if(empty($href)||empty($description)) return false;
	$ch=curl_init($href);
	curl_setopt_array($ch,array(
			CURLOPT_FOLLOWLOCATION=>1,
			CURLOPT_RETURNTRANSFER=>1,
			CURLOPT_HEADER=>0,
			CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
		)
	);
	$site=curl_exec($ch);
	if($site){
		$site=preg_replace('#<br/?>|</?(?:strong|b|i|em|strike|u|a)*?>#i', '', $site);
		$site=strtr($site, array('&#39;'=>'’','&quot;'=>'"','&apos;'=>'\''));
		$site=preg_replace('#[\s ]+#', ' ', $site);
		$description=trim($description, " \t\r\n\0.,;");
		$description=preg_replace('#&middot;.+#', '', $description);
		$description=strtr($description, array('&#39;'=>'’','&quot;'=>'"','&apos;'=>'\''));
		$description=trim(preg_replace('#\s+#', ' ', $description));
		$re=preg_quote($description,'#');

		if(preg_match('#[^>"\']*'.$re.'[^<"\']*#', $site, $f)){
			$description=trim($f[0]);
			$description=mb_substr($description, 0, 255);
		}
	}
	return $description;
} 