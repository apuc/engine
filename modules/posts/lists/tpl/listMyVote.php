<?php
/*
 * Входные двнные находятся в stdClass $data
 * */
$tpl->title="My votes";
$tpl->desc="Choose the most interesting posts and vote";
?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
<script type="text/javascript">
	function updateFBpublish(id,isDone){
		if(isDone||confirm('Are you sure?')){
			$.post(document.location.basepath+'?module=posts/admin&act=updateFBpublish',{pid:id,isDone:isDone});
		}
	}
</script>
<div class="cols">
	<div class="left-col main-list">
		<h1>Vote for posts</h1>
		<div class="models">
			<?if(!empty($data->posts)){
				foreach($data->posts as $p){?>
				<div>
					<div class="model-text">
						<?=$p->funcPanel?>
						<div class="model-title"><a href="<?=url::post($p->url)?>" title="<?=$p->title?>"><?=$p->title?></a></div>
						<small><?=$p->datePublish?>&nbsp;/&nbsp;<img class="author_icon" alt="Author" title="Author" src="/files/template/icons/author_icon.png" />
						<?if(!empty($p->authorMail)){?><a href="<?=url::author($p->user)?>" rel="author"><i><?=$p->authorName?></i></a><?}
						else{?><i><?=$p->authorName?></i><?}?>
						</small>
						<small style="clear:both;float:right;">
							<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
							<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
						</small>
						<p><?=$p->txt?></p>
					</div>
					<div class="read-more"><a href="<?=url::post($p->url)?>" rel="details">read more</a></div>
					<?if($data->FBpublishAcceess){?>
					<div class="like">
						<a href="javascript:void(0);" onclick="updateFBpublish(<?=$p->id?>,0);$(this).parent().parent().fadeOut(500);" title="FBPublish Ban"></a>
						<span style="color:<?=($p->likeSum>=0)?'#90EE90':'#FF7676'?>;"><?=$p->likeSum?></span>
						<a href="javascript:void(0);" onclick="updateFBpublish(<?=$p->id?>,1);$(this).parent().parent().fadeOut(500);" title="FBPublish Done"></a>
					</div>
					<?}?>
					<div class="clear"></div>
				</div>
				<?}
			}?>
			<?=$data->paginator?><small>items:&nbsp<i><?=@$data->count?></i></small>
		</div>
	</div>
	<div class="right-col">
	</div>
</div>