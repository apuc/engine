<script type="text/javascript">
	$(document).ready(function(){
		$('#newtheme').click(newTheme);
	});
	function newTheme(){
		var name=prompt("Theme name", "lucky star");
		if(name==null) return;
		var form=$(this).parent('form');
		form.children('input[name=name]').val(name);
		form.submit();
	}
</script>
<form action="<?=url::admin_themesNew()?>" method="post">
	<input type="button" value="Новая тема" id="newtheme"/>
	<input type="hidden" value="" name="name"/>
</form>

<div class="genthemes">
	<script type="text/javascript">
		function reqGenTheme(el){
			$(el).val('...');
			$.post(
				window.location.basepath+'?module=admin/themes&act=genBaseTheme',
				{postCall:1},
				function (data){
					$(el).val('done');
					document.location.href=document.location.href;
				}
			);
		}
	</script>
	<input onclick="if(confirm('Unsaved data will be lost, continue?')) reqGenTheme(this);"	type="button" value="update base theme"/>
</div>
<p>&nbsp;</p>
<div class="prethemes">
	<form method="post" action="<?=url::admin_themes()?>">
	<h3>Предустановленные темы</h3>
	<?if(!empty($data->prethemes)){?>
		<select name="prethemes">
		<?foreach ($data->prethemes as $v) {?>
			<option value="<?=$v?>"><?=$v?></option>
		<?}?>
		</select>
		<input type="hidden" name="act" value="usePreTheme"/>
		<input type="button" value="create theme" id="use"/>
	<?}else{?>none :(<?}?>
	</form>
</div>

