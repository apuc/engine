<?php
/*
 * Входные данные находятся в stdClass $data
 * */
$cat=$data->cat;
$tpl->title=$cat->title;
$tpl->desc=$cat->title?"Details of - {$cat->title}.":'';
?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
<?if(@$data->access->publishPost){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<div class="breadcrumbs">
<?foreach ($data->breadCrumbs as $val) {?>
	<a <?=($val->url==$cat->url?'':'href="'.url::category($val->url).'"')?> title="<?=$val->title?>"><?=$val->title?></a>
<?}?>
</div>
<div class="cols">
	<div class="left-col top-level">
        <h1><?=ucfirst($cat->title)?></h1>
		<?if($data->pins){?>
		<div class="top-level-models">
			<?include $template->inc('subCatList/pinposts.php');?>
		</div>
		<?}?>
		<div class="top-level-models">
			<?if(isset($data->access->editNews)){?><span><a href="<?=url::post_adminAdd($cat->url,$data->prfxtbl)?>">new post</a></span><?}?>
			<?include $template->inc('subCatList/listposts.php');?>
			<?include $template->inc('mainList/delposts.php');?>
		</div>
	</div>
	<div class="right-col">
		<?=$data->topLevelCats?>
	</div>
</div>
