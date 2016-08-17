<?if(!empty($data->error)){
	if($data->error=='UnknownError')
		$data->error='Unknown error';
	elseif($data->error=='imageExist')
		$data->error='Image exist';
	elseif($data->error=='notAnImage')
		$data->error='file is not an image';
	?><span class="warning"><?=$data->error?></span>
<?}?>
<?if(!empty($data->message)){
	if($data->message=='saved')
		$data->message='Saved '.$data->id;?>
	<span class="success"><?=$data->message?></span>
<?}?>
<?=$data->html?>
