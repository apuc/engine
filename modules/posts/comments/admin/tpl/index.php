<?php
$tpl->title=NAME;
$tpl->desc="comments moderation";
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<div>
	<a href="<?=url::comments_admin(1)?>">На модерации (<?=$data->count[1]?>)</a> | 
	<a href="<?=url::comments_admin(2)?>">Подозрительные (<?=$data->count[2]?>)</a> | 
	<a href="<?=url::comments_admin(3)?>">Промодерированные (<?=$data->count[3]?>)</a> | 
	<a href="<?=url::comments_admin(4)?>">Забаненные (<?=$data->count[4]?>)</a>
</div>

<form action="<?=url::comments_adminSave($data->type)?>" method="POST">
<table>
	<tr style="background-color:lime">
		<td><?=$data->type!=3?'Принять':'Отклонить'?></td>
		<td>id</td>
		<td>User</td>
		<td>Text</td>
		<td>Date</td>
		<td>Url</td>
	</tr>
	<?foreach($data->comments as $c){?>
		<tr>
			<td>
				<input type="checkbox" name="com[]" value="<?=$c->id?>">
				<input type="hidden" name="seen[]" value="<?=$c->id?>">
			</td>
			<td><?=$c->id?></td>
			<td><?=@$c->user->mail?></td>
			<td><?=$c->text?></td>
			<td><?=$c->date?></td>
			<td><a href="<?=url::post($c->url)?>" target="_blank">url</a></td>
		</tr>
	<?}?>
</table>
<input type="submit" name="save" value="Save">
</form>
