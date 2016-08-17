<span class="catfunc">
<?if($data->itemID){?>
	<?if($data->accessDel){?><a onclick="return confirm('Are you sure?')" href="<?=url::shop_CatDel($data->itemID)?>"  title="delete"><img width="15" src="<?=HREF?>/files/template/icons/dell.jpg" /></a><?}?>
	<?if($data->accessEdit){?><a href="<?=url::shop_CatEdit($data->itemID)?>" title="edit"><img width="15" src="<?=HREF?>/files/template/icons/edit.jpg" /></a><?}?>
<?}?>
<?if($data->accessEdit){?>&nbsp;<a href="<?=url::shop_CatEdit(0,$data->itemUrl)?>" title="new sub item"><img width="15" src="<?=HREF?>/files/template/icons/add.png" /></a><?}?>
</span>
