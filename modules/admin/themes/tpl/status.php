<?if(!empty($data->status)){
	if($data->status=='notwritable'){?>
		<div><span style="color:red;">Theme's dir is not writable<br/>chmod 777 <?=$data->dir?></span></div>
	<?}
}?>