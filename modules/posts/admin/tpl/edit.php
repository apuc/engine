<?php
$tpl->title="Add/Edit post. ".NAME;
$tpl->desc="";
$firstParentCat=current($data->category);
$post=$data->post;
?>
<script type="text/javascript" src="<?=HREF?>/files/template/tiny_mce/tinymce.min.js"></script>
<script type="text/javascript" src="<?=HREF?>/files/posts/admin/js/textForm.js"></script>
<script type="text/javascript" src="<?=HREF?>/files/posts/admin/js/addCategories.js"></script>
<?if($data->accessPublish){?> 
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<script type="text/javascript">
	$(document).ready(function(){
		$('a[name=images-form]').click(function(){
			$.get(
				window.location.basepath+'?module=images/admin',
				{act:'edit',pid:'<?=$post->id?>',tbl:'<?=$data->tbl?>'},
				function(html){
					$('#type-selector').html(html);
				}
			);
		});
		$('a[name=keywords-form]').click(function(){
			$.get(
				window.location.basepath+'?module=posts/admin',
				{act:'editKeywords',pid:'<?=$post->id?>',prfxtbl:'<?=$data->prfxtbl?>'},
				function(html){
					$('#type-selector').html(html);
				}
			);
		});
	});
</script>
<div class="breadcrumbs">
	<?if(!empty($post->url)){?><a href="<?=url::post($post->url,$data->prfxtbl)?>"><?=$post->title?></a><?}?>
</div>
<form action="<?=url::post_adminAdd()?>" method="post" enctype="multipart/form-data" class="form-post-edit">
<div class="cols">
	<div class="left-col">
		<?if(!$data->accessSaveNoText){?>
			<p><small><span style="color:red;">&nbsp;*&nbsp; - Required fields</span></small></p>
		<?}?>
		<div>
			<?if(!empty($post->id)){?><small style="float:right">status: <?=$post->published;?></small><?}?>
			<label>
				<span>Title *</span>
				<input type="text" name="title" value="<?=!empty($post->title)?$post->title:''?>" size="255" />
			</label>
			<?if($data->accessEditKeyword){?>
			<label>
				<span>Keyword</span>
				<input type="text" name="keyword" value="<?=!empty($post->keyword->title)?$post->keyword->title:''?>" size="255" />
			</label>
			<?}?>
			<?include $template->inc('edit/foruser.php');?>
			<?include $template->inc('edit/category.php');?>
			<?include $template->inc('edit/editor.php');?>
			<?include $template->inc('edit/params.php');?>
			<label for="sources"><span>Article source&nbsp;<small>(add each source as new line)</small>&nbsp;*&nbsp;</span></label>
			<textarea name="sources"><?=!empty($post->sources)?$post->sources:''?></textarea>
			<?include $template->inc('edit/setsite.php');?>
			<?include $template->inc('edit/themes.php');?>
			<input type="hidden" name="pid" value="<?=!empty($post->id)?$post->id:''?>"/>
			<input type="hidden" name="tbl" value="<?=!empty($data->tbl)?$data->tbl:''?>"/>
			<input type="hidden" name="prfxtbl" value="<?=!empty($data->prfxtbl)?$data->prfxtbl:''?>"/>
			<?if(!empty($post->date)){?><br/><span><small>date:&nbsp;<?=$post->date?></small></span><?}?>
			<?include $template->inc('edit/buttons.php');?>
		</div>
		<?if($data->accessPostAdd){?><span><a href="<?=url::post_adminAdd(@$firstParentCat->url)?>">new post</a></span><?}?>
	</div>
	<div class="right-col">
		<a name="images-form" style="cursor:pointer;">Edit images</a> |
		<a name="keywords-form" style="cursor:pointer;">Edit keywords</a>
		<hr/><br/>
		<div id="type-selector">
			<?=$data->imagesForm?>
		</div>
	</div>
</div>
</form>
