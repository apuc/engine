<?if($data->mes){?>
	<script type="text/javascript">alert('<?=$data->mes?>');</script>
<?}else{?>
	<li><a style="cursor:pointer;"><?=$data->filename?></a><small class="del-icon del-tpl" title="delete"></small></li>
<?}?>