<?if(!empty($posts)){
	foreach($posts as $p){?>
	<div class="model">
		<a href="<?=url::post($p->url,$data->prfxtbl)?>" title="<?=!empty($p->text)?$p->text:$p->imgs[0]->text?>">
			<div style="float:left;width:186px;background-image:url('<?=url::imgThumb('250_',$p->imgs[0])?>');background-position: center center;background-repeat: no-repeat;background-size: cover;">
				<img src="<?=empty($p->imgs)?HREF.'/files/images/404.png':url::image($p->imgs[0])?>"
					title="<?=$p->imgs[0]->text?>" alt="<?=$p->title?>"
					style="opacity:0;" 
				/>
			</div>
		</a>
		<div class="model-text">
			<?=@$p->funcPanel?>
			<div class="title"><a href="<?=url::post($p->url,$data->prfxtbl)?>" title="<?=$p->title?>"><?=$p->title?></a></div>
			<small><?=date('Y-m-d', strtotime($p->datePublish?$p->datePublish:$p->date))?> <img class="author_icon" alt="Author" title="Author" src="/files/template/icons/author_icon.png" />
				<?if(!empty($p->authorMail)){?><i><?=$p->authorName?></i><?}
				else{?><i><?=$p->authorName?></i><?}?>
			</small>
			<small style="float:right;clear:both;">
				<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" /> <span class="tooltip"><?=$p->statViews?><em>Total views: <?=$p->statViews?><i></i></em></span> / <span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days: <?=$p->statViewsShort?><i></i></em></span>
				<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
			</small>
			<p><?=strip_tags($p->txt)?></p>
		</div>
		<div class="clearfix"></div>
	</div>
	<?}
}?>