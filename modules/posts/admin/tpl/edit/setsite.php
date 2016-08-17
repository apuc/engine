<?if(isset($data->access->setSite)){?>
	<br/><label for="site"><span>Site</span></label>
	<?if(!empty($post->siteList)){?>
		<select name="site[]">
			<option value="">new site</option>
		<?foreach ($post->siteList as $v) {?>
			<option <?=$v==$post->site?'selected ':''?>value="<?=$v?>"><?=$v?></option>
		<?}?>
		</select>
		<input style="display:none;" type="text" name="site[]" value="" placeholder="input new site or select"/><br/>
		<script type="text/javascript">
			var form=$('select[name=site\\[\\]]');
			switchSiteFrom(form);
			form.change(function(){
				switchSiteFrom($(this));
			});
			function switchSiteFrom(form){
				var val=form.children('option').filter(':selected').val();
				if(val==''){
					$('input[name=site\\[\\]]').css('display','inline-block');
				}else{
					$('input[name=site\\[\\]]').css('display','none');
				}
			}
		</script>
	<?}else{?>
		<input type="text" name="site[]" value="" placeholder="input new site"/><br/>
	<?}?>
<?}?>
<?if(isset($data->access->chooseSite)){?>
	<br/><label for="site"><span>Site</span></label>
	<select name="site[]">
		<option value=""></option>
	<?foreach ($post->siteList as $v) {?>
		<option <?=$v==$post->site?'selected ':''?>value="<?=$v?>"><?=$v?></option>
	<?}?>
	</select>
<?}?>