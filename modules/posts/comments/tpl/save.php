<?if(!empty($data->error)){
	if($data->error=='badMail'){
		$data->error='email is wrong';
	}elseif($data->error=='signInError'){
		$data->error='sign in error';
	}elseif($data->error=='textEmpty'){
		$data->error='the text is empty';
	}
	?><span class="warning"><?=$data->error?></span><?}?>
<?if(!empty($data->saved)){?><span class="success"><?=$data->saved?></span><?}?>
<?=$data->form?>
<?=$data->comments?>
