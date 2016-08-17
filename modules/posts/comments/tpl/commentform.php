<div class="comments-form">
	<form action="" method="post">
		<div id="formContent">
			<?if(!$data->user->id){?>
				<label for="author">Your email (required):</label>
				<input type="text" name="mail" value="" size="22" tabindex="1" aria-required="true">
				<a href="<?=url::userLogin()?>">Sign in</a>
				<?if($data->auth){?>
				or login with:
				<a href="<?=$data->auth->facebook?>">Facebook</a> 
				<a href="<?=$data->auth->google?>">Google+</a> 
				<a href="<?=$data->auth->twitter?>">Twitter</a>
				<?}?>
			<?}?>
		</div>
		<label for="comment"><small>Type your comment here:</small></label>
		<textarea class="commentText" name="commentText"></textarea>
		<input name="" type="submit" id="submit" value="Send" class="button" style="float:right;" onclick="commentSubmit();return false;">
		<div style="clear:both;"></div>
	</form>
</div>
<script type="text/javascript">
	tinyMCE.EditorManager.init({
		mode : "specific_textareas",
		theme : "modern",
		menubar: false,
		statusbar: false,
		toolbar: 'undo redo | bold italic | bullist numlist',
		plugins : "",
		image_advtab: true,
		editor_selector : "commentText", 
		skin : "lightgray",
		plugin_preview_width : "800",
		plugin_preview_height : "600",
		template_replace_values : {
			username : "Some User"
		}
	});
</script>
