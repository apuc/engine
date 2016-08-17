<?
/*
	строит структуру категорий и постов в виде массива на основе директорий и файлов
*/
function searchStruct($path,&$struct=array()){
	$base=scandir($path);
	foreach ($base as $dir) {
		if($dir=='.'||$dir=='..') continue;
		if(is_dir($newpath="$path/$dir")){
			$struct[$dir]=array();
			searchStruct($newpath,$struct[$dir]);
		}else{
			$struct[str_replace('.txt', '', $dir)]=file_get_contents("$path/$dir");
		}
	}
	return $struct;
}
function storeStruct(&$struct,$userID,&$log,$repeatKeys,$kwastitle,$autoposting,$tbl,&$cats=array(),$parent=''){
	static $length;
	if(!isset($length)){
		list($length)=db::qrow("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS 
			WHERE TABLE_SCHEMA='".DB_NAME."' && TABLE_NAME='post' && COLUMN_NAME='url'");
	}
	foreach ($struct as $name=>$val){
		if(is_array($val)){# сохраняем категории
			$res=module::exec('category/admin',array('act'=>'structSave','parentID'=>$parent,'name'=>$name,'view'=>'on'),'data')->data;
			if(!$res) die("category insert error");
			$log->cats[$name]=(object)array(
					'name'=>$name,
					'url'=>$res->id,
					'status'=>$res->exists?'exists':($res->renamed?'renamed':'insert'),
			);
			$t_cats=array_merge($cats,array($res->id));
			storeStruct($val,$userID,$log->cats[$name],$repeatKeys,$kwastitle,$autoposting,$tbl,$t_cats,$res->id);
		}else{# сохраняем посты
			#распаковываем данные поста
			if(!empty($val)){
				$data=json_decode($val);
				if(!is_object($data)){
					$data=unserialize($val);
					if(!is_object($data)){
						unset($data); $val='';
					}else
						$val=json_encode($data);
				}
			}
			if(!isset($data)) $data=new \stdClass;
			#Проверяем существует ли уже пост
			$res=(object)array();
			$url=mb_substr(\posts_admin\key2url($name),0,$length);
			$title=!empty($data->key)?$data->key:$name;
			$pin=@$data->pin?$parent:false;

			$log->posts[$name]=db::qfetch("SELECT `url`,title FROM `{$tbl->post}` WHERE `url`='$url' LIMIT 1");
			if(empty($log->posts[$name]->url)||$repeatKeys=='insert'){#Если не существует то записываем
				#Если пост существует но принудительно задано записывать все посты
				if($repeatKeys=='insert') $url=getUrl('post',mt_rand(0,1000000)."-$name");
				$url=structSavePost($title,$url,$userID,$cats,$pin,$val,$kwastitle,$autoposting,$tbl);
				$log->posts[$name]=(object)array(
					'status'=>$url?'insert':'error',
					'url'=>$url,
					'title'=>$name,
					'pin'=>$pin
				);
			}else{
				# записываем новые категории
				savePostCats($cats,$url,$tbl,$repeatKeys);
				$log->posts[$name]->status='exists';
			}
		}
	}
}
/*
 * Сохраняем пост из loadStruct
*/ 
function structSavePost($name,$url,$userID,$cats,$pin=false,$data=false,$kwastitle=true,$autoposting=false,$tbl){
	$autoposting=$autoposting!==false&&$pin===false?'autoposting':'published';
	$pin=$pin===false?'':$pin;
	db::query("INSERT IGNORE INTO `{$tbl->post}` SET 
		`url`='{$url}',".
		"`title`='".($keyword=db::escape(stripcslashes(html_entity_decode($name))))."',".
		"`user`='{$userID}',".
		"`pincid`='{$pin}',".
		"`data`='".db::escape($data)."',".
		"`published`='{$autoposting}',".
		"`date`=NOW(),".
		"`datePublish`=NOW()".($autoposting=='autoposting'?' + INTERVAL 1 DAY':''));
	$id=db::insert();
	if($id){
		if($kwastitle){
			#Записываем кейворды
			db::query("INSERT IGNORE INTO `keyword` SET title='$keyword'");
			if(!$kid=db::insert()){
				list($kid)=db::qrow("SELECT id FROM `keyword` WHERE `title`='$keyword' LIMIT 1");
			}
			if($kid){
				db::query("INSERT INTO `".PREFIX_SPEC."keyword2post` SET kid='$kid',pid='$id',tbl='{$tbl->post}'");
				db::query("UPDATE `{$tbl->post}` SET kid='$kid' WHERE id='$id' LIMIT 1");
			}
		}
		# записываем новые категории
		savePostCats($cats,$url,$tbl);
		return $url;
	}else
		return false;
}
# записываем категории для поста
function savePostCats($cats,$url,$tbl,$repeatKeys='add'){
	static $posts;
	if(empty($cats)) return;
	$sqlVal=array();
	if($repeatKeys=='update' && empty($posts[$url])){
		$posts[$url]=1;
		db::query("DELETE FROM `".PREFIX_SPEC."{$tbl->category2post}` WHERE pid='$url'");
	}
	foreach ($cats as $cat) {
		$sqlVal[]="('{$cat}','{$url}')";
	}
	db::query("INSERT IGNORE INTO `".PREFIX_SPEC."{$tbl->category2post}` (`cid`,`pid`) VALUES".implode(',',$sqlVal));
}

