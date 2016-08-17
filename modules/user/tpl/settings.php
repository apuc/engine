<?
$tpl->desc=$tpl->title="Profile setings. ".NAME;
$user=$data->user;
$err=$data->err;
$msg=$data->msg;

include $template->inc('user/functions.php');

foreach($err as $er){?><?=userErr($er)?><br /><?}
$mes=array(
	'register'=>"Thank you for you registration!<br>On you email have been send activation code which is needed to chek your mail.",
	'activate'=>"Activation complite!<br>Thank you for you registration!",
	'newPas'=>"New password was generated and send on your email.",
	'settings'=>"Your settings have been successfully updated!",
);
if(@$mes[$msg]){?>
	<span class='message'><?=$mes[$msg]?></span><?
}?>
<center>
<form action="<?=url::userSettings()?>" method="post">
<table style="width:200px;">
	<tr><td><h1><span>Profile</span></h1></td></tr>
	<tr><td><label class="lable">Your mail:</label><br><b><?=@$user->mail?></b></td></tr>
<tr>
	<td><label class="lable">Your name:</label><br><input type="text" name="name" value="<?=@$user->name?>" class="inp"></td>
</tr>
<tr><td>
	<br><br><h3>Change password:</h3>
	<label class="lable">Current password:</label><br><input type="password" name="pas" class="inp"></td>
</tr><tr>
	<td><label class="lable">New password(Leave empty to do not change):</label><br><input type="password" name="newPas" class="inp"></td>
</tr>
<tr><td><br><br><input type="submit" name="submit" value="Continue" class="continue"></td></tr>
</table>
</form>
</center>