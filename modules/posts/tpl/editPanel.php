<?
echo ' ';
$post=$data->post;
if($data->accessEdit||$data->accessPub){?>
<div style="box-shadow: 1px 1px 2px -1px;float: right;padding: 3px 4px 0 0;">
	<?if($data->accessHistory){
		if(!empty($post->has_diff)){?>
		&nbsp;<a href="<?=url::post_diff($post->id)?>" onclick="show_diff(this);return false;">DIFF</a>
		<?}?>
		&nbsp;<a href="<?=url::post_archive($post->id)?>">History</a>&nbsp;
	<?}?>
	<?if($data->accessPub){
        $status=$post->published=='published'?'yes':'no';?>
        <button 
            name="published" 
            data-id="<?=$post->id?>"
            data-prfxtbl="<?=$data->prfxtbl?>"
            class="published-<?=$status?>" 
            value="<?=$status?>"
            style="cursor:pointer;border: 0;background: none;"
            >
            <img title="<?=$post->published?>" src="<?=HREF?>/files/icons/<?=$status=='yes'?'pause.jpg':'play.jpg'?>" style="margin:0 !important;;padding:0 !important;width:14px !important;"/>
        </button>
	<?}elseif($data->accessEdit){?><?=$post->published?><?}?>
	<?if($data->accessEdit){?>
	<a href="<?=url::post_adminEdit($post->id,$data->prfxtbl)?>" title="edit">
		<img style="margin:0 !important;;padding:0 !important;width:14px !important;" src="<?=HREF?>/files/template/icons/edit.jpg" 
	/></a>
	<?}?>
	<?if($data->accessDel){?>
		<a href="<?=url::post_adminDel($post->id,$data->prfxtbl)?>" onclick="return confirm('delete?');" title="delete">
			<img style="margin:0 !important;;padding:0 !important;width:14px !important;" src="<?=HREF?>/files/template/icons/del.jpg" 
		/></a>
		<?if(!empty($post->keyword)){?>
			<a href="http://www.google.com/search?q=<?=urldecode($post->keyword)?>&tbm=isch&sout=1&hl=en&gl=us" target="_blank">
				<img style="margin:0 !important;;padding:0 !important;width:14px !important;" src="<?=HREF?>/files/template/icons/google.png"
			/></a>
		<?}?>
		<input type="checkbox" name="post[]" value="<?=$post->id?>"/>
	<?}?>
</div>
<?}?>
