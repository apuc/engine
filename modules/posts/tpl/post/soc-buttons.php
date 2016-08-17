<div class="social-buttons">
	<div id="fb-root"></div>
	<div class="fb-like" id="fb-like" style="vertical-align:top;" data-href="<?=url::post($post->url,$data->prfxtbl)?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
	<div id="fb-share-bottom" style="vertical-align:top;" class="fb_share" data-href="<?=url::post($post->url,$data->prfxtbl)?>">Share</div>
	<div class="g-plus" data-action="share" data-annotation="none"></div>
	<a href="https://twitter.com/share" class="twitter-share-button" data-count="none" rel="nofollow"></a>
	<?if(!empty($post->imgs)){?>
	<a rel="nofollow" href="https://www.pinterest.com/pin/create/button/?url=<?=urlencode(url::post($post->url,$data->prfxtbl))?>
		&media=<?=urlencode(url::image($post->imgs[0]->url))?>
		&description=<?=urlencode($post->title)?>"
		data-pin-do="buttonPin"
		data-pin-config="">
		<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" />
	</a>
	<?}?>
	<a rel="nofollow" class="reddit-share">
		<script type="text/javascript" src="//www.redditstatic.com/button/button1.js?newwindow=1"></script>
	</a>
</div>