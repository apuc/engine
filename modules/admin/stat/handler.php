<?php
namespace admin_stat;
use module,db,url;

class handler{
	function __construct(){
		$this->template='';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
	}
	function index($ref,$uri){#Делаем все обработки для вывода данных
		#Запись статистики
		new stats(SITE,$ref,$uri);
		$image=imagecreate(1,1);
		header('Content-type: image/gif');
		header('Cache-Control: no-store');
		imagegif($image);
		die;
	}
	function statGoogleBots($ua,$uri){
		if(stripos($ua,'Googlebot')){
			#Запись статистики google ботов
			new stats(SITE,'',$uri);
		}
	}
}

#класс отправки статы
class stats{
	function __construct($site,$ref,$uri,$pages=0,$url='http://streoel:19523@brainpat.com/stat/count.php?pass=19523141583'){
		@$ip=isset($_SERVER['HTTP_X_REAL_IP'])?$_SERVER['HTTP_X_REAL_IP']:$_SERVER['REMOTE_ADDR'];
		@$ar=array($ip,time(),$site,urldecode($uri),urldecode($ref),$_SERVER['HTTP_USER_AGENT'],$pages);
		shell_exec($t="nohup wget --post-data='".self::mkPost($ar)."' -O - $url > /dev/null 2>/dev/null & echo $!");
	}
	function mkPost($ar){
		$list=array('ip','time','domain','url','refer','agent','pages');
		foreach($list as $i=>$v){
			$ar[$i]="$v=".urlencode($ar[$i]);
		}
		return implode("&",$ar);
	}
}
