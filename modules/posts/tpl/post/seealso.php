<?if(!empty($data->otherPosts)){?>
<div class="relRandom">
	<h4>See also</h4>
	<?foreach($data->otherPosts as $rel){?>
		<div>
			<a href="<?=url::post($rel->url,$data->prfxtbl)?>" title="<?=$rel->title?>" class="model-link">
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