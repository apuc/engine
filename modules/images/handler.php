<?php
namespace images;
use module,db,url;

/*
 * Должен возвращать:
 * $this->data - объект переменных для вывода в шаблоне
 * $this->headers - объект для изменения заголовков при отдаче
 * $this->act - если есть несколько вариантов ответов
 */
class handler{
	static $resolution=array(
			array(250,0),
		);
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	/*
		Генерация превью для картинки в гугле
	*/
	function genImg($img,$dir){
		if(!is_dir($storeDir=PATH."modules/images/files/googleBack")){mkdir($storeDir);chmod($storeDir,0777);}
		$filename="$storeDir/$img";
		set_time_limit(10);
		ini_set('memory_limit','1024M');
		list($pid,$tbl)=db::qrow("SELECT pid,tbl FROM `".PREFIX_SPEC."imgs` WHERE `url`='{$img}' LIMIT 1");
		list($title)=db::qrow("SELECT `title` FROM `{$tbl}` WHERE id='{$pid}'");
		list($otherimgs)=db::qrow("SELECT GROUP_CONCAT(url) FROM `".PREFIX_SPEC."imgs` WHERE tbl='{$tbl}' && pid='{$pid}' && url!='{$img}'");
		ob_start();
		if(makeBackground($img,$dir,$title,$otherimgs)!==false){
			$str=ob_get_contents();
			file_put_contents($filename,$str); @chmod($filename,0666);
		}
		ob_end_flush();
		die;
	}
	function hotlink($img){
		$this->template='';
		list($tbl,$pid)=db::qrow("SELECT `tbl`,`pid` FROM `".PREFIX_SPEC."imgs` WHERE `url`='$img' LIMIT 1");
		$this->headers->location=url::img($tbl,$pid,$img);
		return;
	}
	function mkThumb($img,$sizeW,$sizeH,$easy){
		# проверяем установлен ли jpegoptim
		static $jpegoptim;
		if(empty($jpegoptim)){
			preg_match('!jpegoptim\:\s([^\s]+)\s!',shell_exec('whereis jpegoptim'),$m);
			@$jpegoptim=trim($m[1]);
		}
		
		if(!$sizeW&&!$sizeH) return;
		$imgDir=PATH.'modules/images/files/images';
		if(!is_dir($thPath=$imgDir.((!$sizeW)?'':$sizeW).'_'.((!$sizeH)?'':$sizeH)))
			@mkdir($thPath);
		if(file_exists($src="$imgDir/$img")){
			$outOnOff=$easy?0:1;
			resize($src,$dest=$thPath."/$img",$sizeW,$sizeH,$outOnOff);
			# оптимизируем jpg если установлен jpegoptim
			if($jpegoptim&&file_exists($dest)) 
				shell_exec("{$jpegoptim} --strip-all $dest");
			if($outOnOff) die;
		}else{
			header("Content-type: image/jpeg");
			#очищаем предыдущий вывод, на всякий случай
			ob_end_clean();
			die(file_get_contents(PATH.'modules/images/files/404.png'));
		}
		return;
	}
	function download($file){
		$file=PATH."modules/images/files/images/".basename($file);
		if($file=="" or !file_exists($file)){location(HREF);};
		downloadHeaders($file);
		
		header("Content-Length: ".filesize($file));
		readfile($file);
		die;
	}
	function downloadResize($file,$x,$y){
		$file=PATH."modules/images/files/images/".basename($file);
		if($file||$x||$y){
			downloadHeaders($file);
			resize($file,false,$x,$y,1);
		}
		die;
	}
}
function downloadHeaders($filename){
	// required for IE, otherwise Content-disposition is ignored
	if(ini_get('zlib.output_compression'))ini_set('zlib.output_compression', 'Off');
	$file_extension=strtolower(substr(strrchr($filename,"."),1));
	switch( $file_extension ){
		case "pdf": $ctype="application/pdf"; break;
		case "exe": $ctype="application/octet-stream"; break;
		case "zip": $ctype="application/zip"; break;
		case "doc": $ctype="application/msword"; break;
		case "xls": $ctype="application/vnd.ms-excel"; break;
		case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; break;
		default: $ctype="application/force-download";
	}
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers 
	header("Content-Type: $ctype");
	// change, added quotes to allow spaces in filenames, by Rajkumar Singh
	header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
	header("Content-Transfer-Encoding: binary");
}
/* 
	генерирует preview изображение
	используется imagick
*/
function makeBackground($img,$dir,$text,$otherimgs){
	$image=new \Imagick(realpath($dir.$img));
	$image->stripImage();
	$w=$image->getImageWidth();
	$h=$image->getImageHeight();
	if($w<300||$h<300) return false;
	#вычисляем размер текста
	$text=mb_substr($text, 0, 40);
	$fontSize=(int)($w*0.025);
	$imageTextTop=textWithGradient($text,$fontSize,'right');
	$imageTextBottom=textWithGradient(SITE,$fontSize*1.3,'left');

	#накладываем на основное изображение
	$image->compositeImage($imageTextTop,\Imagick::COMPOSITE_DEFAULT,0,0);
	$image->compositeImage($imageTextBottom,\Imagick::COMPOSITE_DEFAULT,$w-$imageTextBottom->getImageWidth(),$h-$imageTextBottom->getImageHeight());
	#делаем превью других изображений
	if(!empty($otherimgs))
		$imgs=explode(',', $otherimgs);
	if(isset($imgs[0])){
		$rotate[]=17;
		$rotate[]=-10;
		$rotate[]=16;
		$countBox=new \Imagick();
		$countBox->newImage($bw=$w*0.09, $bh=$bw*0.6, 'white');
		$countBox->setImageFormat('png');
		$countBox->borderImage('grey',1,1);
		$draw=new \ImagickDraw();
		$draw->setFillColor('red');
		$draw->setFont("Bookman-DemiItalic");
		$draw->setFontSize($bfs=(int)($w*0.018));
		$draw->setGravity(\Imagick::GRAVITY_NORTH);
		$draw->setStrokeColor('black');
		$draw->setStrokeOpacity(0.5);
		$countBox->annotateImage($draw, 0, $bh-$bh*0.8, 0, count($imgs));
		$draw->setFillColor('#A9A9A9');
		$draw->setFontSize($bfs*0.8);
		$draw->setGravity(\Imagick::GRAVITY_SOUTH);
		$countBox->annotateImage($draw, 0, $bh-$bh*0.8, 0, 'pictures');
		$oiw=$w*0.13;#ширина превью картинки
		$oih=$oiw*0.75;#высота превью картинки
		$ph=$h-$h*0.98;#положение блока превью по оси Y
		$image->compositeImage($countBox,\Imagick::COMPOSITE_DEFAULT,$w-$bw*1.77,$ph+$oih*1.16);
		foreach ($imgs as $i=>$im) {
			if(!$imgpath=realpath($dir.$im)) continue;
			$timg=new \Imagick($imgpath);
			$timg->resizeImage($oiw=$w*0.13,0,\Imagick::FILTER_GAUSSIAN,1);
			$timg->cropThumbnailImage($oiw,$oih);
			$timg->borderImage('white',5,5);
			$timg->borderImage('grey',1,1);
			$timg->setImageFormat('png');
			$timg->rotateImage('none',$rotate[$i]);
			$otherIm[$i]=$timg;
			if($i>=2) break;
		}
		if(isset($otherIm[0]))
			$image->compositeImage($otherIm[0],\Imagick::COMPOSITE_DEFAULT,$w-$oiw*2.25,$ph+$oih*0.7);
		if(isset($otherIm[1]))
			$image->compositeImage($otherIm[1],\Imagick::COMPOSITE_DEFAULT,$w-$oiw*2.1,$ph);
		if(isset($otherIm[2]))
			$image->compositeImage($otherIm[2],\Imagick::COMPOSITE_DEFAULT,$w-$oiw*1.3,$ph+$oih*0.08);
	}
	header('Content-type: image/'.$image->getImageFormat());
	header('Cache-Control: no-store');
	echo $image;
}
function textWithGradient($text,$fontSize,$gradientPos='right'){
	$imageTextBackg=new \Imagick();
	$draw=new \ImagickDraw();
	$draw->setFillColor('white');
	$draw->setFont("Bookman-DemiItalic");
	$draw->setFontSize($fontSize);
	$draw->setGravity(\Imagick::GRAVITY_WEST);
	$gradientW=60;
	$textMetrics=$imageTextBackg->queryFontMetrics($draw,$text);
	#вычисляем размер фона для текста
	$textBackgrndW=$textMetrics['textWidth']+$gradientW*1.2;
	$textBackgrndH=$fontSize*1.8;
	#создаем прозрачный холст
	$imageTextBackg->newImage($textBackgrndW, $textBackgrndH, "none");
	$imageTextBackg->setImageFormat('png');
	#создаем фон для текста
	$background=new \Imagick();
	$background->newImage($textBackgrndW-$gradientW, $textBackgrndH, "black");
	$background->setImageFormat('png');
	#пишем текст
	$background->annotateImage($draw, 10, 0, 0, $text);
	if($gradientPos=='right')
		$x=0;
	elseif($gradientPos=='left'){
		$x=$gradientW;
	}
	$imageTextBackg->compositeImage($background,\Imagick::COMPOSITE_DEFAULT,$x,0);
	#создаем полоску с градиентом
	$gradient=new \Imagick();
	if($gradientPos=='right')
		$g="gradient:black-none";
	elseif($gradientPos=='left'){
		$g="gradient:none-black";
	}
	$gradient->newPseudoImage($textBackgrndH, $gradientW, $g);
	$gradient->setImageFormat('png');
	$gradient->rotateImage(new \ImagickPixel(),270);
	#накладываем все на прозрачный холст
	if($gradientPos=='right')
		$x=$textBackgrndW-$gradientW;
	elseif($gradientPos=='left'){
		$x=0;
	}
	$imageTextBackg->compositeImage($gradient,\Imagick::COMPOSITE_DEFAULT,$x,0);
	return $imageTextBackg;
}

function resize($src,$dest,$xx,$yy,$outOnOff=1){
	ini_set('memory_limit','1G');
	list($x, $y, $t, $attr) = getimagesize($src);
	if($x){
		if ($t == IMAGETYPE_GIF)
			$src=imagecreatefromgif($src);
		else if ($t == IMAGETYPE_JPEG)
			$src=imagecreatefromjpeg($src);
		else if ($t == IMAGETYPE_PNG)
			$src=imagecreatefrompng($src);
		
		if($src){
			$dst_x=0;
			$dst_y=0;
			$autoSide=0;# (bool) - подгонять сторону картинки под размеры "черными полосами"
			if(!$xx) {$xx=floor($x/($y/$yy)); $autoSide=1;}
			if(!$yy) {$yy=floor($y/($x/$xx)); $autoSide=1;}
			$dst=imagecreatetruecolor($xx,$yy);
			
			if(!$autoSide){
				if(($x/$xx)>($y/$yy)){
					$diff=$x/$xx;# разница картинок по высоте, при сохранении соотношения сторон
					$y_real=floor($y/$diff);
					$dst_y=($yy-$y_real)/2;# смещение по ОY
					$yy=$y_real;
				}else{
					$diff=$y/$yy;# разница картинок по ширине, при сохранении соотношения сторон
					$x_real=floor($x/$diff);
					$dst_x=($xx-$x_real)/2;# смещение по ОХ
					$xx=$x_real;
				}
			}
			# копирует прямоугольную часть одного изображения на другое изображение
			$res = imagecopyresampled($dst,$src,$dst_x,$dst_y,0,0,$xx,$yy,$x,$y);
			imagedestroy($src);
			@ob_end_clean();
			ob_start();
			imagejpeg($dst);
			if($dest!==false)
				@file_put_contents($dest,ob_get_contents());
			if($outOnOff){
				header("Content-type: image/jpeg");
				ob_end_flush();
			}else
				ob_end_clean();
			imagedestroy($dst);
		}
	}
}
