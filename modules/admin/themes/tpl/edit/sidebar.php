<div id="themefiles">
<h2><?=$data->theme?></h2>
<?=listRec($data->dir)?>
<?function listRec($list){?>
	<ul>
		<?foreach ($list as $key => $v) {
			if(is_array($v)){?>
				<li<?=!count($v)?' style="color:#CBCBCB;"':''?>>
					<span><?=$key?></span><small class="del-icon del-tpl" title="delete"></small>
					<small class="new-file-icon newfile-tpl" title="new file"></small>
					<?listRec($v)?>
				</li>
			<?}else{?>
				<li><a style="cursor:pointer;"><?=$key?></a><small class="del-icon del-tpl" title="delete"></small></li><?}
		}?>
	</ul>
<?}?>
</div>
<?php
function stampToDateFile($file){
	$extension = new SplFileInfo($file);
	if($extension->getExtension() > 9){
		$new = substr($file, 0, -10);
		$new .= date('Y-m-d ',$extension->getExtension());
		return $new;
	}
	else {
		return $file;
	}
}
function prn($content)
{
	echo '<pre style="background: lightgray; border: 1px solid black; padding: 2px">';
	print_r($content);
	echo '</pre>';
}
?>