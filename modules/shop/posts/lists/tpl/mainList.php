<?php
/*
 * Входные двнные находятся в stdClass $data
 * */
$cat=$data->cat;
$posts=$data->posts;
$forDesc=$data->forDesc;
$tpl->title=$cat->title.($data->page>1?" - Page {$data->page}":'');
$tpl->desc=$cat->title?"Records for category \"{$cat->title}\".":'';
$tpl->desc.=empty($forDesc)?'':" Latest:  \"".implode('","',$forDesc)."\"";
require __DIR__.'/../tpl/tocart.php';
?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
<div class="breadcrumbs">
<?foreach ($data->breadCrumbs as $val) {?>
	<a <?=($val->url==$cat->url?'':'href="'.url::shop_category($val->url).'"')?> title="<?=$val->title?>"><?=$val->title?></a>
<?}?>
</div>
<div class="cols">
	<div class="left-col main-list">
		<h1><?=$data->cat->title?></h1>
		<?if($data->pins){?>
		<div class="models">
			<?foreach($data->pins as $p){?>
				<div>
					<div class="model-text">
						<?=$p->funcPanel?>
						<div class="model-title"><?=$p->title?></div>
						<small><?=$p->datePublish?></small>
						<small style="clear:both;float:right;">
							<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
							<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
						</small>
						<p><?=$p->txt?></p>
					</div>
				</div>
			<?}?>
		</div>
		<?}?>
		<div class="models">
			<?if($data->accessPostAdd){?><span><a href="<?=url::shop_post_adminAdd($cat->url)?>">new post</a></span><?}?>
			<?if(!empty($posts)){
				foreach($posts as $p){?>
				<div>
					<div class="model-text">
						<?=$p->funcPanel?>
						<div class="model-title"><a href="<?=url::shop_post($p->url)?>" title="<?=$p->title?>"><?=$p->title?></a></div>
						<small><?=$p->datePublish?></small>
						<small style="clear:both;float:right;">
							<img class="views_icon" alt="Views" title="Views" src="<?=HREF?>/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
							<img class="count_photo_icon" alt="Count photos" title="Count photos" src="<?=HREF?>/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
						</small>
						<p><?=$p->txt?></p>
					</div>
					<?=tplcartButton($p->id);?>
				</div>
				<?}
			}?>
			<?=$data->paginator?><small>items:&nbsp<i><?=@$data->count?></i></small>
		</div>
	</div>
	<div class="right-col">
		<?=$data->subCats?>
		<?=$data->topLevelCats?>
	</div>
</div>
