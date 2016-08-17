<?php
/*
 * Входные двнные находятся в stdClass $data
 * */
$cat=$data->cat;
$posts=$data->posts;
$tpl->title=NAME." main.";
$tpl->desc="Discover our website, interesting stories and beautiful media content.";
?>
<style type="text/css">
	.read-more{display: block;text-align: right;font-style: italic;}
	.index.left-col {
		width: 960px; 
		margin: 0;
	}
	.index.right-col {
		float: right;
		margin-bottom: 30px;
		width: 240px;
	}
</style>
<?if($data->accessPublish){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<div class="cols">
	<div class="left-col index">
		<?if($data->accessPostAdd){?><span><a href="<?=url::shop_post_adminAdd($cat->url)?>">new post</a></span><?}?>
		<div class="index-models">
			<?if(!empty($posts)){
                $i = 0;
				foreach($posts as $p){
                    $i++;
                    $large=in_array($i,array(2,9,16,19));?>
				<div class="model<?=$large ? ' large' : ''?>">
					<?=$p->funcPanel?>
					<a href="<?=url::shop_post($p->url)?>" title="<?=$p->title?>" class="model-link">
						<div class="model-image" style="background-image:url(<?=url::imgThumb($large?'600_':'250_',$p->imgs[0])?>);">
							<img src="<?=url::imgThumb($large?'600_':'250_',$p->imgs[0])?>" title="<?=$p->title?>" alt="<?=$p->title?>"/>
						</div>
						<div class="model-title"><?=$p->title?></div>
					</a>
					<div class="model-text">
						<small>
							<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
						</small>
					</div>
					<?=tplcartButton($p->id);?>
				</div>
				<?}
			}?>
            <div class="clearfix"></div>
			<?=$data->paginator?><small>items:&nbsp<i><?=@$data->count?></i></small>
		</div>
	</div>
	<div class="right-col index">
		<?=$data->subCats?>
		<?=$data->topLevelCats?>
	</div>
</div>
