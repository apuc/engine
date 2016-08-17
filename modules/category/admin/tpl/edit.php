<?php
$item=$data->item;
$parent=$data->parent;
if(!$item->id)
	$tpl->title="Add item into [{$parent->title}]";
else
	$tpl->title="Edit [{$data->item->title}]";
$tpl->desc="";
?>
<style type="text/css">
	.breadcrumbs {margin-top: 12px;margin-bottom: 8px;}
	.breadcrumbs a {background-color: #E6E6E6;color: #607586 !important;font-size: 0.97em;padding: 2px 5px;text-decoration: none;text-transform: uppercase;}
	.breadcrumbs span{text-transform: uppercase;font-weight: bold;color: #607586;}
	.left-col{width: 50%;}
	.form form {background: #f8f8f8; border: 1px solid #d1d5dc; padding: 15px 16px 12px;}
	.form input[type="text"] {border: 1px solid #d1d5dc; color: #607586; font-size: 14px; background: #fff; padding: 0 10px; width: 96%; height: 40px; line-height: 40px;}
	.form input[disabled=""] {color: silver;opacity: 0.6;}
	.form .button {padding: 0 5px;height: 41px; margin: 5px 0 0; background: #2b3b4e; color: #fff; border: 0; font-size: 18px; line-height: 39px;display: inline-block;}
	.success{display: block;padding: 2px 5px 4px;background: #90EE90; color:#FFFFFF; font-weight: bold; font-size: 1em}
	.warning{display: block;padding: 2px 5px 4px;background: #FF0000; color:#FFFFFF; font-weight: bold; font-size: 1em}
</style>
<script type="text/javascript" src="<?=HREF?>/files/posts/admin/js/addCategories.js"></script>
<div class="breadcrumbs">
	<a href="<?=HREF?>">home</a>
	<?if(!empty($parent->url)){?><a href="<?=url::category($parent->url)?>"><?=$parent->title?></a><?}?>
	<?if($item->id){?><a href="<?=url::category($item->url)?>"><?=$item->title?></a><?}
	else{?><a>[new]</a><?}?>
</div>
<div class="cols">
	<div class="left-col">
		<div class="form">
			<form action="<?=url::edit($item->id)?>" method="post">
				<?if($item->id){?>
					<label id="category-inputs" data-add="false">Parent URL: <input class="category-input" type="text" name="parentID" value="<?=$parent->url?>" autocomplete="off"/></label>
					<label for="itemUrl">URL:</label>
					<a style="cursor:pointer;" onclick="if(confirm('This is unsafe, continue?')){$('input[name=itemUrl]').prop('disabled',false);$('input[name=merge]').prop('disabled',false);}">enable</a>
					<label style="float:right;"><small>merge</small> <input type="checkbox" name="merge" disabled=""></label>
					<input disabled="" type="text" name="itemUrl" value='<?=empty($item->url)?'':$item->url?>'/>
				<?}else{?>
					<input type="hidden" name="parentID" value="<?=$parent->url?>"/>
				<?}?>
				<label>Name: <input type="text" name="name" value='<?=empty($item->title)?'':$item->title?>'/></label>
				<label>Show in sidebar: <input type="checkbox" name="view" <?=(isset($item->view) && $item->view==0)?'':' checked="checked"'?>/></label>
				<div>
					<label>Type:
						<select name="subCatList">
							<option<?=(empty($item->subCatList)||$item->subCatList==0)?' selected':''?> value="0">обычная</option>
							<option<?=@$item->subCatList==1?' selected':''?> value="1">список подкатегорий</option>
						</select>
					</label>
				</div>
				<?if(!empty($data->themes)){?>
				<div>
					<label>Theme:
						<select name="theme">
							<option value="">default</option>
						<?foreach ($data->themes as $v) {?>
							<option value="<?=$v?>"<?=@$item->theme==$v?' selected=""':''?>><?=$v?></option>
						<?}?>
						</select>
					</label>
				</div>
				<?}?>
				<input type="hidden" name="itemID" value="<?=$item->id?>"/>
				<div style="text-align:right;">
					<input type="submit" name="edit" class="button" value="<?=!$data->item->id?'Add':'Edit'?>"/>
				</div>
			</form>
			<?if($item->id){?><a href="<?=url::edit(0,$parent->url)?>">add new</a><?}?>
		</div>
	</div>
	<div class="right-col"></div>
</div>
