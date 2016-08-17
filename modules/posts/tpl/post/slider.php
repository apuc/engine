<?if(!empty($data->post->imgs)){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/slider/slider.js"></script>
	<div class="gallery">
	<h2><?=!empty($data->keyword)?"{$data->keyword} ":''?><span>Gallery</span></h2>
	<div class="slider-gallery load-disable">
		<div class="sg-wrapper">
		<?
		$page=1; $num=0; $cols=4;
		foreach($data->post->imgs as $img){
			if($num&&!($num%$cols)) $page++;
			$num++;
			$title="$img->title #$num";?>
			<div class="photo slide <?=" page{$page}"?>">
				<div style="background-image:url('<?=url::imgThumb('250_',$img->url)?>');height:100%;background-position: center center;background-repeat: no-repeat;background-size: cover;">
					<a href="<?=url::image($img->url)?>" class="pretty" title="<?=$title?>" target="_blank"></a>
					<a title="<?=$title?>" class="overlay-enable" href="<?=url::img('post',$img->pid,$img->url)?>" data-tbl="<?=$post->tbl?>" data-pid="<?=$post->id?>" data-url="<?=$img->url?>">
						<img style="opacity:0;width:100%;height:100%" src="<?=url::image($img->url)?>" alt="<?=$title?>" title="<?=$img->text?>"/>
						<span class='imagetext'><h4><?=$img->title?></h4></span>
					</a>
				</div>
			</div>
		<?}?>
			<div style="clear:both;"></div>
		</div>
		<div class="sg-nav">
			<a class="slider-prev" title="next slide" href="" style="background: url('<?=HREF?>/files/posts/slider/images/controls.png') no-repeat scroll 0 0;">&lt;</a>
			<a class="slider-next" title="next slide" href="" style="background: url('<?=HREF?>/files/posts/slider/images/controls.png') no-repeat scroll -43px 0;">&gt;</a>
		</div>
		<span id="sg-page-marker" style="display:none;" data-id="1"></span>
		<ul class="sg-pages" data-id="<?=($pages=ceil(count($data->post->imgs)/$cols))?>">
		<?for($i=1;$i<=$pages;$i++){?>
			<li><a id="page<?=$i?>" href="#page<?=$i?>"></a></li>
		<?}?>
		</ul>
	</div>
	<script type="text/javascript">galleryRun();</script>
	</div><br/>
<?}?>