<span class="catfunc">
<?if($data->itemID){?>
	<?if($data->accessDel){?><a onclick="return confirm('Are you sure?')" href="<?=url::del($data->itemID,$data->prfxtbl)?>" title="delete"><img width="15" src="<?=HREF?>/files/template/icons/del.jpg" /></a><?}?>
	<?if($data->accessEdit){?><a href="<?=url::edit($data->itemID,'',$data->prfxtbl)?>" title="edit"><img width="15" src="<?=HREF?>/files/template/icons/edit.jpg" /></a><?}?>
<?}?>
<?if($data->accessEdit){?>&nbsp;<a href="<?=url::edit(0,$data->itemUrl,$data->prfxtbl)?>" title="new sub item"><img width="15" src="<?=HREF?>/files/template/icons/add.png" /></a><?}?>
</span>
