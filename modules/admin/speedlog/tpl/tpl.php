<?php
/*
	Доступные переменные:
	- (std object) $tpl - данные для вывода в основной шаблон
	- (subTemplate object) $template - объект подшаблонов
		- $template->inc([path to sub template]) - метод подключения файлов подшаюблонов
	- (std object) $data - данные вернувшиеся из обработчика
*/
$tpl->title="Speed log";
$tpl->desc="";

?>
<?if($data->speeddata){?>
<div><?=$data->start?> - <?=$data->end?></div>
<table cellpadding="0" cellspacing="0" border="1">
	<tr>
		<th>group</th>
		<th>count</th>
	</tr>
	<?foreach ($data->speeddata as $key => $v) {?>
	<tr>
		<td style="padding:2px;">&gt;= <?=$key?></td>
		<td style="padding:2px;"><?=$v?> (<?=round($v/$data->count*100,1)?>%)</td>
	</tr>
	<?}?>
</table>
<?}else{?>
	<h2>No data of speed</h2>
<?}?>