<?php
$tpl->title="admin/traffic - ".NAME;
$tpl->desc="";
$sortDirec=strtolower($data->sort[1]);
?>
<script type="text/javascript">
	$(document).ready(function(){
		$('input[name="del_all"]').change(function(){
			var chkval=$(this).prop('checked');
			var eldel=$('input[name="del\[\]"]').each(function(indx,el){
				$(el).prop('checked',chkval);
			});
		});
	});
	function delPosts(){
		if(!confirm('Are you sure?')) return;
		var pids=[];
		var eldel=$('input[name="del\[\]"]:checked').each(function(indx,el){
			pids.push(parseInt($(el).attr('data-id')));
		});
		if(!pids[0]){
			return;
		}
		$.post(
			document.location.basepath+'?module=posts/admin',
			{act:'delPosts',pids:pids},
			function(answer){
				if(answer.trim()=='done')
					document.location.href=document.location;
				else
					alert('Delete posts fail');
			}
		);
	}
</script>
<style type="text/css">
	.traffic-data table th,.traffic-data table td{padding: 2px 4px}
	.traffic-data table tr:nth-child(2n+3){
		background-color: #ECECEC;
	}
	.traffic-data table th a{text-decoration: none;color: black;}
</style>
<?include $template->inc('index/panel.php');?>
<div class="traffic-data">
	<div>
		<?if(!empty($data->cat->url)){?>
			посты категории: <a href="<?=url::admin_traffic_cats('','',$data->cat->url)?>"><?=$data->cat->title?></a>
		<?}?>
	</div>
	<?if(!empty($data->posts)){?>
	<div style="display:inline-block;">
		<table border="1" cellpadding="0" cellspacing="0">
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">по датам</th>
				<th rowspan="2">пост (pid)</th>
				<th rowspan="2" title="google images serp"><img src="<?=HREF?>/modules/admin/traffic/tpl/files/icons/google_icon.png" width="20"/></th>
				<th rowspan="2"><a href="<?=url::admin_traffic_posts("",'traf|'.($sortDirec=='desc'?'asc':'desc'),$data->cat->url)?>">трафик <small title="sort"><?=(@$data->sort[0]=='traf'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></th>
				<th colspan="2">замена кейвордов</th>
				<th rowspan="2" style="width:50px;">всего кейвордов</th>
				<th colspan="2">кейвордов с картинками из топа</th>
				<th rowspan="2">удалить</th>
			</tr>
			<tr>
				<th><a href="<?=url::admin_traffic_posts("",'k_top5|'.($sortDirec=='desc'?'asc':'desc'),$data->cat->url)?>">топ 5<small title="sort"><?=(@$data->sort[0]=='k_top5'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<th><a href="<?=url::admin_traffic_posts("",'k_top100|'.($sortDirec=='desc'?'asc':'desc'),$data->cat->url)?>">топ 100<small title="sort"><?=(@$data->sort[0]=='k_top100'&&$sortDirec=='desc'?'&#9650;':'&#9660;')?></small></a></th>
				<td>топ 5</td>
				<td>топ 100</td>
			</tr>
		<?
		$i=0;
		foreach ($data->posts as $v) {
			$i++;?>
			<tr>
				<td><?=$i?></td>
				<td><a href="<?=url::admin_traffic_itemPost($v->pid).'&start='.date('Y-m-d',strtotime('-1 week',strtotime($data->date)))?>"><?=$data->date?></a></td>
				<td>
					<a href="<?=url::post($v->url)?>" title="go to post" target="_blank"><?=$v->pid?></a>
					<?=empty($v->postExists)?' <span style="color:red">удален</span>':''?>
				</td>
				<td><?=(!empty($v->ktitle)?'<a target="_blank" href="http://www.google.com/search?q='.urlencode($v->ktitle).'&tbm=isch&sout=1&hl=en">'.$v->ktitle.'</a>':'')?></td>
				<td><?=@(int)$v->traf?></td>
				<td><?=$v->k_top5?></td>
				<td><?=$v->k_top100?></td>
				<td><?=@$v->kcount?></td>
				<td><?=$v->tbnMatchTop5?></td>
				<td><?=$v->tbnMatchTop100?></td>
				<td>
					<a href="<?=url::post_adminDel($v->pid)?>" target="_blank" style="color:red;text-decoration:none;" onclick="return confirm('Are you sure?');">&#10005;</a>
					<input type="checkbox" name="del[]" data-id="<?=$v->pid?>"/>
				</td>
			</tr>
		<?}?>
		</table>
		<div style="text-align:right;">
			<label>выбрать все <input type="checkbox" name="del_all"/></label>
			<input type="button" onclick="delPosts()" value="Удалить"/>
		</div>
		<br/>
		<?=$data->paginator?>
	</div>
	<?}?>
</div>