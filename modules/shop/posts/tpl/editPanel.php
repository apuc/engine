<?
echo ' ';
$post=$data->post;
if($data->accessEdit||$data->accessPub){?>
<div class="editPanel">
	<?if($data->accessEdit){?><?=$post->published?><?}?>
	<?if($data->accessEdit){?>
	<a href="<?=url::shop_post_adminEdit($post->id)?>" title="edit"><img width="14" src="<?=HREF?>/files/template/icons/edit.jpg" /></a>
	<?}?>
	<?if($data->accessDel){?>
		<a href="<?=url::shop_post_adminDel($post->id)?>" onclick="return confirm('delete?');" title='delete'><img  width="14" src="<?=HREF?>/files/template/icons/dell.jpg" /></a>
		<?if(!empty($post->keyword)){?>
			<a href="http://www.google.com/search?q=<?=urldecode($post->keyword)?>&tbm=isch&sout=1" target="_blank">
				<img width="14" src="<?=HREF?>/files/template/icons/google.png" />
			</a>
		<?}?>
		<input type="checkbox" name='post[]' value='<?=$post->id?>'>
	<?}?>
</div>
<?}?>
