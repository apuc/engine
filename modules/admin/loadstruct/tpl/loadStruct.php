<?php
$tpl->title="Upload struct from ZIP";
?>
<script type="text/javascript">
	$(document).ready(function(){
		$('#setprfx input:button').click(function(){
			var name=$('#setprfx input:text').val();
			if(name){
				$('#setprfx select').append('<option value="'+name+'">'+name+'</option>');
				$('#setprfx select option').prop('selected',false);
				$('#setprfx select option:last-child').prop('selected',true);
			}
		});
	});
</script>
<style type="text/css">
	li>ul{padding-left: 1em;}
</style>
<div><?=$data->message?></div>
<form action="" method="post" enctype="multipart/form-data">
	<div style="font-weight:bold">Pleae add zip arhive of keywords - files:</div><br>
	<?if($data->detectFTPupload){?>
		FTP <b>struct.zip</b> file detected
		<input type="hidden" name="ftpstructfile" value="1"/>
	<?}else{?>
		<input type="file" name="structfile"/>
	<?}?><br>
	<small>max file size:<?=$data->sizelimit?></small><br><br>
	<div id="setprfx">
		<label>
			<b>prefix</b>
			<select name="tblprefix">
				<option<?=empty($data->tblprefix)?' selected=""':''?> value=''>default</option>
			<?foreach ($data->prefixlist as $v) {?>
				<option value="<?=$v?>"<?=$v==$data->tblprefix?' selected=""':''?>><?=$v?></option>
			<?}?>
			</select>
		</label>
		<input type="text"/>
		<input type="button" value="add"/>
		<br/><br/>
	</div>
	<div style="font-weight:bold">Select what we do with repeating keywords:</div>
	<table>
		<tr>
			<td><input name='repeatKeys' value='add' type='radio' checked></td>
			<td>Add category for keyword</td>
		</tr>
		<tr>
			<td><input name='repeatKeys' value='update' type='radio'></td>
			<td>Update category for keyword</td>
		</tr>
		<tr>
			<td><input name='repeatKeys' value='insert' type='radio'></td>
			<td>Insert new keywords</td>
		</tr>
	</table>
	<br/>
	<label><b>Insert keyword as title</b> <input type="checkbox" name="kwastitle" checked="" /></label><br/>
	<label>
		<b>Autoposting</b> <input type="checkbox" name="autoposting" checked=""/>
		<small>Notice: set autoposting config after struct loading</small>
	</label>
	<br/><br/>
	<input class="button" name="submit" type="submit" value="upload"/>
</form>
<?if($data->log){?>
	<div style="display:inline-block;min-width:900px;">
		<div style="float:left;max-width:500px;">
			<label style="font-weight:bold">Posts list:</label><br><br>
			<?=recPosts($data->log,$data->tblprefix)?>
		</div>
		<div style="float:right">
			<label style="font-weight:bold">Category list:</label><br><br>
			<?=recCats($data->log,$data->tblprefix)?>
		</div>
		<div style="clear:both;"></div>
	</div>
<?}?>
<div style="clear:both;"></div>
<?



function recCats($log,$tblprefix){
	if(empty($log->cats)) return;?>
	<ul>
	<?foreach($log->cats as $k=>$v){?>
		<li><a href="<?=url::category($v->url,$tblprefix)?>"><?=$v->name?></a>
			<small><?=$v->status=='insert'?$v->status:"<span style='color:red'>$v->status</span>"?></small>
			<?if(!empty($v->cats)){recCats($v,$tblprefix);}?>
		</li>
	<?}?>
	</ul>
<?}?>
<?function recPosts($log,$tblprefix){?>
	<ul>
		<?if(!empty($log->cats)){
			foreach($log->cats as $k=>$v){?>
			<li><a href="<?=url::category($v->url,$tblprefix)?>"><b><?=$v->name?></b></a>
				<small><?=$v->status=='insert'?$v->status:"<span style='color:red'>$v->status</span>"?></small>
				<?if(!empty($v->cats)){recPosts($v,$tblprefix);}?>
			</li>
			<ul>
			<?if(!empty($v->posts))
				foreach($v->posts as $p){?>
				<li><a href="<?=url::post($p->url,$tblprefix)?>"><?=$p->title?></a>
					<small><?=$p->status=='insert'?$p->status:"<span style='color:red'>$p->status</span>"?>
					<?if(!empty($p->pin)){?><?=$p->pin?', pined ('.$p->pin.')':''?><?}?>
					</small>
				</li>
				<?}?>
			</ul>
			<?}
		}?>
		<?if(!empty($log->posts)){?>
			<li>
				Uncategorized
				<ul>
					<?foreach($log->posts as $p){?>
					<li><a href="<?=url::post($p->url,$tblprefix)?>"><?=$p->title?></a>
						<small><?=$p->status=='insert'?$p->status:"<span style='color:red'>$p->status</span>"?>
						<?if(!empty($p->pin)){?><?=$p->pin?', pined ('.$p->pin.')':''?><?}?>
						</small>
					</li>
					<?}?>
				</ul>
			</li>
		<?}?>
	</ul>
<?}?>
