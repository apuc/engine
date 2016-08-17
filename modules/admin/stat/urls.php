<?php
#маршруты модуля
#route::set('[pregexp](.*?)',array('param1'));
#route::set('[pregexp](.*?)',array('module'=>'posts','param1'));
#route::set('[pregexp](.*?)(.*?)',array('module'=>'posts','act'=>'post','param1','param2'));

route::set('/s\.gif(?:\?\d+)?',array('module'=>'admin/stat'),9999);
?>