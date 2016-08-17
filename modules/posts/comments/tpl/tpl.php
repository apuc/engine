<script type="text/javascript" src="<?=HREF?>/files/tiny_mce/tinymce.min.js"></script>
<script type="text/javascript">
	function commentSubmit(){
		var text=tinyMCE.get('commentText').getContent();
		delete tinyMCE.editors['commentText'];
		var data = {
				mail: $('.comments form [name="mail"]').val(),
				pid: '<?=$data->pid?>',
				prfxtbl: '<?=$data->prfxtbl?>',
				text: text,
			};
		
		$('.comments .comments-inner').html('<img src="'+window.location.basepath+'modules/posts/comments/tpl/files/icons/progress.gif" style="width:32px;" />');
		$.post('<?=url::commentSave()?>',data,
			function (serverAnswer){
				$('.comments .comments-inner').html(serverAnswer);
			}
		)
	}
	function commentDel(el){
		var container=$(el).parent('.comments-text');
		var id=$(el).attr('data-id');
		$.post(
			document.location.basepath+'?module=posts/comments',
			{act:'del',id:id},
			function(answer){
				if(answer=='done')
					container.fadeOut(300);
			}
		);
	}
</script>
<div class="comments">
	<strong>Add a comment:</strong>
	<div class="comments-inner">
		<?=$data->form?>
		<?=$data->comments?>
	</div>
</div>