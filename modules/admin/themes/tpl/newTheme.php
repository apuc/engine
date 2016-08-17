<style type="text/css">
	.warning{display: block;padding: 2px 5px 4px;background: #FF0000; color:#FFFFFF; font-weight: bold; font-size: 1em}
</style>
<?=empty($data->mes)?'':'<span class="warning">'.$data->mes.'</span>'?>
<?=$data->html?>