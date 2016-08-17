<div class="comments-list">
<?if($data->countComments){?>
	<a rel="nofollow" style="display:block;text-align:right;" href="<?=url::commentsPage($data->url)?>">All comments (<?=(int)$data->countComments?>)</a>
<?}?>
<?if(!empty($data->comments)){?>
	<div>
		<?foreach($data->comments as $c){?>
		<div class="comments-text">
			<small><?=$c->authorName?>&nbsp;|&nbsp;<?=$c->date?>&nbsp;</small>
			<?if($c->self){?><a class="comment-del" onclick="commentDel(this)" data-id="<?=$c->id?>"></a><?}?>
			<?if($c->approve){?>
				<p><?=$c->text?></p>
			<?}else{?>
				<i><a rel="nofollow" href="<?=url::commentsPage($data->url)."#{$c->id}"?>">...on verification, click to read...</a></i>
			<?}?>
		</div>
		<?}?>
	</div>
<?}?>
<?if(!empty($data->otherComments)){?>
	<hr/><p>Also discuss</p>
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
<?}?>
</div>