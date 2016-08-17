<?php
route::set('^/image-([^/]+)/(\d+)-([^/]+.jpg).html',array('module'=>'gallery','tbl','pid','url'),2);

#url модуля
$GLOBALS['urls']->img=HREF.'/image-$tbl/$pid-$img.html';#страница картинки
$GLOBALS['urls']->imgOverlay='#gal_$tbl_$pid_$img';#страница картинки
$GLOBALS['urls']->imgFile=PATH.'modules/images/files/images/$name';
?>