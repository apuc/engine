<?php
$post=$data->post;
$tpl->title="{$post->title}";
$tpl->desc="{$post->title}. Article: ".$post->shortTxt;
include $template->inc('post/tocart.php');
?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/gallery.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/posts/slider/slider.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?=HREF?>/files/posts/js/ba-hashchange.js"></script>
<![endif]-->
<?if($data->access->publish){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<style>
	@import '/files/posts/slider/slider.css';
</style>
<div class="cols-post">
	<div class="left-col-post">
		<?if(isset($data->post->imgs)){?>
			<div class="gallery">
			<div class="slider-gallery load-disable">
				<div class="sg-wrapper">
				<?
				$page=1; $num=0; $cols=4;
				foreach($data->post->imgs as $img){
					if($num&&!($num%$cols)) $page++;
					$num++;
					$title="$img->title #$num";?>
					<div class="photo slide <?=" page{$page}"?>">
						<div style="background-image:url('<?=url::imgThumb('250_',$img->url)?>');height:100%;background-position: center center;background-repeat: no-repeat;background-size: cover;">
							<a href="<?=url::image($img->url)?>" class="pretty" title="<?=$title?>" target="_blank"></a>
							<a title="<?=$title?>" class="overlay-enable" href="<?=url::img('post',$img->pid,$img->url)?>" data-tbl="<?=$post->tbl?>" data-pid="<?=$post->id?>" data-url="<?=$img->url?>">
								<img style="opacity:0;width:100%;height:100%" src="<?=url::image($img->url)?>" alt="<?=$title?>" title="<?=$img->text?>"/>
								<span class='imagetext'><h4><?=$img->title?></h4></span>
							</a>
						</div>
					</div>
				<?}?>
					<div style="clear:both;"></div>
				</div>
				<div class="sg-nav">
					<a class="slider-prev" title="next slide" href="">&lt;</a>
					<a class="slider-next" title="next slide" href="">&gt;</a>
				</div>
				<span id="sg-page-marker" style="display:none;" data-id="1"></span>
				<ul class="sg-pages" data-id="<?=($pages=ceil(count($data->post->imgs)/$cols))?>">
				<?for($i=1;$i<=$pages;$i++){?>
					<li><a id="page<?=$i?>" href="#page<?=$i?>"></a></li>
				<?}?>
				</ul>
			</div>
			<script type="text/javascript">galleryRun();</script>
			</div><br/>
		<?}?>
		<div class="text">
			<h2><?=$post->price?></h2>
			<?=tplcartButton($post->id);?>
			<?=$post->funcPanel?>
			<div class="title"><h1><?=$post->title?></h1></div>
			<small><?=$post->datePublish?></small>
			<small style="float:right;">
				views&nbsp;<span class="tooltip"><?=$post->statViews?><em>Total views:&nbsp;<?=$post->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$post->statViewsShort?><em>Views for 7 days:&nbsp;<?=$post->statViewsShort?><i></i></em></span>
			</small>
			<?=$post->stxt?>
			<?if(empty($post->txt)){?>
				<?
				$img=$data->post->imgs[5];
				if(!empty($img)){?>
					<a href="<?=url::img('post',$img->pid,$img->url)?>">
						<img src='<?=url::image($img->url)?>' border=0 width=640>
					</a><br>
					<?=$img->text?>
					<br><br>
				<?}?>
				<?
				$img=$data->post->imgs[6];
				if(!empty($img)){?>
					<a href="<?=url::img('post',$img->pid,$img->url)?>">
						<img src='<?=url::image($img->url)?>' border=0 width=640>
					</a><br>
					<?=$img->text?>
					<br><br>
				<?}?>
				<?
				$img=$data->post->imgs[7];
				if(!empty($img)){?>
					<a href="<?=url::img('post',$img->pid,$img->url)?>">
						<img src='<?=url::image($img->url)?>' border=0 width=640>
					</a><br>
					<?=$img->text?>
				<?}?>
			<?}?>	
		</div>
		<div style="clear:both;"></div>
		<div class="social-buttons">
			<div id="fb-root"></div>
			<div class="fb-like" id="fb-like" style="vertical-align:top;" data-href="<?=url::shop_post($post->url)?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
			<div id="fb-share-bottom" style="vertical-align:top;" class="fb_share" data-href="<?=url::shop_post($post->url)?>">Share</div>
			<div class="g-plus" data-action="share" data-annotation="none"></div>
			<a href="https://twitter.com/share" class="twitter-share-button" data-count="none" rel="nofollow"></a>
		</div>
		<div style="clear:both;"></div>
		<?if(!empty($post->cats)){?>
			<div class="breadcrumbs"><span>filed under:</span>
			<?foreach ($post->cats as $val) {?>
				<a href="<?=url::shop_category($val->url)?>"><?=$val->title?></a>
			<?}?>
			</div>
		<?}?>
		<div style="clear:both;"></div>
		<?if(defined('FACEBOOKPAGE')){?>
		<div class="follow">
			<h4>Funny news, interesting pictures</h4><span>Enjoy with us!</span>
			<div id="fb-root"></div>
			<div align="center" id="fb-follow-bottom" class="fb-like" data-href="https://www.facebook.com/<?=FACEBOOKPAGE?>" data-layout="standard" data-action="like" data-ref="art-bottom" data-show-faces="false" data-share="false" data-width="300"></div>
		</div>
		<?}?>
		<?if(!empty($data->related)){?>
		<div class="news2">
			<h4>Related posts</h4>
			<?foreach($data->related as $rel){?>
				<div>
					<a href="<?=url::shop_post($rel->url)?>" title="<?=$rel->title?>" class="model-link">

						<div class="model-image" style="float:left;">
							<div style="background-image:url('<?=url::imgThumb('250_',$rel->imgs[0]->url)?>');background-position: center center;background-repeat: no-repeat;background-size: cover;">
								<img style="opacity:0;" src="<?=url::image($rel->imgs[0]->url)?>" title="<?=$rel->imgs[0]->title?>" alt="<?=$rel->imgs[0]->title?>"/>
							</div>
						</div>
						<p class="model-title"><?=$rel->title?></p>
					</a>
					<span class="date"><small><?=$rel->date?></small></span>
					<p class="rel-post-text"><?=$rel->txt?></p>
					<div style="clear:both;"></div>
				</div>
			<?}?>
		</div>
		<?}?>
	</div>
	<div class="right-col-post">
		<?=$data->subCats?>
		<?=$data->topLevelCats?>
		<?if(!empty($data->otherPosts)){?>
		<div class="relRandom">
			<h4>See also</h4>
			<?foreach($data->otherPosts as $rel){?>
				<div>
					<a href="<?=url::shop_post($rel->url)?>" title="<?=$rel->title?>" class="model-link">
						<div class="model-image" style="float:left;">
							<div style="margin-right:20px;background-image:url('<?=url::imgThumb('250_',$rel->imgs[0]->url)?>');background-position: center center;background-repeat: no-repeat;background-size: cover;">
								<img style="opacity:0;" src="<?=url::image($rel->imgs[0]->url)?>" title="<?=$rel->imgs[0]->title?>" alt="<?=$rel->imgs[0]->title?>"/>
							</div>
						</div>
						<p class="model-title"><?=$rel->title?></p>
					</a>
					<span class="date"><small><?=$rel->date?></small></span>
					<p class="rel-post-text"><?=$rel->txt?></p>
					<div style="clear:both;"></div>
				</div>
			<?}?>
		</div>
		<?}?>
	</div>
</div>
