<?
$tpl->meta='<meta name="robots" content="noindex,follow" />';

$err=$data->err;

include $template->inc('user/functions.php');

if($err) foreach($err as $er){?><?=userErr($er)?><br /><?}
elseif(!empty($data->mail)){?>Your password sent to <?=$data->mail?><?}?><br />
<form action="<?=url::userRestore()?>" method="POST">
	e-mail:<input type="text" name="mail" />
	<input type="submit" />
</form>