<?
$tpl->desc=$tpl->title="User activation. ".NAME;	
	
include $template->inc('user/functions.php');

foreach($data->err as $er){?><?=userErr($er)?><br /><?}