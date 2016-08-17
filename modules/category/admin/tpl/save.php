<?
$mes=array(
	'parentNotExists'=>'Parent is not exists',
	'usuccess'=>'Update successful',
	'ufail'=>'Update failed',
	'isuccess'=>'Insert successful',
	'ifail'=>'Insert failed',
	'nameExists'=>'Name already exists',
	'urlEmpty'=>'Url cat not be empty',
	'urlExists'=>'Url exists',
	'mergeComplete'=>'notice: merge complete',
	'mergeFail'=>'merge fail',
);
?>
<?if(!empty($data->success)){?><span class="success"><?=$mes[$data->success]?></span><?}?>
<?if(!empty($data->warning)){?><span class="warning"><?=$mes[$data->warning]?></span><?}?>
<?=$data->html?>