<?php
$tpl->title="admin/traffic - ".NAME;
$tpl->desc="";
$sortDirec=strtolower($data->sort[1]);
?>
<script type="text/javascript">
	$(document).ready(function(){
		anotherRunningAtt();
	});
	function anotherRunningAtt(){
		$.get(window.location.basepath+"?module=admin/traffic",{
			act:'anotherRunning',
		},function answer(data){
			if(data) {
				if(!confirm("HAVE TO CONTINUE?\nAnother script runnig\n"+data)){
					$('[name=repl_parsing_run]').attr('disabled','');
				}
			}
		});
	}
</script>
<style type="text/css">
	.traffic-data table th,.traffic-data table td{padding: 2px 4px}
	.traffic-data table tr:nth-child(2n+3){
		background-color: #ECECEC;
	}
	.traffic-data > div{display: inline-block;vertical-align: top;}
	.traffic-data table th a{text-decoration: none;color: black;}
</style>
<small>mysql table size: <?=$data->tablesize?> Gb</small>
<?include $template->inc('index/panel.php');?>
<div class="traffic-data">
<?if(!empty($data->sum)){?>
	<div>
		<table border="1" cellpadding="0" cellspacing="0">
			<tr>
				<th rowspan="2"><a href="<?=url::admin_traffic('date|'.($sortDirec=='desc'?'asc':'desc'))?>">дата <small title="sort"><?=(@$data->sort[0]=='date'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th rowspan="2"><a href="<?=url::admin_traffic('traf|'.($sortDirec=='desc'?'asc':'desc'))?>">трафик <small title="sort"><?=(@$data->sort[0]=='traf'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th colspan="2">замена постов</th>
				<th rowspan="2" style="width:50px;">всего постов</th>
				<th colspan="2">замена кейвордов</th>
				<th rowspan="2" style="width:50px;">всего кейвордов</th>
				<th colspan="2">постов без картинки</th>
				<th colspan="2">кейвордов без картинки</th>
			</tr>
			<tr>
				<th><a href="<?=url::admin_traffic('p_top5|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 5<small title="sort"><?=(@$data->sort[0]=='p_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic('p_top100|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 100<small title="sort"><?=(@$data->sort[0]=='p_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic('k_top5|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 5<small title="sort"><?=(@$data->sort[0]=='k_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic('k_top100|'.($sortDirec=='desc'?'asc':'desc'))?>">топ 100<small title="sort"><?=(@$data->sort[0]=='k_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<td>топ 5</td>
				<td>топ 100</td>
				<td>топ 5</td>
				<td>топ 100</td>
			</tr>
		<?foreach ($data->sum as $v) {?>
			<tr>
				<td><a href="<?=url::admin_traffic_cats($v->date)?>"><?=$v->date?></a></td>
				<td><?=@(int)$v->traf?></td>
				<td><?=$v->p_top5?></td>
				<td><?=$v->p_top100?></td>
				<td><?=$data->countPosts[$v->date]?></td>
				<td><?=$v->k_top5?></td>
				<td><?=$v->k_top100?></td>
				<td><?=$data->countKw?></td>
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
