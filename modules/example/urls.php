<?php
#маршруты модуля
#route::set('[pregexp](.*?)',array('param1'));
#route::set('[pregexp](.*?)',array('module'=>'posts','param1'));
#route::set('[pregexp](.*?)(.*?)',array('module'=>'posts','act'=>'post','param1','param2'));

#url модуля
/*
	Создание шаблона для URL
		$urls->post=HREF.'/$url.html';
	ИЛИ
		Если требуется дополнительная обработка URL для 
		function url_post($a){
			return HREF."/url_for_post_{$a}.html";
		}
*/
?>