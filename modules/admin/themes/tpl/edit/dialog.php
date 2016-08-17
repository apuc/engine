<style type="text/css">
	.dialog{display: none;}
</style>
<div id="dialog" title="Add file" class="dialog">
	<div>
		<input type="text" placeholder="name..." />
		<input type="button" value="new file"/>
	</div>
	<b>or</b>
	<div>
		<h5>Upload file <small>max: <?=ini_get('upload_max_filesize');?></small></h5>
		<input type="file"/>
	</div>
</div>