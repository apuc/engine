<?php
list($status,$mail,$text)=array($data->status,$data->mail,$data->txt);
$tpl->title="Contacts.";
$tpl->meta='<meta name="robots" content="noindex,follow" />';
?>
<div style="padding:50px 0 0 200px;">
	<h1>Contact Us</h1><br><br>
<h3><?
	$txt=array('sent'=>'Mail sent!',
		'capcha'=>'CAPTCHA Failed',
		'mail'=>'Wrong mail'
		);
	foreach($status as $v){
		print "<h1 style='color:red;'>".$txt[$v]."</h1><br>";
	}
?></h3>
<form name="contact" method="post" action="">
	<label for="mail">E-mail</label><br>
	<input name="mail" type="text" value="<?=$mail?>" size="35"/>
	<br><label for="text">Comment</label><br>
	<textarea name="text" rows="7" cols="40"><?=$text?></textarea>
	<br><label for="capcha"><img src="<?=url::contactsCapcha()?>"></label><br>
	<input name="secret" type="text" size="35"/><br><br>
	<input type="submit" name='send' value="Comment" />
	
</form>
</div>
<br/>
