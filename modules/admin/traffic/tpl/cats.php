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
	.traffic-data table th a{text-decoration: none;color: black;}
	.traffic-data table td small a{text-decoration: none;}
</style>
<?include $template->inc('index/panel.php');?>
<div class="traffic-data">
	<div>
		<?if(!empty($data->parent->url)){?>
			категория: <b><?=$data->parent->title?></b> |
			<a href="<?=url::admin_traffic_itemCat($data->parent->url)?>">По датам</a> |
		<?}?>
		<a href="<?=url::admin_traffic_posts('','',$data->parent->url)?>">По постам</a>
	</div>
	<?if(!empty($data->sum)){?>
	<div style="display:inline-block;">
		<table border="1" cellpadding="0" cellspacing="0">
			<tr>
				<th rowspan="2"><a href="<?=url::admin_traffic_cats('','title|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">категория <small title="sort"><?=(@$data->sort[0]=='title'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></th>
				<th rowspan="2"><a href="<?=url::admin_traffic_cats('','traf|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">трафик <small title="sort"><?=(@$data->sort[0]=='traf'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></th>
				<th colspan="2">постов</th>
				<th rowspan="2" style="width:50px;">всего постов</th>
				<th colspan="2">кейвордов</th>
				<th rowspan="2" style="width:50px;">всего кейвордов</th>
				<th colspan="2">постов без картинки</th>
				<th colspan="2">кейвордов без картинки</th>
			</tr>
			<tr>
				<th><a href="<?=url::admin_traffic_cats('','p_top5|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">топ 5<small title="sort"><?=(@$data->sort[0]=='p_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_cats('','p_top100|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">топ 100<small title="sort"><?=(@$data->sort[0]=='p_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_cats('','k_top5|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">топ 5<small title="sort"><?=(@$data->sort[0]=='k_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_cats('','k_top100|'.($sortDirec=='desc'?'asc':'desc'),$data->parent->url)?>">топ 100<small title="sort"><?=(@$data->sort[0]=='k_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<td>топ 5</td>
				<td>топ 100</td>
				<td>топ 5</td>
				<td>топ 100</td>
			</tr>
		<?foreach ($data->sum as $cid=>$v) {?>
			<tr>
				<td><a href="<?=isset($data->childs[$v->cid])?url::admin_traffic_cats('','',$v->cid):url::admin_traffic_posts('','',$v->cid)?>"><?=$v->title?></a></td>
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
