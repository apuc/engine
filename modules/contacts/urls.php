<?php
route::set('^/contacts.html',array('module'=>'contacts'),999);
route::set('^/capcha.jpg$',array('module'=>'contacts','act'=>'capcha'),999);

#url модуля
$GLOBALS['urls']->contacts=HREF.'/contacts.html';
$GLOBALS['urls']->contactsCapcha=HREF.'/capcha.jpg';
?>