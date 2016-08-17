<?if(!empty($posts)){
	$i=0;
	foreach($posts as $p){
		$i++;
		$large=in_array($i,array(2,9,16,19));?>
		<div class="model<?=$large ? ' large' : ''?>">
			<?=$p->funcPanel?>
			<a href="<?=url::post($p->url)?>" title="<?=$p->title?>" class="model-link">
				<div class="model-image" style="background-image:url(<?=url::imgThumb($large?'600_':'250_',$p->imgs[0])?>);">
					<img src="<?=url::imgThumb($large?'600_':'250_',$p->imgs[0])?>" title="<?=$p->title?>" alt="<?=$p->title?>"/>
				</div>
				<div class="model-title"><?=$p->title?></div>
			</a>
			<div class="model-text">
				<small>
					<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
					<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
				</small>
				<small style="float:right;"><img class="author_icon" alt="Author" title="Author" src="/files/template/icons/author_icon.png" />
				<?if(!empty($p->authorMail)){?><i><?=$p->authorName?></i><?}
				else{?><i><?=$p->authorName?></i><?}?>
				</small>
			</div>
		</div>
	<?}
}?>
<div class="clearfix"></div>