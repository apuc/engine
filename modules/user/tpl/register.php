<?
$tpl->desc=$tpl->title="Registration. ".NAME;
$tpl->meta='<meta name="robots" content="noindex,follow" />';

include_once $template->inc('user/functions.php');

@$err=$data->err;
@$msg=$data->from;
@$mail=$data->mail;
@$auth=$data->socauth;	
$mes=array(
	'com'=>"Thanks for the comment!",
	'likeliked'=>"You have already voted!",
	'likegood'=>"Thank you for your vote!",
);
if(@$mes[$msg]){?>
	<span class='message'><?=$mes[$msg]?> Signup please!</span><?
}
if(count($err))foreach($err as $er){?><?=userErr($er)?><br /><?}
?>
<center>
	<div style="width:250px;text-align:left;" class='register'>
		<form action="<?=url::userRegister()?>" method="post" name="regForm" >
		<h1 style="padding-top:20px;"><span>Sign up</span></h1>
		<table style="padding-top:50px;">
		<tr><td>
			<label class="lable">Email:</label></td>
			<td><div class="inputDiv"><input type="text" name="mail" class="inp" value="<?=$mail?>"></div></td>
		</tr>
		<tr>
			<td><label class="lable">Password:</label></td>
			<td><div class="inputDiv"><input type="password" name="pas" class="inp"></div></td>
		</tr>
		</table>
		<input type="submit" name=submit value="Continue" class="button">
		</form>
		<br/>
		<?if($auth){?>
		Login with:
		<div class="social-auth">
			<a href="<?=$auth->facebook?>" class="auth-fb"></a>
			<a href="<?=$auth->google?>" class="auth-gplus"></a>
			<a href="<?=$auth->twitter?>" class="auth-tw"></a>
		</div>
		<?}?>
	</div>
</center>
