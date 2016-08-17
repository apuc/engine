<?php
$tpl->title="admin/traffic - {$data->cat->title} - ".NAME;
$tpl->desc="";
$sortDirec=strtolower($data->sort[1]);
?>
<style type="text/css">
	.traffic-data table th,.traffic-data table td{padding: 2px 4px}
	.traffic-data table tr:nth-child(2n+3){
		background-color: #ECECEC;
	}
	.traffic-data table th a{text-decoration: none;color: black;}
</style>
<?include $template->inc('index/panel.php');?>
<div class="traffic-data">
	<h3><?=$data->cat->title?></h3>
<?if(!empty($data->cats)){?>
	<div style="display:inline-block;">
		<table border="1" cellpadding="0" cellspacing="0">
			<tr>
				<th rowspan="2"><a href="<?=url::admin_traffic_itemCat($data->cat->url,'date|'.($sortDirec=='desc'?'asc':'desc'))?>">дата <small title="sort"><?=(@$data->sort[0]=='date'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th rowspan="2"><a href="<?=url::admin_traffic_itemCat($data->cat->url,'traf|'.($sortDirec=='desc'?'asc':'desc'))?>">трафик <small title="sort"><?=(@$data->sort[0]=='traf'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></th>
				<th colspan="2">замена остов</th>
				<th rowspan="2" style="width:50px;">всего постов</th>
				<th colspan="2">замена кейвордов</th>
				<th rowspan="2" style="width:50px;">всего кейвордов</th>
				<th colspan="2">постов без картикни</th>
				<th colspan="2">кейвордов без картикни</th>
			</tr>
			<tr>
				<th><a href="<?=url::admin_traffic_itemCat($data->cat->url,'p_top5|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 5<small title="sort"><?=(@$data->sort[0]=='p_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_itemCat($data->cat->url,'p_top100|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 100<small title="sort"><?=(@$data->sort[0]=='p_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_itemCat($data->cat->url,'k_top5|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 5<small title="sort"><?=(@$data->sort[0]=='k_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_itemCat($data->cat->url,'k_top100|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 100<small title="sort"><?=(@$data->sort[0]=='k_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<td>топ 5</td>
				<td>топ 100</td>
				<td>топ 5</td>
				<td>топ 100</td>
			</tr>
		<?foreach ($data->cats as $v) {?>
			<tr>
				<td><?=$v->date?></td>
				<td><?=@(int)$v->traf?></td>
				<td><?=$v->p_top5?></td>
				<td><?=$v->p_top100?></td>
				<td><?=$v->pcount?></td>
				<td><?=$v->k_top5?></td>
				<td><?=$v->k_top100?></td>
				<td><?=$v->kcount?></td>
				<td><?=$v->p_tbnMatchTop5?></td>
				<td><?=$v->p_tbnMatchTop100?></td>
				<td><?=$v->k_tbnMatchTop5?></td>
				<td><?=$v->k_tbnMatchTop100?></td>
			</tr>
		<?}?>
		</table><br/>
		<?=$data->paginator?>
	</div>
<?}?>
</div>
