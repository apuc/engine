<?php
/*
 * Входные двнные находятся в stdClass $data
 * */
$cat=$data->cat;
$posts=$data->posts;
$tpl->title=NAME." main.";
$tpl->desc="Discover our website, interesting stories and beautiful media content.";
?>
<?if($data->accessPublish){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<div class="cols">
	<div class="left-col index">
		<?if(isset($data->access->editNews)){?><span><a href="<?=url::post_adminAdd($cat->url)?>">new post</a></span><?}?>
		<div class="index-models">
			<?include $template->inc('index/listposts.php');?>
			<?include $template->inc('../../posts/lists/tpl/mainList/delposts.php');?>
			<?=$data->paginator?><small>items:&nbsp<i><?=@$data->count?></i></small>
			<?include $template->inc('index/seealso.php');?>
		</div>
	</div>
	<div class="right-col index">
		<?=$data->subCats?>
		<?=$data->topLevelCats?>
	</div>
</div>
