<?php
$item=$data->item;
$parent=$data->parent;
if(!$item->id)
	$tpl->title="Add item into [{$parent->title}]";
else
	$tpl->title="Edit [{$data->item->title}]";
$tpl->desc="";
?>
<div class="breadcrumbs">
	<a href="<?=HREF?>">home</a>
	<?if(!empty($parent->url)){?><a href="<?=url::shop_category($parent->url)?>">/&nbsp;<?=$parent->title?></a><?}?>
	<?if($item->id){?><a href="<?=url::shop_category($item->url)?>">/&nbsp;<?=$item->title?></a><a>:edit</a><?}
	else{?><a>:add</a><?}?>
</div>
<div class="cols">
	<div class="left-col">
		<div class="form">
			<p class="title"><?=$data->parent->title?></p>
			<form action="<?=url::shop_CatEdit($item->id)?>" method="post">
				<span>Name:</span>&nbsp;<input type="text" name="name" value='<?=empty($item->title)?'':$item->title?>'/>
				<span>Show in sidebar:</span>&nbsp;<input type="checkbox" name="view" <?=(isset($item->view) && $item->view==0)?'':' checked="checked"'?>/>
				<input type="hidden" name="parentID" value="<?=$parent->url?>"/>
				<input type="hidden" name="itemID" value="<?=$item->id?>"/>
				<div class="button-submit">
					<input type="submit" name="edit" class="button" value="<?=!$data->item->id?'Add':'Edit'?>"/>
				</div>
			</form>
			<?if($item->id){?><a href="<?=url::shop_CatEdit(0,$parent->url)?>">add new</a><?}?>
		</div>
	</div>
	<div class="right-col"></div>
</div>
