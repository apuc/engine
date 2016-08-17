<div class="button-submit">
	<input class="button post-submit-btn" type="submit" name="save" value="Save"/>
	<?if ($data->accessPublish):?>
		<input class="button post-submit-btn" type="submit" name="save" value="Publish">
		<input class="button post-submit-btn" type="submit" name="save" value="Autoposting">
		<?if(!empty($post->id)):?>
			<input class="button post-submit-btn" type="submit" name="save" value="Remake"/>
		<?endif;?>
	<?endif;?>
	<?if($data->accessPublish&&!empty($post->id)):?>
		<input class="button post-submit-btn" type="submit" name="save" value="Accept"/>
	<?endif;?>
</div>