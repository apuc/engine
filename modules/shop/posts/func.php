<?
namespace shop_posts;
use module,db,url,cache;

function getPrevNext($post,$cat){
	$cid=is_object($cat)?$cat->url:$cat;
	$res=new \stdClass;
	list($count)=db::qrow("SELECT COUNT(pid) FROM `".PREFIX_SPEC."category2post` WHERE cid='{$cid}'");
	if($count>2){
		$res->prev=db::qfetch("SELECT post.id, post.title, post.url FROM `post` WHERE url=(SELECT pid FROM `".PREFIX_SPEC."category2post` WHERE cid='{$cid}' AND pid<'{$post->url}' ORDER BY pid DESC LIMIT 1)");
		if(!$res->prev){
			$res->prev=db::qfetch("SELECT post.id, post.title, post.url FROM `post` WHERE url=(SELECT pid FROM `".PREFIX_SPEC."category2post` WHERE cid='{$cid}' AND pid!='{$post->url}' ORDER BY pid DESC LIMIT 1)");
		}
		$res->next=db::qfetch("SELECT post.id, post.title, post.url FROM `post` WHERE url=(SELECT pid FROM `".PREFIX_SPEC."category2post` WHERE cid='{$cid}' AND pid>'{$post->url}' AND pid!='{$res->prev->url}' ORDER BY pid ASC LIMIT 1)");
		if(!$res->next){
			$res->next=db::qfetch("SELECT post.id, post.title, post.url FROM `post` WHERE url=(SELECT pid FROM `".PREFIX_SPEC."category2post` WHERE cid='{$cid}' AND pid NOT IN('{$post->url}','{$res->prev->url}') ORDER BY pid ASC LIMIT 1)");
		}
	}
	return $res;
}
#Проверяем права на просмотр и редактирование поста
function checkAccess($access,$user){
	return (
		//Может редактировать все новсти
		$access->rbac('editNews') or
		//Может редактировать только свою новсть
		($access->rbac('editNewsMy') 
			&& $access->user->id==$user
		)
	)?true:false;
}
#Получаем картинки для поста
function getImages($tbl,$pid,$imgfromcookie){
	db::query("SELECT 
			`url`,keyword.title,pid,width,height,filesize,text
		FROM `".PREFIX_SPEC."imgs` imgs 
		LEFT JOIN keyword ON keyword.id=imgs.kid 
			WHERE `tbl`='{$tbl}' && `pid`='{$pid}' && type!=1 
		ORDER BY `priority` DESC");
	$imgs=array();$imgcookie=false;
	while($d=db::fetch()){
		#Получаем картинку которую видел человек в гугле
		if($imgfromcookie)
			if(!isset($imgcookie)&&$imgfromcookie==$d->url){
				$imgcookie=$d;
			}
		$imgs[]=$d;
	}
	return array($imgcookie,$imgs);
}
/*
	Обрезает текст до конца предложения
		- сохраняет вставки img и iframe
	params
		text, (int) - количество слов, симв. окончания
*/
function cutText($str,$cword=40,$e='...'){
	$pagebreak=strpos($str,'<!-- pagebreak -->');
	if($pagebreak!==false){
		return substr($str,0,$pagebreak).$e;
	}
	$str=trim($str);
	$str=strip_tags($str,'<img><iframe>');
	$textArr=array();
	do{
		preg_match('!^(.*?)(<(?:img|iframe)[^>]*>(?:\s*</iframe>)?)(.*?)$!si', $str,$m);
		if(isset($m[1])){
			foreach (explode(' ', $m[1]) as $val) {
				array_push($textArr, $val);
			}
			if(count($textArr)>=$cword) {
				$textArr=array_slice($textArr,0,$cword);
				break;
			}
			if(!empty($m[2]))
				$textArr[]=$m[2];
		}else{
			foreach (explode(' ', $str) as $val) {
				array_push($textArr, $val);
			}
			$textArr=array_slice($textArr,0,$cword);
			break;
		}
		if(empty($m[3])) break;
		$str=$m[3];
	}while(1);
	if(isset($textArr[0])){
		$str=implode(' ',$textArr);
		if(!empty($str))$str.=$e;
	}

	return $str;
}
/*
	Записывает количество просмотров для поста
*/
function statViews(&$post,$tbl){
	if(date('Y-m-d',strtotime($post->statShortFlag))<date('Y-m-d',time()-604800)){
		$statViewsShort=", `statViewsShort`='1', `statShortFlag`=NOW()";
	}else{
		$statViewsShort=", `statViewsShort`=`statViewsShort`+1";
	}
	db::query("UPDATE `{$tbl}` SET `statViews`=`statViews`+1{$statViewsShort} WHERE `id`='{$post->id}'");
}
/*
	Получает список постов по категории
*/
function relatedPosts($cid,$post,$limit=5){
	$rel=array();
	$equal=is_array($cid)?" IN('".implode("','",$cid)."')":"='{$cid}'";
	$q="SELECT DISTINCT post.*,rel.cid FROM `".PREFIX_SPEC."category2{$post->tbl}` rel
				INNER JOIN `{$post->tbl}` ON post.url=rel.pid && cid{$equal} 
			WHERE post.published='published' && post.pincid='' && %s";
	db::query(sprintf($q,"post.id>'{$post->id}' LIMIT {$limit}"));
	while ($d=db::fetch()) {
		$d->txt=cutText(strip_tags($d->txt),20);
		$rel[$d->id]=$d;
	}
	$c=count($rel);
	if($c<$limit){
		$limit=$limit-$c;
		$sqlIn=empty($rel)?'':",'".implode("','",array_keys($rel))."'";
		db::query(sprintf($q,"post.id>0 && post.id NOT IN('{$post->id}'{$sqlIn}) LIMIT {$limit}"));
		while ($d=db::fetch()) {
			$d->txt=cutText(strip_tags($d->txt),20);
			$rel[$d->id]=$d;
		}	
	}

	# получаем картинки
	db::query("SELECT url,keyword.title,pid FROM `".PREFIX_SPEC."imgs` img 
		LEFT OUTER JOIN keyword ON keyword.id=img.kid 
		WHERE img.tbl='".handler::tbl."' && img.pid IN(".implode(',', array_keys($rel)).") ORDER BY priority DESC");
	while ($d=db::fetch()) {
		if(!isset($rel[$d->pid]->imgUrl)){
			$rel[$d->pid]->imgUrl=$d->url;
			$rel[$d->pid]->imgTitle=$d->title;
		}
	}

	return $rel;
}
/*
	Случайная категория у которой постов >= 5
*/
function getRandCat($exclude=false){
	$reltbl=PREFIX_SPEC."category2".handler::tbl;
	$cat=array();
	db::query("SELECT title,url,count AS cn FROM `category` WHERE `parentId`='' && `view`=1 && count>=5".($exclude?" && `url`!='{$exclude->url}'":''));
	while ($d=db::fetch()) {
		$cat[]=$d;
	}
	shuffle($cat);
	return !empty($cat)?$cat[0]:false;
}
/*
	Получает список популярных постов в категории
*/
function popularPostsInCategory($cid, $limit=4){
	$limit = (int) $limit;
	if (!$cid){return;}
	$cid=db::escape($cid);
	$relTbl=PREFIX_SPEC."category2".handler::tbl;
	$subsql=
		"SELECT %s FROM (
			SELECT pid,cid FROM `{$relTbl}` cat WHERE cid='{$cid}'
		) cat 
		INNER JOIN ".handler::tbl." post ON post.url=cat.pid WHERE post.published = 'published' && post.pincid='' %s";
	
	$c=db::qfetch(sprintf($subsql, "COUNT(post.id) as cnt", ""));
	$offset=0;
	$from=(int)$c->cnt - $limit;
	if($from>0){$offset=mt_rand(0, $from);}
	db::query(sprintf($subsql,"post.id, post.title, post.url, post.txt","ORDER BY post.statViewsShort DESC LIMIT {$limit} OFFSET {$offset}"));
	while ($d=db::fetch()){
		$d->txt=cutText(strip_tags($d->txt), 20);
		$posts[$d->id]=$d;
	}
	if(empty($posts)) return;

	$sql="SELECT * FROM 
		(SELECT 
				img.priority,img.pid, img.url, keyword.title FROM `".PREFIX_SPEC."imgs` img 
			LEFT OUTER JOIN keyword 
				ON keyword.id=img.kid
			WHERE 
				img.tbl='".handler::tbl."' 
				&& img.pid IN(".implode(',', array_keys($posts)).")
			ORDER BY img.priority DESC
		) img 
		GROUP BY img.pid";
	db::query($sql);
	while ($d=db::fetch()) {
		$posts[$d->pid]->imgUrl=$d->url;
		$posts[$d->pid]->imgTitle=$d->title;
	}
	return $posts;
}

/*
	Получает список популярных постов
*/
function popularPosts($post = null, $limit=4){
	$limit=(int)$limit;
	$sqlWhere=$post?" && id!='{$post->id}'":'';
	list($from) = db::qrow(
		"SELECT COUNT(id) as cnt FROM `".handler::tbl."` 
		WHERE 1 {$sqlWhere} && published = 'published' && post.pincid ='' ");
	if($from>100)$from=100;
	$offset=($from-$limit)>0?mt_rand(0,$from-$limit):0;
	$sql="SELECT * FROM `".handler::tbl."` WHERE 1 {$sqlWhere} && published = 'published' 
			ORDER BY statViewsShort DESC LIMIT {$limit} OFFSET {$offset}";
	$posts = array();
	db::query($sql);
	while ($post = db::fetch()) {
		$post->txt = cutText(strip_tags($post->txt), 20);
		$posts[$post->id]=$post;
	}
	# получаем картинки
	db::query("SELECT url,keyword.title,pid FROM `".PREFIX_SPEC."imgs` img 
		LEFT OUTER JOIN keyword ON keyword.id=img.kid 
		WHERE img.tbl='".handler::tbl."' && img.pid IN(".implode(',', array_keys($posts)).") ORDER BY priority DESC");
	while ($d=db::fetch()) {
		if(!isset($posts[$d->pid]->imgUrl)){
			$posts[$d->pid]->imgUrl=$d->url;
			$posts[$d->pid]->imgTitle=$d->title;
		}
	}

	return $posts;
}

/*
	Получает список постов автора
*/
function authorPosts($post, $limit=4){
	$limit=(int)$limit;
	$sql=
		"SELECT * FROM
			(SELECT post.*,img.url AS imgUrl, keyword.title AS imgTitle FROM 
				(SELECT * 
					FROM `".handler::tbl."` 
						WHERE user = {$post->user} && %s && published = 'published' 
					ORDER BY statViewsShort DESC 
					LIMIT %d
				) post
			/* присоединяем данные по картинкам */
			INNER JOIN `".PREFIX_SPEC."imgs` img 
				ON img.tbl='".handler::tbl."' && img.pid=post.id 
			JOIN keyword 
				ON keyword.id=img.kid
			ORDER BY img.priority DESC) p
		GROUP BY p.id";
	$posts=array();
	db::query(sprintf($sql, "post.id > {$post->id}", $limit));
	while($p = db::fetch()) {
		$p->txt = cutText(strip_tags($p->txt), 40);
		$posts[$p->id] = $p;
	}
	$c = count($posts);
	if($c<$limit){$limit -= $c;
		db::query(sprintf($sql, "post.id < {$post->id}", $limit));
		while ($p = db::fetch()) {
			$p->txt = cutText(strip_tags($p->txt), 40);
			$posts[$p->id] = $p;
		}	
	}
	return $posts;
}

/*
	Получает список случайных фото
*/
function featuredPhoto($cid=null, $limit=9) {
	$cid=db::escape($cid);
	$sql="SELECT %s FROM `".handler::tbl."` post ".(
		$cid?
			"JOIN `".PREFIX_SPEC."category2".handler::tbl."` cat
			ON post.url=cat.pid AND cid = '{$cid}'"
			:"").
		" WHERE published = 'published' && post.pincid='' %s";
	list($count)=db::qrow(sprintf($sql,"COUNT(post.id)",''));
	$from=$count-$limit;
	$offset=($from>0)?mt_rand(0,$from):0;
	# получаем посты у которых есть картинки
	$photos = array();
	db::query(sprintf($sql,"post.id,post.url","LIMIT {$limit} OFFSET {$offset}"));
	while ($d=db::fetch()) {
		$photos[$d->id]=new \stdClass;
		$photos[$d->id]->imgs=array();
		$photos[$d->id]->post_url=$d->url;
	}
	# получаем картинки для выбранных постов
	db::query("SELECT pid,url,keyword.title FROM `".PREFIX_SPEC."imgs` img 
		LEFT OUTER JOIN keyword ON keyword.id=img.kid
		WHERE `tbl`='".handler::tbl."' && `pid` IN(".implode(',', array_keys($photos)).")");
	while ($d=db::fetch()) {
		$photos[$d->pid]->imgs[]=$d;
	}
	# рандомизируем
	$result=array();
	foreach ($photos as $k => $v) {
		if(empty($v->imgs))continue;
		shuffle($v->imgs);
		$d=$v->imgs[0];
		$d->post_url=$v->post_url;
		$result[]=$d;
	}

	return $result;
}
/*
	преобразование имени пользователя 
	в зависимости от полей mail и name
*/
function setAuthorName($d){
	if(empty($d->authorMail)){$d->authorName='Anonym';}
	elseif($d->authorName==''){$d->authorName=preg_replace('!@.+?$!i', '', $d->authorMail);}
	return $d->authorName;
}
/*
	Получаем ID всех родительских категорий c view=0, для хлебных крошек
*/
function getCrumbs($cat){
	static $Res;
	if(!isset($Res)) $Res[]=$cat;
	$catsEscape=array();
	$last=end($Res);
	if(!empty($last->parentId)){
		$d=db::qfetch("SELECT * FROM `category` WHERE `url`='".db::escape($last->parentId)."' LIMIT 1");
		$Res[]=$d;
		getCrumbs($d);
	}else{
		# убираем категории запрещенные к показу
		foreach ($Res as $key => $v) {
			if(isset($v->view))
				if($v->view==0) unset($Res[$key]);
		}
		rsort($Res);
	}
	return $Res;
}
/*
	получает список постов в одной из категорий текущего поста
*/
function relatedByCat($post,$limit=4){
	$related=array();
	if(!empty($post->cats)){
		$cats=array();
		foreach ($post->cats as $cat) {
			$cats[$cat->url]=clone $cat;
		}
		reset($post->cats);
		#выбираем оптимальную категорию
		db::query("SELECT `parentID` FROM `category` WHERE parentID IN ('".implode(',',array_keys($cats))."') GROUP BY parentID");	
		while ($d=db::fetch()) {
			unset($cats[$d->parentID]);
		}
		if(!empty($cats)){
			$sql="SELECT post.* FROM `post` 
				INNER JOIN `".PREFIX_SPEC."category2post` cp ON cp.pid=post.url && cp.cid='".current($cats)->url."'
				WHERE post.pincid='' && post.published='published' && %s";
			db::query(sprintf($sql,"post.id>'{$post->id}' LIMIT {$limit}"));
			while ($d=db::fetch()) {
				$d->txt=cutText(strip_tags($d->txt),20);
				$related[$d->id]=$d;
				$limit--;
			}
			if($limit){
				db::query(sprintf($sql,"post.id>0 && post.id!='{$post->id}' LIMIT {$limit}"));
				while ($d=db::fetch()) {
					$d->txt=cutText(strip_tags($d->txt),20);
					$related[$d->id]=$d;
				}
			}
			if(!empty($related)){
				# получаем картинки для выбранных постов
				db::query(
					"SELECT img.id,pid,url,kid,keyword.title,filesize,width,height FROM `".PREFIX_SPEC."imgs` img 
					LEFT OUTER JOIN keyword ON keyword.id=img.kid
					WHERE `tbl`='post' && `pid` IN(".implode(',', array_keys($related)).") ORDER BY `priority` DESC"
				);
				while ($d=db::fetch()) {
					$related[$d->pid]->imgs[]=$d;
				}
			}
		}
	}
	return $related;
}
/*
	получает случайные посты
*/
function randomPosts($limit=4,$exclude=array()){
	$l=$limit*3;
	$random=array();
	list($max)=db::qrow("SELECT MAX(id) FROM `post`");
	$rnd=mt_rand(0,($max>$l)?$max-$l:$max);
	$SQLexclude=!empty($exclude)?" && post.id NOT IN(".implode(',', array_keys($exclude)).")":'';
	$sql="SELECT * FROM 
		(SELECT post.* FROM `post` 
			WHERE post.pincid='' && post.published='published'{$SQLexclude} && %s) p
		ORDER BY RAND()";
	db::query(sprintf($sql,"post.id>'{$rnd}' LIMIT {$l}"));
	while ($d=db::fetch()) {
		$d->txt=cutText(strip_tags($d->txt),20);
		$random[$d->id]=$d;
		$l--;
		if(!--$limit) break;
	}
	if($l&&$limit){
		db::query(sprintf($sql,"post.id>0 LIMIT {$l}"));
		while ($d=db::fetch()) {
			$d->txt=cutText(strip_tags($d->txt),20);
			$random[$d->id]=$d;
			if(!--$limit) break;
		}
	}
	if(!empty($random)){
		# получаем картинки для выбранных постов
		db::query(
			"SELECT img.id,pid,url,kid,keyword.title,filesize,width,height FROM `".PREFIX_SPEC."imgs` img 
			LEFT OUTER JOIN keyword ON keyword.id=img.kid
			WHERE `tbl`='post' && `pid` IN(".implode(',', array_keys($random)).") ORDER BY `priority` DESC"
		);
		while ($d=db::fetch()) {
			$random[$d->pid]->imgs[]=$d;
		}
	}
	return $random;
}
/*
	сортирует категории относителдьно их родительских категорий
*/
function sortCats($cats){
	if(empty($cats)) return;
	foreach ($cats as $i=>$v) {
		$parents[$v->url]=new \stdclass;
		$parents[$v->url]=$v->parentId;
		$cats2url[$v->url]=$i;
	}
	#сортируем
	asort($parents);
	#возвращаем остальные данные объекта
	foreach ($parents as $url => $v) {
		$parents[$url]=$cats[$cats2url[$url]];
	}
	#помещаем подкатегории в их родителей
	foreach ($parents as $url => $v) {
		if(isset($parents[$v->parentId])){
			$parents[$v->parentId]->child[$url]=$parents[$url];
			unset($parents[$url]);
		}
	}
	#помещаем подкатегории следом за их родителем
	return recSortedCats($parents);
}
/*
	рекурсивный проход по дочерним категориям
*/
function recSortedCats($cats){
	static $sorted;
	if(empty($sorted)) $sorted=array();
	foreach ($cats as $url => $v) {
		$sorted[]=$v;
		if(isset($v->child)){
			recSortedCats($v->child);
			unset($v->child);
		}
	}
	return $sorted;
}
