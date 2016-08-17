<?if(!empty($data->themes)){?>
<div style="margin:10px 0;">
	<label for="themes">Theme</label>
	<select name="theme">
		<option value="">default</option>
	<?foreach ($data->themes as $v) {?>
		<option value="<?=$v?>"<?=@$post->theme==$v?' selected=""':''?>><?=$v?></option>
	<?}?>
	</select>
</div>
<?}?>