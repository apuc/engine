<?foreach($data->pins as $p){?>
	<div class="model">
		<a><img src="<?=url::imgThumb('250_',$p->imgs[0])?>" title="<?=$p->title?>" alt="<?=$p->title?>"/></a>
		<div class="model-text" style="height: auto;">
			<?=$p->funcPanel?>
			<div class="model-title"><?=$p->title?></div>
			<small><?=date('Y-m-d', strtotime($p->datePublish))?> <img class="author_icon" alt="Author" title="Author" src="/files/template/icons/author_icon.png" />
			<?if(!empty($p->authorMail)){?><i><?=$p->authorName?></i><?}
			else{?><i><?=$p->authorName?></i><?}?>
			</small>
			<small style="float:right;clear:both;">
				<img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
				<img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /> <?=$p->countPhoto?>
			</small>
			<p><?=$p->txt?></p>
		</div>
		<div class="clearfix"></div>
	</div>
<?}?>