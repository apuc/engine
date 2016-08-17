<div class="sub-cats-list">
<?if(!empty($data->subCatsPosts)){
	foreach($data->subCatsPosts as $sc){?>
		<h5><?=$sc->title?></h5>
		<?foreach ($sc->posts as $pid) {
			if(!isset($data->posts[$pid])) continue;
			$p=$data->posts[$pid];?>
			<div class="model">
				<a href="<?=url::post($p->url,$data->prfxtbl)?>" title="<?=!empty($p->text)?$p->text:$p->imgs[0]->text?>">
					<div style="background-image:url('<?=url::imgThumb('250_',$p->imgs[0])?>');">
						<img src="<?=empty($p->imgs)?HREF.'/files/images/404.png':url::image($p->imgs[0])?>" alt="<?=$p->title?>" style="opacity:0;"/>
					</div>
				</a>
				<div class="model-text">
					<?=@$p->funcPanel?>
					<div class="model-title"><a href="<?=url::post($p->url,$data->prfxtbl)?>" title="<?=$p->title?>"><?=$p->title?></a></div>
					<small><?=date('Y-m-d', strtotime($p->datePublish?$p->datePublish:$p->date))?></small>
					<small style="float:right;clear:both;">
						<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
						<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
					</small>
					<p><?=strip_tags($p->txt)?></p>
				</div>
				<div class="clearfix"></div>
			</div>
		<?}
	}
}?>
</div>