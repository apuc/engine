<?php
/*
 * Входные данные находятся в stdClass $data
 * */
$cat=$data->cat;
$posts=$data->posts;
$tpl->title=(!empty($cat->title)?$cat->title:"Fun pics & images").(@$data->page>1?" - Page {$data->page}":'');
$tpl->desc=$cat->title?"Records for category \"{$cat->title}\".":'';
if(count($posts)<3 or @$data->page>1 or @$data->noindex)$tpl->meta='<meta name="robots" content="noindex,follow" />';
if(!count($data->posts)){echo "Nothing Found!";return;}
?>
<?if(isset($data->access->publishPost)){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<?include $template->inc('mainList/breadCrumbs.php');?>
<div class="cols">
	<div class="left-col top-level">
		<?if(!empty($data->pins)){?>
		<div class="top-level-models">
			<?include $template->inc('mainList/pinposts.php');?>
		</div>
		<?}?>
		<div class="top-level-models">
			<?if(isset($data->access->editNews) && !empty($cat->url)){?><span><a href="<?=url::post_adminAdd($cat->url,$data->prfxtbl)?>">new post</a></span><?}?>
			<?include $template->inc('mainList/listposts.php');?>
			<?include $template->inc('mainList/delposts.php');?>
			<?=@$data->paginator?>
			<?if(@$data->count){?>
				<small>items:&nbsp<i><?=@$data->count?></i></small>
			<?}?>
		</div>
	</div>
	<div class="right-col">
		<?=@$data->subCats?>
		<?=@$data->topLevelCats?>
	</div>
</div>
