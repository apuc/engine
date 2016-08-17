<?
$comments=$data->comments;
$post=$data->post;
$tpl->title="$post->title - Related comments - Knowledge of users. ".NAME;
$tpl->desc="This list of topics for '$post->title'";
?>
<div class="cols">
	<div class="left-col comments">
		<div class="comments-list">
			Comments <strong><?=$data->countComments?></strong>
			<h2><a href="<?=url::post($post->url)?>"><?=$post->title?></a></h2><br/>
			<div>
				<?foreach($comments as $c){?>
				<div class="comments-text" name="<?=$c->id?>">
					<small><?=$c->authorName?>&nbsp;|&nbsp;<?=$c->date?>&nbsp;</small>
					<p><?=$c->text?></p>
				</div>
				<?}?>
			</div>
			<?=$data->paginator?>
		</div>
	</div>
	<div class="right-col comments">
		<?if(!empty($data->otherComments)){?>
		<div class="comments-list">
			<p>Also discuss</p>
			<div class="comments-other">
				<?foreach($data->otherComments as $c){?>
				<div class="comments-text">
					<span><?=$c->title?></span><br/>
					<small><?=$c->authorName?>&nbsp;|&nbsp;<?=$c->date?>&nbsp;</small>
					<?if($c->approve){?>
						<p><?=$c->text?></p>
					<?}else{?>
						<i><a rel="nofollow" href="<?=url::commentsPage($c->url)."#{$c->id}"?>">...on verification, click to read...</a></i>
					<?}?>
				</div>
				<?}?>
			</div>
		</div>
		<?}?>
	</div>
</div>
