<?php
$tpl->title="Statistics".(!empty($data->userData)?": {$data->userData->mail}":'');
$tpl->desc="";
?>
<style type="text/css">
	.statistic{font-size: 0.9em;border: 1px solid black;width:100%;margin-top:5px;}
</style>
<div class="breadcrumbs">
	<a>statistics</a>
</div>
<div class="cols">
	<div class="left-col">
		<h3>Statistic:</h3>
		<?=$data->dateForm?>
		<?if(!empty($data->stat)){?>
			<table class="statistic" border="1">
				<th>#</th><th>date</th><th>search</th><th>copywriting</th><th>ready</th><th>published</th><th>sum</th><th>pub this day</th>
			<?
			foreach ($data->stat as $k=>$v) {?>
				<tr style="background-color:<?=($k%2)?'#FFFFFF':'#E5E5E5'?>;"><td><?=($k+1)?></td><td><?=$v->reformdate?></td><td><?=$v->search?></td><td><?=$v->copywriting?></td><td><?=$v->ready?></td><td><?=$v->published?></td><td><?=($v->sum)?><td><?=$v->pubReal?></td></td></tr>
			<?}?>
				<tr><td colspan="2"><b>Total</b></td><td><b><?=$data->total->search?></b></td><td><b><?=$data->total->copywriting?></b></td><td><b><?=$data->total->ready?></b></td><td><b><?=$data->total->published?></b></td><td><b><?=$data->total->sums?></b></td><td><?=@$data->total->pubReal?></td></tr>
			</table>
		<?}?>
	</div>
	<div class="right-col">
	<?if(!empty($data->usersList)){?>
		<h4>Users:</h4>
		<ul>
			<li><a href="<?=url::post_stat()?>">all</a></li>
		<?foreach ($data->usersList as $u) {?>
			<li><a href="<?=url::post_stat($u->id)?>"><?=$u->longName?></a></li>
		<?}?>
		</ul>
	<?}?>
	</div>
</div>
