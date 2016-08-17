<?php
namespace images_admin;
use module,db,url;

#сторонние модули
require_once(module::$path.'/images/handler.php');
require_once(module::$path.'/posts/handler.php');

class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->uhandler=module::exec('user',array(),1)->handler;
		$this->user=$this->uhandler->user;
	}
	/*
		Удаление картинки
			- удаляет основную картинку
			- удаляет все thumbnail к картинке
	*/
	function del($imid,$ajax,$withoutRbacCheck=false){
		list($tbl,$pid,$url,$user,$sha1)=db::qrow("SELECT tbl,pid,url,uid,sha1 FROM `".PREFIX_SPEC."imgs` WHERE id='$imid'");
		if($withoutRbacCheck!==true){
			#Проверяем разрешение на удаление картинки
			if(!$rbac=$this->uhandler->rbac('delImage'))
				$rbac=($this->uhandler->rbac('delImageMy')&&$this->user->id==$user);
			if(!$rbac){
				return array('html'=>'permission denied');
			}
		}
		$count_same=db::num_rows(db::query("SELECT `id` FROM `".PREFIX_SPEC."imgs` WHERE `sha1` = '".$sha1."'"));
		if($count_same==1){
			# удаляем основную картинку
			unlink($tFile=module::$path.'/images/files/images/'.$url);
			if(!file_exists($tFile))
				db::query("DELETE FROM `".PREFIX_SPEC."imgs` WHERE `id`='$imid'");
			else
				return (object)array(
					'html'=>'fail, set permissions'
				);
			# удаляем thumbnails
			$thumbDirsArr=scandir($imageDirs=module::$path.'/images/files/');
			foreach($thumbDirsArr as $tdir){
				if(!is_dir($imageDirPath=$imageDirs.$tdir)) continue;
				if(!preg_match('!^images(\d|\_)!',$tdir)&&$tdir!='googleBack') continue;
				@unlink("$imageDirPath/$url");
			}
		}elseif($count_same>1){
			db::query("DELETE FROM `".PREFIX_SPEC."imgs` WHERE `id`='$imid'");
		}
		# пересчитываем количество картинок
		module::exec('posts/admin',array('act'=>'imgRecount','pid'=>$pid,'tbl'=>$tbl),'data');
		return (object)array(
			'html'=>$ajax?module::exec('images/admin',array('act'=>'listofimages','pid'=>$pid,'tbl'=>$tbl),1)->str:'done'
		);	
	}
	/*
		удаляет картинки по ID поста
	*/
	function delByPid($pid,$tbl){
		if(empty($pid)||empty($tbl)) return;
		db::query("SELECT `id`,`uid` FROM `".PREFIX_SPEC."imgs` WHERE `pid`='$pid' && `tbl`='{$tbl}'");
		while($d=db::fetch()){
			$this->del($d->id,false,$withOutRbacCheck=true);
		}
	}
	function install(){
		db::query("CREATE TABLE IF NOT EXISTS `".PREFIX_SPEC."imgs` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `kid` int(11) NOT NULL,
			  `gtitle` varchar(255) NOT NULL,
			  `gtbn` varchar(255) NOT NULL,
			  `text` varchar(255) NOT NULL,
			  `tbl` varchar(50) NOT NULL DEFAULT 'posts',
			  `pid` int(11) NOT NULL,
			  `url` varchar(255) NOT NULL,
			  `sha1` varchar(50) NOT NULL,
			  `uid` int(11) NOT NULL,
			  `date` date NOT NULL,
			  `width` int(11) NOT NULL,
			  `height` int(11) NOT NULL,
			  `filesize` int(11) NOT NULL,
			  `source` varchar(255) NOT NULL,
			  `sourcePage` varchar(255) DEFAULT NULL,
			  `type` varchar(50) NOT NULL,
			  `keyword_type` varchar(255) NOT NULL,
			  `priority` tinyint(4) NOT NULL DEFAULT '0',
			  `approve` int(11) NOT NULL DEFAULT '0',
			  `statViews` int(11) NOT NULL,
			  `statViewsShort` int(11) NOT NULL,
			  `statShortFlag` date NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `uniq` (`tbl`,`pid`,`url`),
			  UNIQUE KEY `tbl_2` (`tbl`,`pid`,`sha1`),
			  KEY `sha1` (`sha1`),
			  KEY `pid` (`pid`),
			  KEY `tbl` (`tbl`),
			  KEY `kid` (`kid`),
			  KEY `gtbn` (`gtbn`),
			  KEY `tbl_priority` (`tbl`,`priority`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",1);
		return;
	}
	/*
	 * модерация картинок
	 * 1. Доступ для пользователей с атрибутом rbac=3 или 1
	 * 
	 * Логика работы
	 * 		- для каждой картинки проставить поле approve -> админ переносит удаленные картинки в таблицу imgsDel
	 * 		- approve = 1 - оставить,  2 - удалить, 0 - не определено
	 * */
	function moderate($filter,$limit,$page,$data){
		$sql_where='';
		if(!$rbac=$this->uhandler->rbac(@$this->user->rbac,'moderImage')){
			if($rbac=$this->uhandler->rbac(@$this->user->rbac,'moderImageMy'))
				$sql_where=" && `uid`='{$this->user->id}'";
		}
		if(!$rbac){
			$this->headers->location='/';
			return;
		}
		# запись в базу из POST данных
		if($data){
			$unapproved=$approved=array();
			foreach($data as $id=>$val){
				if($val==2) $unapproved[]=$id;
				else $approved[]=$id;
			}
			# помечаем unapproved
			if(isset($unapproved[0]))
				db::query("UPDATE `".PREFIX_SPEC."imgs` SET `approve`=2 WHERE `id` IN('".implode("','",$unapproved)."')".$sql_where);
			# помечаем approved	
			if(isset($approved[0]))
				db::query("UPDATE `".PREFIX_SPEC."imgs` SET `approve`=1 WHERE `id` IN('".implode("','",$approved)."')".$sql_where);
		}
		
		$cn=array();
		$imgsCurrent=array();
		$st=isset($filter['status'])?(int)$filter['status']:-1;
		$sql_where_status=($st==-1?' ':" && `approve`='$st' ");
		# получаем картинки с указанным статусом
		db::query(
			"SELECT img.*,keyword.title AS keyword FROM `".PREFIX_SPEC."imgs` img
				LEFT OUTER JOIN `keyword` 
				ON keyword.id=img.kid
			WHERE 1"
			.$sql_where.$sql_where_status
			."LIMIT ".(($page-1)*$limit).",$limit");
		while($d=db::fetch()){
			$imgsCurrent[]=$d;
		}
		
		#получаем количество картинок
		# common count
		list($cn['all'])=db::qrow("SELECT COUNT(`id`) FROM `".PREFIX_SPEC."imgs` WHERE 1".$sql_where.$sql_where_status);
		# not defined
		list($cn[0])=db::qrow("SELECT COUNT(`id`) FROM `".PREFIX_SPEC."imgs` WHERE `approve`='0'".$sql_where);
		# approved
		list($cn[1])=db::qrow("SELECT COUNT(`id`) FROM `".PREFIX_SPEC."imgs` WHERE `approve`='1'".$sql_where);
		# not approved
		list($cn[2])=db::qrow("SELECT COUNT(`id`) FROM `".PREFIX_SPEC."imgs` WHERE `approve`='2'".$sql_where);
		
		return (object)array(
			'count'=>$cn,
			'limit'=>$limit,
			'imgs'=>$imgsCurrent,
			'currentStatus'=>$st,
			'paginator'=>module::exec("paginator",
				array(
					'page'=>$page,
					'num'=>$limit,
					'count'=>$cn['all'],
					'uri'=>url::imagesModerate().'&filter[status]='.$st.'&page=%d'
					)
				,1)->str,
		);
	}
	/*
		вывод
			1. формы загрузки картинок
			2. список загруженных картинок listofimages()
	*/
	function edit($pid,$tbl,$key){
		return (object)array(
			'images'=>module::exec('images/admin',array('act'=>'listofimages','pid'=>$pid,'tbl'=>$tbl),1)->str,
			'pid'=>$pid,
			'tbl'=>$tbl,
			'key'=>$key,
			'limitFileUpload'=>ini_get('upload_max_filesize'),
			'user'=>$this->user->id,
		);
	}
	/*
		список загруженных картинок
			вход: tbl, pid
		запрашивается в edit и для обновления списка, например: после загрузки картинок
	*/
	function listofimages($pid,$tbl,$image_sort){
		if(!empty($image_sort)) {
			foreach($image_sort as $priority=>$image_id) {
				db::query("UPDATE `".PREFIX_SPEC."imgs` SET `priority`='".$priority."'  WHERE `id`='$image_id' && `tbl`='$tbl' && `pid`='$pid'");	
			}
		}
		$images=array();
		# получаем картинки
		db::query(
			"SELECT imgs.*,keyword.title FROM `".PREFIX_SPEC."imgs` imgs 
				LEFT JOIN keyword ON keyword.id=imgs.kid 
			WHERE imgs.`tbl`='$tbl' && imgs.`pid`='$pid' 
			ORDER BY imgs.`priority` DESC");
		while($d=db::fetch()){
			$d->gif=@mime_content_type(module::$path."/images/files/images/".$d->url)=="image/gif";
			$images[]=$d;
		}
		return (object)array('images'=>$images,'pid'=>$pid,'tbl'=>$tbl);
	}
	/*
		Сохранение картинок
	*/
	function save($pid,$title,$tbl,$images,$type){
		$kids=array();
		$error='';
		@mkdir($imgDir=module::$path.'/images/files/images/');
		if($type=='save'){
			/*
				Запуск пакетной загрузки файлов
			*/
			# собираем список файлов загружаемых по ссылке
			if(is_array(@$images['urls'])){
				$user=empty($this->user->id)?0:$this->user->id;
				foreach($images['urls'] as $k=>$url){
					if(!preg_match('!^(?:http|ftp|sftp)!i',$url))
						unset($images['urls'][$k]);
				}
				
				$quantity=count($images['urls']);
				list($startPriority)=db::qrow("SELECT MAX(`priority`) FROM `".PREFIX_SPEC."imgs` WHERE `tbl`='{$tbl}' && `pid`='{$pid}'");
				if(empty($startPriority)) $startPriority=0;

				$i=0;
				foreach($images['urls'] as $k=>$url){
					$url=trim($url);
					$keyword=empty($images['keyword'][$k])?'':$images['keyword'][$k];
					# определение title
					$title_key=stripslashes(empty($keyword)?$title:$keyword);
					
					$kid=createKeyword($title_key,$pid);
					$kids[]=$kid;
					
					$image=getImageName($title_key,$imgDir);
					# основные параметры для сохранения картинок
					$args=(object)array(
						'pid'=>$pid,
						'uid'=>$user,
						'tbl'=>$tbl,
						'url'=>$image,#имя файла картинки
						'link'=>$url,#ссылка на оригинал
						'kid'=>$kid,
						'gtitle'=>empty($images['gtitle'][$k])?'':$images['gtitle'][$k],
						'text'=>empty($images['description'][$k])?'':$images['description'][$k],
						'gtbn'=>empty($images['tbn'][$k])?'':$images['tbn'][$k],
						'sourcePage'=>empty($images['href'][$k])?'':$images['href'][$k],
						'priority'=>$startPriority+$quantity-$i,#устанавливаем сортировку каринки
					);
					$i++;

					$shellparams=base64_encode(serialize($args));
					# запускаем скрипт закачки
					#echo "nohup php ".module::$path."/images/admin/sh/uploadImages.php 'status_{$pid}_{$user}_{$tbl}' '{$shellparams}' >/dev/null 2> /dev/null & echo $!";
					shell_exec("nohup php ".module::$path."/images/admin/sh/uploadImages.php 'status_{$pid}_{$user}_{$tbl}' '{$shellparams}' >/dev/null 2> /dev/null & echo $!");
				}
			}
		}elseif($type=='saveFile'){
			/*
				сохранение файла загруженного с локали
			*/
			# проверяем установлен ли jpegoptim
			preg_match('!jpegoptim\:\s([^\s]+)\s!',shell_exec('whereis jpegoptim'),$m);
			@$jpegoptim=trim($m[1]);
			
			$tImg=$images['files'];
			$user=empty($this->user->id)?0:$this->user->id;
			$keyword=empty($images['keyword'])?'':$images['keyword'];
			$title_key=db::escape(stripslashes(empty($keyword)?$title:$keyword));
			
			$kid=createKeyword($title_key,$pid);
			$kids[]=$kid;

			if(imageExist(sha1_file($tImg),$tbl,$pid)){
				@unlink($tImg);
				$error='imageExist';
			}else{
				//Если картинки с таким хэшем нет в бд -- грузим
				if(!storeDBOnly($tImg,$pid,$tbl,$user,$kid)){
					$image=getImageName($title_key,$imgDir=module::$path.'/images/files/images/');
					if(move_uploaded_file($tImg,$imgFile=$imgDir.$image)){
						if($imgData=getimagesize($imgFile)){
							# оптимизируем jpg если установлен jpegoptim
							if($jpegoptim) shell_exec("{$jpegoptim} --strip-all $imgFile");
							$hash=sha1_file($imgFile);
							db::query("INSERT INTO `".PREFIX_SPEC."imgs` SET
								`tbl`='{$tbl}',
								`pid`='{$pid}',
								`kid`={$kid},
								`url`='".db::escape($image)."',
								`date`=NOW(),
								`uid`='{$user}',
								`sha1`='{$hash}',
								`width`='{$imgData[0]}',
								`height`='{$imgData[1]}',
								`filesize`='".filesize($imgFile)."',
								`approve`=1,
								`source`=''");
							# resize
							foreach(\images\Handler::$resolution as $r){
								module::exec('images',array('act'=>'mkThumb','image'=>$image,'sizeW'=>$r[0],'sizeH'=>$r[1]),1);
							}
							# пересчитываем количество картинок
							module::exec('posts/admin',array('act'=>'imgRecount','pid'=>$pid,'tbl'=>$tbl),'data');
						}else{
							@unlink($imgFile);
							$error='notAnImage';
						}
					}
				}
			}
		}
		return (object)array('kids'=>$kids,'error'=>$error);
	}
	/*
		Обновляет кейворд у загруженных картинок
	*/
	function updateKeyword($pid,$keywords,$texts,$tbl){
		$newkids=array();
		#главный кейворд
		list($mkid)=db::qrow("SELECT `kid` FROM `post` WHERE `id`='{$pid}' LIMIT 1");
		$keyword_str=array();
		foreach($keywords as $title){
			if($title=='') continue;
			$keyword_str[db::escape($title)]=1;
		}
		$kids=array();
		db::query("SELECT id,title FROM keyword WHERE title IN ('".implode("','",array_keys($keyword_str))."')");
		while($d=db::fetch()){
			$kids[$d->title]=$d->id;
		}
		#вместо пустого кейворда назначается главный кейворд
		$kids['']=$mkid;
		foreach($keywords as $id=>$title){
			if(isset($kids[$title])){
				$kid=$kids[$title];
			}else{
				db::query("INSERT INTO keyword (title) VALUES ('".db::escape($title)."')");
				$kid=(int)db::insert();
				$newkids[]=$kid;
			}
			if(!$kid){
				continue;
			}
			db::query("UPDATE `".PREFIX_SPEC."imgs` SET `kid`={$kid},`text`='".db::escape($texts[$id])."'
				WHERE `id`='{$id}' && `tbl`='{$tbl}' && `pid`='{$pid}' LIMIT 1");
		}
		return (object)array('newkids'=>$newkids);
	}
	/*
		Количество работающих потоков пакетной закачки картинок
	*/
	function ImageLoadStatus($tbl,$pid,$uid){
		if(empty($tbl)||empty($pid)||empty($uid)) die;
		$out=shell_exec("ps aux|grep \".*uploadImages.php status_{$pid}_{$uid}_{$tbl}\"");
		return (object)array('count'=>count(explode("\n", trim($out)))-2);
	}
	/*
		Парсинг картинок с гугла
	*/
	function searchRequest($keyword, $nocheck=false){
		if(!$keyword) return;
		
		$keyword = trim($keyword);
		if (!$nocheck) {
			list($c)=db::qrow("SELECT COUNT(*) FROM `".PREFIX_SPEC."imgs` imgs WHERE kid = (SELECT id FROM keyword WHERE title = '".db::escape($keyword)."')");
			if (@$c>0) {
				echo '"exists"';
				exit;
			}
		}

		$count=30;
		$options=array('http' => array('user_agent' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/29.0.1547.65 Chrome/29.0.1547.65 Safari/537.36'));
		$context=stream_context_create($options);
		$urls = array();
		foreach (explode("\n", $keyword) as $line) {
			$kw=trim($line);
			if(!$kw) continue;
			$r=file_get_contents('http://www.google.com/search?q='.urlencode(preg_replace('/[\s]+/', '+', $kw)).'&hl=en&tbm=isch', false, $context);
			#preg_match_all('!/imgres\?imgurl=([^\&]+)[^\s]*imgrefurl=([^\&]+).+?<div class="rg_meta">(.+?)</div>!is', $r, $m);
			preg_match_all('!<div class="rg_meta">(.+?)</div>!is', $r, $m);
			if(empty($m[1])) continue;

			$i=0;
			foreach($m[1] as $k=>$jsonEnc) {
				$obj = json_decode($jsonEnc);
				if(is_object($obj)){
					#получаем google thumbnail ID
					$tu=preg_replace('!^.+?tbn\:(.+?)$!si','$1',$obj->tu);
				}else{
					continue;
				}
				if($i++>=$count) break;
				
				#исключаем wikipedia
				if(!preg_match('/wikipedia\.[a-z]{2,3}/i',$obj->ru)) 
					$urls[]=array($obj->ru, $kw, $obj->s, $obj->ou, $tu, $obj->pt);
			}
		}
		return (object)array('json'=>json_encode($urls));
	}
	/*
		обрезает изображение
	*/
	function crop($url,$playicon,$scale,$x1,$y1,$width,$height){
		if(strpos($url,'/')!==false||strpos($playicon,'/')!==false){
			return false;
		}
		$status=false;
		$path=module::$path.'/images/files/images/'.$url;
		if($width){
			list(,,$t,)=getimagesize($path);
			if($t==IMAGETYPE_GIF)
				$src=imagecreatefromgif($path);
			elseif($t==IMAGETYPE_JPEG)
				$src=imagecreatefromjpeg($path);
			elseif($t==IMAGETYPE_PNG)
				$src=imagecreatefrompng($path);
			$width/=$scale;
			$height/=$scale;
			$dest=imagecreatetruecolor($width,$height);
			imagecopyresampled($dest,$src,0,0,$x1/$scale,$y1/$scale,$width,$height,$width,$height);
			$status=imagejpeg($dest,$path,80);
		}
		if(!empty($playicon)){
			$playicon_path=module::$path.'/images/files/images/'.$playicon;
			if(file_exists($playicon_path)){
				$status=rename($playicon_path,$path);
			}else{
				$playicon=null;
			}
		}
		if($status){
			shell_exec('rm '.module::$path.'/images/files/images*_*/'.$url);
			db::query("UPDATE `".PREFIX_SPEC."imgs` SET `sha1`='".sha1_file($path)."' WHERE `url`='".db::escape($url)."' LIMIT 1");
		}
		return;
	}
	/*
		накладывание play icon на изображение
	*/
	function playicon($url,$scale,$x1,$y1,$width,$height,$drop){
		if(strpos($url,'/')!==false){
			return false;
		}
		$path=module::$path.'/images/files/images/'.$url;
		if($drop){
			@unlink($path);
		}else{
			$playiconfile='playicon_'.$url;
			$temp=microtime(true).'_'.$url;
			copy($path, $pathtmp=module::$path.'/images/files/images/'.$temp);
			module::exec('images/admin',array('act'=>'crop','url'=>$temp,'playicon'=>'','scale'=>$scale,'x1'=>$x1,'y1'=>$y1,'width'=>$width,'height'=>$height),1);
			list(,,$t,)=getimagesize($pathtmp);
			if($t==IMAGETYPE_GIF)
				$src=imagecreatefromgif($pathtmp);
			elseif($t==IMAGETYPE_JPEG)
				$src=imagecreatefromjpeg($pathtmp);
			elseif($t==IMAGETYPE_PNG)
				$src=imagecreatefrompng($pathtmp);
			$dest=imagecreatetruecolor(470,246);
			imagecopyresampled($dest,$src,0,0,0,0,470,246,imagesx($src),imagesy($src));
			$play=imagecreatefrompng(module::$path.'/images/tpl/files/icons/play.png');
			imagecopy($dest,$play,199,87,0,0,72,72);
			imagejpeg($dest,module::$path.'/images/files/images/'.$playiconfile,80);
			unlink($pathtmp);
			echo $playiconfile;
		}
		exit;
	}
	/*
		возвращает ошибки состояния модуля
	*/
	function status(){
		$dirs=array(PATH.'modules/images/files',PATH.'modules/images/files/googleBack',PATH.'modules/images/files/images');
		foreach ($dirs as $dir) {
			if(!file_exists($dir)){@mkdir($dir);@chmod($dir,0777);}
			if(!$writable=is_writable($dir)) break;
		}
		return (object)array('dir'=>$dir,'imgsWritable'=>$writable);
	}
}

/*
 * Функции для сохранения картинок
 * */
function getImageName($title,$imgDir){# Создаем название картинки
	static $lastTitle;
	static $i;
	if(!isset($i)||@$lastTitle!=$title) $i=0;
	$lastTitle=$title;
	$title=preg_replace("![^\w\d\s]!u",'',mb_strtolower($title,'UTF8'));
	$title=preg_replace("!\s+!",'-',$title);
	$i++; $file="$title-$i";
	while(file_exists($imgDir."$file.jpg")){
		$i++; $file="$title-$i";
	}
	return "$file.jpg";
}
/*
	Проверка существования картинки в базе по хэшу
*/
function imageExist($hash,$tbl,$pid){
	list($id)=db::qrow("SELECT `id` FROM `".PREFIX_SPEC."imgs` WHERE `tbl`='{$tbl}' && `pid`='{$pid}' && `sha1`='{$hash}'");
	return (bool)$id;
}
/*
 *  Проверка на существование картинки в бд, если есть - не грузим заново, а делаем запись с имеющейся уже
 */
function storeDBOnly($url,$pid,$tbl,$user,$kid,$skip=false){
	$isset = db::qfetch("SELECT * FROM `".PREFIX_SPEC."imgs` WHERE `sha1`='".sha1_file($url)."'");
	if(!empty($isset->id)){
		if(!$skip){
			unset($isset->id);
			$isset->pid=$pid;
			$isset->tbl=$tbl;
			$isset->uid=$user;
			$isset->date=date("Y-m-d");
			$isset->kid=$kid;
			foreach($isset as $k=>$v) {
				$fields[]="`".$k."`='".$v."'";
			}
			db::query("INSERT INTO `".PREFIX_SPEC."imgs` SET ".implode(", ", $fields));
		}
		return true;
	}else
		return false;
}
/*
	проверяет права на запись в каталоги для картинок
*/
function dirWritable($dir){
	if(!is_writable($dir)){
		return false;
	}else{
		foreach(scandir($dir) as $file){
			if($file{0}=='.'||!is_dir($dir.$file)){
				continue;
			}
			if(!is_writable($dir.$file)){
				return false;
			}
		}
	}
	return true;
}
/*
	создает новый кейворд
*/
function createKeyword($title,$pid){
	$kid=(int)@db::qfetch("SELECT id FROM keyword WHERE title = '".$title."'")->id;
	if(!$kid){
		db::query("INSERT INTO keyword (title) VALUES ('".$title."')");
		$kid=(int)db::insert();
	}
	return $kid;
}
