<?if(!empty($data->seealso)){?>
	<div>
		<div class="title"><h4>See also</h4></div>
		<div class="cross-b">
		<?foreach($data->seealso as $p){?>
			<a href="<?=url::post($p->url)?>" title="<?=$p->title?>">
				<div style="background-image:url('<?=url::imgThumb('600_',$p->imgs[0]->url)?>');">
					<img src="<?=url::imgThumb('150_',$p->imgs[0]->url)?>" title="<?=$p->title?>" alt="<?=$p->title?>"/>
				</div>
				<div><?=$p->title?></div>
			</a>
		<?}?>
		</div>
	</div>
	<div style="clear:both;"></div>
<?}?>