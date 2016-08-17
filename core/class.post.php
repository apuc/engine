<?
if(!defined($var='PREFIX_CON'))DEFINE($var,'zcon_');
class post{#класс работы с постами
	public $ser;
	function __construct($tbl){
		
		$this->tbl=$tbl;
	}
	function getById($pid){#Пост по id
		return db::qfetch("SELECT * FROM `$this->tbl` WHERE id=$pid LIMIT 1");
	}
	function getByUrl($url){#Пост по заголовку
		$post=db::qfetch("SELECT * FROM `$this->tbl` WHERE url='".db::escape($url)."' LIMIT 1");
	}
	function &rand($count){#Получить список случайных постов
		list($maxId)=db::qrow("SELECT MAX(id) FROM `$this->tbl`");
		$ps=array();
		$q=db::query("SELECT * FROM `$this->tbl` WHERE id>".mt_rand(0,$maxId-$count)." limit $count");
		while($d=db::fetch($q)){
			$ps[$d->id]=$d;
		}
		return $ps;
	}
	/*
	примеры $uparams:
	1) tag1|v1			Список постов с tag1.id=v1
	2) tag1|v1|title		Список постов с tag1.title=v1
	3) tag1|v1,v2,v3,v4		список постов по tag1 с id=v1,v2,v3,v4
	4) tag2/			полный список постов с тегом tag2
	5) tag1|{asc|desc}/tag2|v2	список постов с тегом tag1 и tag2.id==v2 отсортированный по tag1 {ASC|DESC}
	*/
	function getPosts($uparams='',$count=20,$page=1){#Получить посты по разным параметрам
		if($page<1)$page=1;
		$params=$this->mkParams($uparams);#Сформировать массив параметров
		if($params){
			$sql=$this->mkSql($params);#Формируем sql запрос из нескольких индексных таблиц
			$q=db::query("$sql LIMIT ".(($page-1)*$count).",$count");
			$pid=$this->tbl;
			list($num)=db::qrow('SELECT FOUND_ROWS()');
			$ar=array();while($d=db::fetch($q)){$ar[$d->$pid]=1;}
			$start=0;
		}else{
			$start=($page-1)*$count;
			$ar=1;
			list($num)=db::qrow("SELECT COUNT(*) FROM `$this->tbl`");
		}
		return array($this->postsByAr($ar,$num,$start),$num);
	}
	function &postsByAr($ar=1,$count=-1,$start=0,$pagen=20){#Получить список постов по массиву array(pid1=>1,pid2=>1,...)
		if($count<0){
			if($ar==1)$count=$pagen;
			else $count=count($ar);
		}
		if(is_array($ar)&&!empty($ar)){
			$where="WHERE id in (".implode(",",array_keys($ar)).")";
		}elseif(empty($ar)){
			$where='id=0';$ar=array();
		}else{
			$where='';$ar=array();
		}
		$unset=$ar;
		$q=db::query("SELECT * FROM `$this->tbl` $where limit $start,$count");
		$ps=array();
		while($d=db::fetch($q)){
			$ar[$d->id]=$d;
			unset($unset[$d->id]);
		}
		#Удаляем не найдденные посты
		foreach($unset as $id=>$v){
			unset($ar[$id]);
		}
		return $ar;
	}
	private function &mkParams($params){#Преобразовать параметры из строки(url) в массив входные параметры такие же как для getPosts
		$e=explode("/",$params);
		$res=array();
		foreach($e as $v){
			$v=trim($v);if(!$v)continue;
			@list($tbl,$par,$type)=explode("|",$v);
			$id=array();$order="";
			if(in_array($par,array('asc','desc')))
				$order=$par;
			elseif($par){
				$par=explode(',',$par);
				foreach($par as $i=>$p){$par[$i]=db::escape($p);}
				$id=array();
				if($type=='title'){
					db::query("SELECT id FROM `$tbl` WHERE `url` in ('".implode("','",$par)."')");
					while($d=db::fetch()){$id[]=$d->id;}
					if(!$id)$id[]=0;
				}else{
					foreach($par as $p){$id[]=$p;}
				}
			}else
				$order='asc';
			$res[db::escape($tbl)]=(object)array('id'=>$id,'order'=>$order);
		}
		return $res;
	}
	#формирует sql запрос для получения списка id постов из индексных таблиц
	#входные параметры такие же как для getPosts
	private function mkSql($params){
		$order=$where=$tables=array();$last='';
		foreach($params as $p=>$v){
			if($v->id){#если указан id
				$where[]="`$p` IN ('".implode("','",$v->id)."')";
			}elseif($v->order){#если указан order
				$order[]="`$p` $v->order";
			}
			$tbl=$this->tbl;
			$tblCon=PREFIX_CON.(@$this->tblcon[$p]?:"{$this->tbl}2$p");
			if(count($tables)){
				$tables[]=" INNER JOIN `$tblCon` on `$tblCon`.`$tbl`=$last.`$tbl`";
			}else{
				$last=$tblCon;
				$tables[]="`$tblCon`";
			}
		}
		$firstTbl=current($tables);
		$where=$where?"WHERE ".implode(" && ",$where):"";
		$order=$order?"order by ".implode(",",$order):"order by $tbl";
		return "SELECT SQL_CALC_FOUND_ROWS distinct $firstTbl.$tbl FROM ".implode($tables)." $where $order";
	}

}
