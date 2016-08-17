<?if(!$data->imgsWritable){?>
	<div><span style="color:red;">Images dir is not writable<br/>chmod -R 777 <?=$data->dir?></span></div>
<?}