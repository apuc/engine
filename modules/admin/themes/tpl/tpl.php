<?php
$tpl->title=NAME;
$tpl->desc="world cars site!";
?>
<style type="text/css">
	.themes {width: 50%;}
	.themes table{width: 100%}
	.themes h3{margin-bottom: 10px}
	.themes td:first-child{padding: 2px 5px}
	.themes tr:first-child{background-color: #EFFFEF;font-weight: bold;}
	.themes tr:nth-child(2n){background-color: #F5F5F5;}
	.themes input[type="button"]{display: block;margin: auto 0 auto auto;}
	.themes input[type="radio"]{margin: 0 0 0 5px;}
	.warning{display: block;padding: 2px 5px 4px;background: #FF0000; color:#FFFFFF; font-weight: bold; font-size: 1em}
	.themes .icons{display: block;width: 25px;height: 25px;background-repeat: no-repeat;background-image:url('<?=HREF?>/modules/admin/themes/tpl/files/icons/iset.png');}
	.genthemes{margin-top: 20px;}
	.news-theme{width: 45%;float: right;}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		//save form preprocessing
		$('#save').click(function(){
			var jqform=$(this).parent('form');
			var val=$('.themes input:radio:checked').val();
			jqform.children('input[name="settheme"]').val(val);
			jqform.submit();
		});
		//use prethemes form preprocessing
		$('#use').click(function(){
			var jqform=$(this).parent('form');
			var chosen=jqform.children('select').children(':selected').val();
			$('.themes input:radio').each(function(indx,el){
				if($(el).val()==chosen){
					if(!confirm('Theme with same name exists. Set and rename as "'+chosen+'_<?=date('Y-m-d')?>"?')) return;
				}
			});
			jqform.submit();
		});
	});
</script>
<?=isset($data->error)?'<span class="warning">'.$data->error.'</span>':''?>
<div class="news-theme">
	<?include $template->inc('index/newtheme.php');?>
</div>
<div class="themes">
	<h3>Доступные темы</h3>
	<?include $template->inc('index/import.php');?>
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><?=$data->current?><?=!$data->found?'<small style="color:red"> not found</small>':''?></td>
			<?if($data->current!='default'){?>
				<td style="width:20px">
					<a class="icons" href="<?=url::admin_themesEdit($data->current)?>" style="background-position:-335px -21px;"></a>					
				</td>
				<td style="width:20px"></td>
				<td style="width:20px">
					<a class="icons" href="<?=url::admin_themesExport($data->current)?>" style="background-position:-200px -229px;" title="export"></a>
				</td>
			<?}else{?>
				<td style="width:20px"></td>
				<td style="width:20px"></td>
				<td style="width:20px"></td>
			<?}?>
			<td style="width:20px"><input type="radio" value="<?=($data->current=='default')?'':$data->current?>" name="t" checked="" /></td>
		</tr>
	<?foreach ($data->themes as $v) {?>
		<tr>
			<td><?=$v?></td>
			<?if($v=='default'){?>
				<td style="width:20px"></td>
				<td style="width:20px"></td>
				<td style="width:20px"></td>
			<?}else{?>
				<td style="width:20px">				
					<a class="icons" href="<?=url::admin_themesEdit($v)?>" style="background-position:-335px -21px;"></a>
				</td>
				<td style="width:20px">
					<a class="icons" href="<?=url::admin_themesDel($v)?>" style="background-position:-14px -255px;" onclick="return confirm('Are you sure?');"></a>
				</td>
				<td style="width:20px">
					<a class="icons" href="<?=url::admin_themesExport($v)?>" style="background-position:-200px -229px;" title="export"></a>
				</td>
			<?}?>
			<td style="width:20px"><input type="radio" value="<?=$v=='default'?'':$v?>" name="t"/></td>
		</tr>
	<?}?>
	</table>
	<form method="post" action="<?=url::admin_themes()?>">
		<input type="hidden" name="act" value="set"/>
		<input type="hidden" name="settheme" value=""/>
		<input type="button" value="use theme" id="save"/>
	</form>
</div>

