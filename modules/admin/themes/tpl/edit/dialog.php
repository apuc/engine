<style type="text/css">
	.dialog{display: none;}
</style>
<div id="dialog" title="Add file or directory" class="dialog">
	<div>
		<input id="dialog_dir_name" type="text" placeholder="directory name..." />
		<input id="dialog_dir_btn" type="button" value="new dir.."/>
	</div>
	<b>or</b>
	<div>
		<input id="dialog_file_name" type="text" placeholder="file name..." />
		<input id="dialog_file_btn" type="button" value="new file"/>
	</div>
	<b>or</b>
	<div>
		<h5>Upload file <small>max: <?=ini_get('upload_max_filesize');?></small></h5>
		<input type="file"/>
	</div>
</div>