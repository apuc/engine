<?php
$tpl->title="Add/Edit post. ".NAME;
$tpl->desc="";
$firstParentCat=current($data->category);
$post=$data->post;
?>
<style type="text/css">
	form .form-el-block{margin: 5px 0;}
</style>
<script type="text/javascript" src="<?=HREF?>/files/template/tiny_mce/tinymce.min.js"></script>
<script type="text/javascript" src="<?=HREF?>/modules/shop/posts/admin/files/js/textForm.js"></script>
<script type="text/javascript" src="<?=HREF?>/modules/shop/posts/admin/files/js/addCategories.js"></script>
<div class="breadcrumbs">
	<?if(!empty($post->url)){?><a href="<?=url::shop_post($post->url)?>"><?=$post->title?></a><?}?>
</div>
<form action="<?=url::shop_post_adminAdd()?>" method="post" enctype="multipart/form-data" class="form-post-edit">
<div class="cols">
	<div class="left-col">
		<?if(!$data->accessSaveNoText){?>
			<p><small><span style="color:red;">&nbsp;*&nbsp; - Required fields</span></small></p>
		<?}?>
		<?if(isset($data->message)){
			if(empty($data->message->type)) {$data->message->type='message';}?>
			<p class="<?=$data->message->type?>"><b><?=$data->message->txt?></b></p>
		<?}?>
		<div>
			<?if(!empty($post->id)){?><small style="float:right">status: <?=$post->published;?></small><?}?>
			<label>
				Title *
				<input type="text" name="title" value="<?=!empty($post->title)?$post->title:''?>" size="255" />
			</label>
			<div class="form-el-block">
				<label>
					Price *
					<input type="number" name="price" value="<?=!empty($post->price)?$post->price:0?>" min="0" />
				</label>
			</div>
			<label><span>Category</span></label>
			<ul>
			<?foreach ($data->category as $key=>$val) {?>
				<li id="catshow<?=$key?>">
					<?if($data->accessPinpost){?><input type="radio" name="pincid" value="<?=$val->url?>" <?=$post->pincid==$val->url?'checked="checked"':''?>/><?}?>
					<?=$val->title?>
					<a href="#del_cat" onclick="$('#cat<?=$key?>').remove();$('#catshow<?=$key?>').remove();return false;" title="del" style="color:red;text-decoration:none;">x</a>
				</li>
			<?}?>
			</ul>
			<div id="category-inputs"><input type="text" style="width:50%" class="category-input" name="cat[]" size="255" placeholder="add title or url of category..." autocomplete="off" /></div>
			<label for="post-text"><span>Description&nbsp;*&nbsp;</span></label>
			<textarea class="post-text" name="post-text"><?=!empty($post->txt)?$post->txt:''?></textarea><br />
			<input type="hidden" name="pid" value="<?=!empty($post->id)?$post->id:''?>"/>
			<?foreach ($data->category as $key=>$val) {?>
				<input type="hidden" name="cat[]" value="<?=$val->url?>" id="cat<?=$key?>" />
			<?}?>
			<?if(!empty($post->date)){?><br/><span><small>date:&nbsp;<?=$post->date?></small></span><?}?>
			<div class="button-submit">
				<input class="button post-submit-btn" type="submit" name="save" value="Save"/>
				<?if($data->accessPublish):?>
					<input class="button post-submit-btn" type="submit" name="save" value="Publish">
				<?endif;?>
			</div>
		</div>
		<?if($data->accessPostAdd){?><span><a href="<?=url::shop_post_adminAdd(@$firstParentCat->url)?>">new post</a></span><?}?>
	</div>
	<div class="right-col">
		<div id="type-selector">
			<?=$data->imagesForm?>	
		</div>
	</div>
</div>
</form>
