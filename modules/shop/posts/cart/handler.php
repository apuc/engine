<?php
namespace shop_posts_cart;
use module,db,url,cache;

class handler{
	function __construct(){
		$this->template='template';#Определяем в какой шаблон будем вписывать
		$this->headers=(object)array();
		$this->userControl=module::exec('user',array(),1)->handler;
		$this->user=$this->userControl->user;
	}
	/*
		добаляет товар в корзину
	*/
	function tocart($gid,$q,$increment=true,$cookieCart){
		$this->template='';
		$data=array();
		$data[]=(object)array('gid'=>$gid,'q'=>$q);
		$d=getCart($this->user,$cookieCart);
		if($this->user->id){
			if(!empty($d)){
				$data=addToCart(current($data),$d,$increment);
				db::query("UPDATE `shop_cart` SET `data`='".serialize($data)."' WHERE `uid`='{$this->user->id}'");
			}else{
				db::query("INSERT INTO `shop_cart` (`uid`,`data`) VALUES ('{$this->user->id}','".serialize($data)."')");
			}
		}else{
			if(!empty($d)){
				$data=addToCart(current($data),$d,$increment);
				db::query("UPDATE `shop_cart` SET `data`='".serialize($data)."' WHERE `ckid`='{$cookieCart}'");
			}else{
				$ckid=getUniqCkID();
				db::query("INSERT INTO `shop_cart` (`ckid`,`data`) VALUES ('{$ckid}','".serialize($data)."')");
				$this->headers->cookie['cookieCart']=array("{$ckid}",'+1 year');
			}
		}
		return (object)array(
			'html'=>'success',
		);
	}
	/*
		список товаров в корзине
	*/
	function cart($cookieCart){
		$posts=array();
		$gids=array();
		$sum=0;
		$cart=getCart($this->user,$cookieCart);
		if($cart){
			foreach ($cart as $v) {
				$gids[$v->gid]=$v;
			}
			db::query(
				"SELECT p.*,c.url AS catUrl,c.title AS catTitle FROM `shop_post` p
					LEFT JOIN `".PREFIX_SPEC."category2shop_post` cp 
						ON p.url=cp.pid
					LEFT JOIN `shop_category` c 
						ON c.url=cp.cid
				WHERE p.id IN(".implode(',',array_keys($gids)).")");
			while ($d=db::fetch()) {
				$d->want=$gids[$d->id]->q;
				$posts[]=$d;
				$sum+=$d->price;
			}
		}

		return (object)array(
			'posts'=>$posts,
			'sum'=>$sum,
		);
	}
	/*
		удаляет товар из корзины
	*/
	function outcart($gid,$cookieCart){
		$this->template='';
		$d=getCart($this->user,$cookieCart);
		$data=rmFromCart($gid,$d);
		$mes='success';#определяяет реакцию JS на ответ сервера
		if($this->user->id){
			if(empty($data))
				db::query("DELETE FROM `shop_cart` WHERE `uid`='{$this->user->id}'");
			else
				db::query("UPDATE `shop_cart` SET `data`='".serialize($data)."' WHERE `uid`='{$this->user->id}'");
		}
		elseif($cookieCart){
			if(empty($data))
				db::query("DELETE FROM `shop_cart` WHERE `uid`='{$this->user->id}'");
			else
				db::query("UPDATE `shop_cart` SET `data`='".serialize($data)."' WHERE `ckid`='{$cookieCart}'");
		}else
			$mes='cart ID error';
		return (object)array(
			'html'=>$mes,
		);
	}
}

/*
	генерирует уникальный ID корзины для куков
*/
function getUniqCkID(){
	do{
		$ckid=mt_rand();
		list($exists)=db::qrow("SELECT `ckid` FROM `shop_cart` WHERE `ckid`='{$ckid}' LIMIT 1");
	}while($exists);
	return $ckid;
}
/*
	добавляет товар к списку товаров в корзине
	возвращает список товаров
*/
function addToCart($tocart,$list,$increment=true){
	$add=true;
	foreach ($list as &$v) {
		if($v->gid==$tocart->gid){
			if($increment)
				$v->q++;
			else
				$v->q=$tocart->q;
			$add=false;
		}
	}
	if($add)
		$list[]=$tocart;
	return $list;
}
/*
	удаляет товар из списка в корзине
	возвращает список товаров
*/
function rmFromCart($gid,$list){
	foreach ($list as $k=>&$v) {
		if($v->gid==$gid){
			unset($list[$k]);
		}
	}
	return $list;
}
/*
	получает ID товаров в корзине
*/
function getCart($user,$cookieCart){
	if($user->id){
		#для авторизованных пользователей
		list($d)=db::qrow("SELECT `data` FROM `shop_cart` WHERE `uid`='{$user->id}' LIMIT 1");
	}elseif($cookieCart){
		#для не авторизованных пользователей
		list($d)=db::qrow("SELECT `data` FROM `shop_cart` WHERE `ckid`='{$cookieCart}' LIMIT 1");
	}
	return (!empty($d))?unserialize($d):false;
}