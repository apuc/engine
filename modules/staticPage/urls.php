<?php
#маршруты модуля
route::set('/s/([^\/]+)\.html',array('module'=>'staticPage','url'),999);

$urls->staticPage=HREF.'/s/$url.html';
?>