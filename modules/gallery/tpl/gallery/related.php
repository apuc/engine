<?if(!empty($data->relatedImgs)){?>
	<style type="text/css">
		.g-related > a {
			display: inline-block;
			width: 185px;
			overflow: hidden;
			height: 230px;
			margin-right: 10px;
		}
		.g-related img {
			position: absolute;
			left: 50%;
			top: 50%;
			height: auto;
			min-height: 130px;
			width: 100%;
			-webkit-transform: translate(-50%,-50%);
			-ms-transform: translate(-50%,-50%);
			transform: translate(-50%,-50%);
		}
		.g-related a div {
			height: 130px;
			width: 100%;
			overflow: hidden;
			position: relative;
			display: inline-block;
		}
	</style>
	<h2>Related Pictures from <?=$img->title?></h2>
	<br/>
	<div class="g-related">
	<?foreach($data->relatedImgs as $image){
		$title=$image->title.'. '.$image->text;?>
		<a href="<?=url::img($data->tbl,$img->pid,$image->url)?>" title="<?=$title?>">
			<div style="background-image:url('<?=url::imgThumb('250_',$image->url)?>');background-position: center center;background-repeat: no-repeat;background-size: cover;">
				<img style="opacity:0;" src="<?=url::image($image->url)?>" alt="<?=$title?>"/>
			</div>
			<h3><?=$image->text?:$image->title?></h3>
		</a>
	<?}?>
	</div>
<?}?>