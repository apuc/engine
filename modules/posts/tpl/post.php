<?php
$post=$data->post;
$tpl->title="{$post->title}";
$tpl->desc="{$post->title}. Article: ".$post->shortTxt;
?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/gallery.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?=HREF?>/files/post/js/ba-hashchange.js"></script>
<![endif]-->
<?if($data->access->publish){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<div class="cols-post">
	<div class="left-col-post">
		<?if(!empty($post->imgcookie)){?>
			<div class="images">
				<a class="overlay-enable" href="<?=url::img('post',$post->imgcookie->pid,$post->imgcookie->url)?>" data-tbl="<?=$post->tbl?>" data-pid="<?=$post->id?>" data-url="<?=$post->imgcookie->url?>">
					<img src="<?=url::imgThumb('600_',$post->imgcookie->url)?>" alt="<?=$post->imgcookie->title?>" title="<?=$post->imgcookie->title?>"/>
				</a>
			</div>
		<?}?>
		<?include $template->inc('post/slider.php');?>
		<center><?=$data->ads->top?></center>
		<?include $template->inc('post/subscribe.php');?>
		<div class="text">
			<?=$post->funcPanel?>
			<?include $template->inc('post/postbody.php');?>
		</div>
		<?if(!empty($post->prevnext->next)&&!empty($post->prevnext->prev)){?>
		<div class="navv">
			<a href="<?=url::post($post->prevnext->prev->url,$data->prfxtbl)?>" class="previous">
				<strong>Previous</strong>
				<span><?=$post->prevnext->prev->title?></span>
			</a>
			<a href="<?=url::post($post->prevnext->next->url,$data->prfxtbl)?>" class="next">
				<strong>Next</strong>
				<span><?=$post->prevnext->next->title?></span>
			</a>
			<div style="clear:both;"></div>
		</div>
		<?}?>
		<div style="clear:both;"></div>
		<?include $template->inc('post/soc-buttons.php');?>
		<div style="clear:both;"></div>
		<?include $template->inc('post/breadcrumbs.php');?>
		<?if(defined('FACEBOOKPAGE')){?>
		<div class="follow">
			<h4>Funny news, interesting pictures</h4><span>Enjoy with us!</span>
			<div id="fb-root"></div>
			<div align="center" id="fb-follow-bottom" class="fb-like" data-href="https://www.facebook.com/<?=FACEBOOKPAGE?>" data-layout="standard" data-action="like" data-ref="art-bottom" data-show-faces="false" data-share="false" data-width="300"></div>
		</div>
		<?}?>
		<?=$data->comments?>
		<?include $template->inc('post/related.php');?>
	</div>
	<div class="right-col-post">
		<?=$data->ads->right?>
		<?=$data->subCats?>
		<?=$data->topLevelCats?>
		<?include $template->inc('post/seealso.php');?>
	</div>
</div>
<?include $template->inc('post/fb-popup.php');?>
