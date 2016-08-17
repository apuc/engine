<br/><label for="post-text"><span>Text&nbsp;*&nbsp;</span></label>
<?if(isset($data->access->editorSaveLinks)){?>
	<label><input type="checkbox" name="editorlinks[save]"/> save links</label>
	<label style="display:none;"><input type="checkbox" name="editorlinks[nofollow]"/> set nofollow</label>
	<script type="text/javascript">
		$('input[name="editorlinks[save]"]').change(function(){
			var nextchkbox=$('input[name="editorlinks[nofollow]"]');
			if($(this).prop('checked')){
				nextchkbox.parent('label').css('display','inline');
				nextchkbox.prop('checked',true);
			}else{
				nextchkbox.prop('checked',false);
				nextchkbox.parent('label').css('display','none');
			}
		});
	</script>
<?}?>
<textarea class="post-text" name="post-text"><?=!empty($post->txt)?$post->txt:''?></textarea><br />