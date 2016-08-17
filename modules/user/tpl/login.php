<?
$tpl->desc=$tpl->title="login. ".NAME;
$tpl->meta='<meta name="robots" content="noindex,follow" />';

@$err=$data->err;

include_once $template->inc('user/functions.php');

if(count($err))foreach($err as $er){?><?=userErr($er)?><br /><?}?>
<center>
	<div style="width:250px;text-align:left;" class='login'>
		<form action="<?=url::userLogin()?>" method="post">
		<h1 style="padding-top:20px;"><span>Log in</span></h1>
		<table style="padding-top:25px;">
			<tr>
				<td><label class="lable">Email:</label></td>
				<td><div class="inputDiv"><input type="text" name="mail" class="inp"></div></td>
			</tr>
			<tr>
				<td><label class="lable">Password:</label></td>
				<td><div class="inputDiv"><input type="password" name="pas" class="inp"></div></td>
			</tr>
		</table>
		<label class="lable checktext" for="rememberme">
			<input type="checkbox" name="remember" value="1" checked="" style="display:inline !important;width:10px;">
			Remember me on this computer.
		</label>
		<input type="submit" name="type" value="Continue" class="button">
		or <a href="<?=url::userRegister()?>">Sign Up</a>
		</form>
		<a href="<?=url::userRestore()?>" rel="nofollow">restore password</a>
		<br/>
		<?if(@$data->socauth){?>
			Login with:
			<div class="social-auth">
				<a href="<?=$data->socauth->facebook?>" class="auth-fb"></a>
				<a href="<?=$data->socauth->google?>" class="auth-gplus"></a>
				<a href="<?=$data->socauth->twitter?>" class="auth-tw"></a>
			</div>
		<?}?>
	</div>
</center>
