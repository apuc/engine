<?php
$tpl->title='Install - '.NAME;
$tpl->desc="";
?>
<style type="text/css">
	.install table tr:nth-child(2n){background-color:#E9E9E9;}
	.install table td{padding: 2px 5px;}
</style>
<div class="install">
	<?=$data->status?>
	<?if(!empty($data->message)){?>
		<?foreach ($data->message as $m) {
			echo $m."<br/>";
		}?>
	<?}?>
	<form method="post" class="form" action="<?=url::install()?>">
	<?if(!empty($data->ready)){?>
		<table border="0" cellpadding="0" cellspacing="0">
		<?foreach ($data->ready as $v) {?>
			<tr>
				<td><?=str_replace('/admin', '', $v)?></td>
				<td><input type="checkbox" name="installModules[]" value="<?=$v?>" checked=""/></td>
			</tr>
		<?}?>
		</table>
	<?}?>
		<input class="button" type="submit" name="install" value="Install"/>
	</form>
</div>