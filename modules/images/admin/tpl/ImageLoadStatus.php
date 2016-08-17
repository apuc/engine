<?if($data->count){?>
	<img src="<?=HREF?>/files/icons/images/admin/loading.gif" style="border:0;width:20px;" />
	<span><?=$data->count?></span>
<?}else{?>
	<span style="color:green" data-check="done">done</span>
<?}?>
