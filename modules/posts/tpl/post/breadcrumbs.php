<?if(!empty($post->cats)){?>
	<div class="breadcrumbs"><span>filed under:</span>
	<?foreach ($post->cats as $val) {?>
		<a href="<?=url::category($val->url,$data->prfxtbl)?>"><?=$val->title?></a>
	<?}?>
	</div>
<?}?>
<div style="clear:both;"></div>