<?php
route::set('^/install/?$',array('module'=>'admin/install'),2);

#url модуля
$GLOBALS['urls']->install=HREF.'/?module=admin/install';
?>
