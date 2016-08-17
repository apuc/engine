<?if(@$data->user->mail){?>	
	<a href="<?=url::contacts()?>" rel="nofollow">Contacts</a>
	<a href="<?=url::userSettings()?>">Profile</a>
	<a href="<?=url::userLogout()?>">exit</a>
<?}else{?>
	<a href="<?=url::contacts()?>" rel="nofollow">Contacts</a>
	<a href="<?=url::userRegister()?>" rel="nofollow" onclick="return userDiv('register')">Signup</a>
	<a href="<?=url::userLogin()?>" rel="nofollow" onclick="return userDiv('login')">Login</a>
	<?include $template->inc('menu/userDiv.php');?>
<?}?>
