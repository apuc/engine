<?php
$tpl->title="Social Buttons Statistics";
$tpl->desc="";
?>
<style type="text/css">
	.statistic{font-size: 0.9em;width:100%;margin-top:5px;}
	.statistic tr:last-child{font-weight:bold;}
	.statistic tr td {padding:2px;}
</style>

<div class="cols">
	<div class="left-col">
		<h3>Social Buttons Statistics</h3>
		<?=$data->dateForm?>
		<?if(!empty($data->stat)){?>
			<table class="statistic" border="1">
				<tr><th>date</th>
				<?foreach ($data->total as $key => $value) {?>
					<th><?=$key?></th>
				<?}?>
				</tr>
			<?
			$i=0;
			foreach ($data->stat as $date=>$stat) {?>
				<tr style="background-color:<?=(++$i%2)?'#FFFFFF':'#E5E5E5'?>;"><td><?=$date?></td>
				<?foreach ($data->total as $key => $value) {?>
					<td><span style="color:green;" title="like"><?=isset($stat[$key])?$stat[$key]->like:0?></span>
					/ <span style="color:red;" title="unlike"><?=isset($stat[$key])?$stat[$key]->unlike:0?></span></td>
				<?}?>
				</tr>
			<?}?>
			<tr style="background-color:<?=(++$i%2)?'#FFFFFF':'#E5E5E5'?>;">
				<td><b>Total:</b></td>
				<?foreach ($data->total as $key => $val) {?>
					<td><span style="color:green;" title="like"><?=$val->like?></span>
					/ <span style="color:red;" title="unlike"><?=$val->unlike?></span></td>
				<?}?>
			</tr>
			</table>
		<?}?>
	</div>
</div>
