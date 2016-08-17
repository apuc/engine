<?
class db{
	var $db_id= false;
	var $query_num=0;
	var $query_id=NULL;
	var $error=FALSE; 
	var $error_num=NULL;
	var $last_query=NULL;
	var $errors=0;
	var $save=0; #Управляет сохранением все запросы и скорость их выполнения
	var $queryList=''; # Дапнные по всем запросам
	function __construct($host='',$user='',$pas='',$bd='') {
		global $db;
		$db=$this;
		$db->db_id=@mysql_connect($host,$user,$pas,true) or die('db connect error');
		mysql_select_db($bd,$db->db_id) or die("db: '$bd' select error");
		$db->query("SET NAMES utf8");
		register_shutdown_function(array($db,'close'));
		if($this->save)register_shutdown_function(array($db,'show'));
	}
	/*
	 * show:
	 * 	true/1  - показывать ошибки
	 *  false/0 - ничего не показывать
	 *  2		- показывать запрос и ошибки
	 */ 
	static function query($query,$show=false){
		global $db;
		#print "$query<br>";
		if($db->save or in_array($show,array(1,2))){
			$start=microtime(2);
		}
		$db->last_query=$query;
		if(!($db->query_id=mysql_query($query, $db->db_id))){
			$db->errors=1;
			$db->error=mysql_error();
			#if($er=mysql_error()){echo "\n<br />$query :\n<br />$er\n<br />".debug_print_backtrace()."\n<br />";die;}
			$db->error_num=mysql_errno();
			if($show){
				$db->display_error($db->error, $db->error_num, $query);
				$num=10000;
				print "\n<h1>query time: ".(round((microtime(2)-$start)*$num)/$num)."s</h1>\n";
			}
		}else{
			$db->error_num=$db->errors=0;
			$db->error='';
		}
		if($show===2){
			self::display_query($query);
			$num=10000;
			print "\n<h1>query time: ".(round((microtime(2)-$start)*$num)/$num)."s</h1>\n";
		}
		if($db->save){
			$num=10000;
			$db->queryList[]=array($query,round((microtime(2)-$start)*$num)/$num);
		}
		$db->query_num ++;
		return $db->query_id;
	}
	static function &qall($query,$show=-1){
		global $db;
		if($show==-1)$db->query($query);
		else $db->query($query,$show);
		$ar=array();
		while($d=$db->fetch()){
			$ar[]=$d;
		}
		return $ar;
	}
	static function &qrowall($query,$show=-1){
		global $db;
		if($show==-1)$db->query($query);
		else $db->query($query,$show);
		$ar=array();
		while($d=$db->fetchRow()){
			$ar[]=$d;
		}
		return $ar;
	}
	static function qrow($query,$show=-1){
		global $db;
		if($show==-1)$db->query($query);
		else $db->query($query,$show);
		return @mysql_fetch_row($db->query_id);
	}
	static function qfetch($query,$show=-1){
		global $db;
		if($show==-1)$db->query($query);
		else $db->query($query,$show);
		return @mysql_fetch_object($db->query_id);
	}
	static function fetch($query_id = ''){
		global $db;
		if ($query_id == '') $query_id = $db->query_id;
		return @mysql_fetch_object($query_id);
	}
	static function fetchRow($query_id = ''){
		global $db;
		if ($query_id == '') $query_id = $db->query_id;
		list($row)=@mysql_fetch_row($query_id);
		return $row;
	}
	static function num_rows($query_id = ''){
		global $db;
		if ($query_id == '') $query_id = $db->query_id;
		return @mysql_num_rows($query_id);
	}
	static function affected(){
		global $db;
		return @mysql_affected_rows($db->db_id);
	}
	static function insert(){
		global $db;
		return @mysql_insert_id($db->db_id);
	}
	static function getTbls(){#Получаем список всех таблиц из БД
		$tbls=array();
		db::query("SHOW TABLES");
		while($r=db::fetch_row()){
			$tbls[$r[0]]=1;
		}
		return $tbls;
	}
	static function fields($query_id = ''){
		global $db;
		if ($query_id == '') $query_id = $db->query_id;
		while ($field = @mysql_fetch_field($query_id)){
			$fields[] = $field;
		}
		return $fields;
	}
	function makeSQL($ar){
		foreach($ar as $k=>$v){
			$str[]="`$k`='$v'";
		}
		return implode(",",$str);
	}
	static function ping(){
		global $db;
		return mysql_ping($db->db_id);
	}
	static function escape($source){
		global $db;
		if ($db->db_id) return mysql_real_escape_string ($source, $db->db_id);
		else return mysql_escape_string($source);
	}
	static function close(){
		global $db;
		@mysql_close($db->db_id);
	}
	static function show(){
		global $db;
		print "<table>";
		foreach($db->queryList as $q){
			@$sum+=$q[1];
			?>
			<tr>
				<td><?$db->display_query($q[0]);?></td>
				<td>used time:<?=$q[1]?></td>
			</tr>
		<?}
		print "</table><h1>total used time:$sum</h1>";
		print timer(3);
		print "<pre>";print_r($db);
		exit;
	}
	static function error(){
		global $db;
		return $db->error;
	}
	function display_error($error, $error_num, $query = ''){
		echo '<html>
		<head><title>MySQL Fatal Error</title>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<style type="text/css">
		body {font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 10px;font-style: normal;color: #000000;}
		</style>
		</head>
		<body>
			<font size="4">MySQL Error!</font><br />------------------------<br /><br />
			<u>The Error returned was:</u><br /><strong>'.$error.'</strong>
			<br /><br /></strong><u>Error Number:</u><br /><strong>'.$error_num.'</strong><br /><br />
			'.self::display_query($query).'
		</body></html>';
		#exit();
	}
	static function display_query($query){?>
		<textarea name="" rows="10" cols="52" wrap="virtual"><?=$query?></textarea><br />
	<?}
	static function save(){
		global $db;
		$db->save=1;
	}
}
