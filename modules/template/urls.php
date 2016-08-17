<?php
#маршруты модуля
route::set('/style.css',array('module'=>'template','act'=>'style'));
route::set('/admin-style.css',array('module'=>'template','act'=>'style','admin'=>1));
?>