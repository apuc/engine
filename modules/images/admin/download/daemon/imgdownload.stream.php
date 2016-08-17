#!/usr/bin/php
<?php
define('SITEOFF',1);#отключаем все кроме инициализации движка

include_once __DIR__.'/../../../../../index.php';#подключаем движок
include_once __DIR__.'/func.php';
set_time_limit(60);
error_reporting(-1); ini_set('display_errors', 'On');

if(empty($argv[1]))
	die("error: no params\n");
if(!is_object($params=unserialize(base64_decode($argv[1]))))
	die("error: wrong params\n");

$urlData=parse_url($params->image->imglink);
$ch=curl_init($params->image->imglink);
curl_setopt_array($ch,array(
		CURLOPT_REFERER=>!empty($params->image->ref)?urldecode($params->image->ref):$urlData['scheme'].'://'.$urlData['host'].'/',
		CURLOPT_FOLLOWLOCATION=>1,
		CURLOPT_RETURNTRANSFER=>1,
		CURLOPT_HEADER=>0,
		CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1944.0 Safari/537.36',
		CURLOPT_CONNECTTIMEOUT=>10,
	)
);

save($params,curl_exec($ch));
curl_close($ch);

function save($params,$result){
	$log='';
	@$img=$params->image;
	if($result){
		file_put_contents($img->dest,$result);
		$log.="$img->url server OK -> ";
	}else
		$log.="$img->url server fail -> ";
	if(!@$imgData=getimagesize($img->dest)) {
		@unlink($img->dest);
		$log.="not an image";
	}else{
		$img->sha1=sha1_file($img->dest);
		#проверка: существует для данного поста
		list($exists)=db::qrow("SELECT id FROM `".PREFIX_SPEC."imgs` WHERE `sha1`='{$img->sha1}' && `tbl`='post' && `pid`='{$params->pid}' LIMIT 1");
		if(!empty($exists)){
			unlink($img->dest);
			$log.="skip exist: pid {$params->pid}, {$img->url}, {$img->sha1}";
		}else{
			#подключаем модуль images/admin
			include_once PATH.'modules/images/admin/handler.php';
			#проверка: существует для других поста, копирует данные (если разрешено $params->skipExists)
			$skipCopy=!empty($params->skipExists);
			$copyImage=\images_admin\storeDBOnly($img->dest,$params->pid,'post',$params->uid,$params->kid,$skipCopy);
			if(!$copyImage){
				#заменяем параметры (размеры) полученные от google реальными
				$img->ow=$imgData[0];
				$img->oh=$imgData[1];
				#получаем google thumbnail ID
				$img->tu=isset($img->tu)?preg_replace('!^.+?tbn\:(.+?)$!si','$1',$img->tu):'';
				#отфильтровываем если не соответствует изначально заданным параметрам
				if(sizeFilter($params->manimsize,$img)){
					# resize options
					$moduleImages=new \images\Handler;
					foreach($moduleImages::$resolution as $r){
						$moduleImages->mkThumb($img->url,$r[0],$r[1],1);
					}
					db::query("INSERT INTO `".PREFIX_SPEC."imgs` 
					(`kid`,`gtitle`,`gtbn`,`text`,`tbl`,`pid`,`url`,`sha1`,`uid`,`date`,`width`,`height`,`filesize`,`source`,`sourcePage`,`type`,`approve`) 
					VALUES (
						'{$params->kid}',
						'".db::escape($img->pt)."',
						'".db::escape($img->tu)."',
						'".db::escape($img->s)."',
						'post',
						'{$params->pid}',
						'{$img->url}',
						'{$img->sha1}',
						'{$params->uid}',
						NOW(),
						'{$imgData[0]}',
						'{$imgData[1]}',
						'".filesize($img->dest)."',
						'".db::escape($img->imglink)."',
						'".db::escape($img->ref)."',
						'".($params->allowgallery?'':'1')."',
						'0'
					)");
					if(db::insert())
						$log.="insert to DB: OK";
					else
						$log.="insert fail: pid {$params->pid} error: ".db::error();
				}else{
					$log.="filter applied: pid {$params->pid} real size: {$img->ow}x{$img->oh}";
					unlink($img->dest);
				}
			}else{
				if(!$skipCopy)
					$log.="copied: pid {$params->pid}, {$img->sha1}";
				else
					$log.="forced skip exists: pid {$params->pid}, {$img->sha1}";
				unlink($img->dest);
			}
		}
	}
	echo "$log\n";
}
