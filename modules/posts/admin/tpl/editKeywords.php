<fieldset class="keywords-edit">
	<legend>Edit keywords</legend>
	<?foreach ($data->post->keywords as $k) {?>
	<div>
		<input type="checkbox" name="keywords[selected][<?=$k->id?>]" value="<?=$k->id?>" checked=""/>
		<input type="hidden" name="keywords[all][<?=$k->id?>]" value="<?=$k->id?>"/>
		<label for="keywords[<?=$k->id?>]"><?=$k->title?></label>
	</div>
	<?}?>
</fieldset>