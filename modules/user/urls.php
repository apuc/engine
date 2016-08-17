<?php
route::set('^/register.html',array('module'=>'user','act'=>'register'),999);
route::set('^/login.html',array('module'=>'user','act'=>'login'),999);
route::set('^/restore.html',array('module'=>'user','act'=>'restore'),999);
route::set('^/auth/(facebook|google|twitter)',array('module'=>'user','act'=>'auth','service'),999);

#url модуля
$urls->userLogin=HREF.'/login.html';
$urls->userRegister=HREF.'/register.html';
$urls->userRestore=HREF.'/restore.html';

$urls->userSettings=HREF.'?module=user&act=settings';
$urls->userLogout=HREF.'?module=user&act=logout';

$urls->userActivate=HREF.'?module=user&act=activate&mail=%mail%&code=%code%';
?>