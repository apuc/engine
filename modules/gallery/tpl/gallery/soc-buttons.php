<style type="text/css">
	.social-buttons #fb-share-bottom {
		background: rgba(0, 0, 0, 0) linear-gradient(#4c69ba, #3b55a0) repeat scroll 0 0;
		border: none;
		border-radius: 2px;
		color: #fff;
		cursor: pointer;
		font-weight: bold;
		height: 20px;
		line-height: 20px;
		text-shadow: 0 -1px 0 #354c8c;
		white-space: nowrap;
		display: inline-block;
		font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
		font-size: 11px;
		vertical-align: top;
		padding: 0 8px;
	}
	.social-buttons #fb-share-bottom:hover {
		background: rgba(0, 0, 0, 0) linear-gradient(#5b7bd5, #4864b1) repeat scroll 0 0;
		border-color: #5874c3 #4961a8 #41599f;
	}
	.social-buttons .reddit-share iframe{height: 20px;}
</style>
<div class="social-buttons">
	<div id="fb-root"></div>
	<div class="fb-like" id="fb-like" style="vertical-align:top;" data-href="<?=url::img($data->tbl,$post->id,$img->url)?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
	<div id="fb-share-bottom" style="vertical-align:top;" class="fb_share" data-href="<?=url::img($data->tbl,$post->id,$img->url)?>">Share</div>
	<div class="g-plus" data-action="share" data-annotation="none"></div>
	<a href="https://twitter.com/share" class="twitter-share-button" data-count="none" rel="nofollow"></a>
	<a rel="nofollow" href="https://www.pinterest.com/pin/create/button/?url=<?=urlencode(url::img($data->tbl,$post->id,$img->url))?>
		&media=<?=urlencode(url::image($img->url))?>
		&description=<?=urlencode($img->title)?>"
		data-pin-do="buttonPin"
		data-pin-config="">
		<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" />
	</a>
	<a rel="nofollow" class="reddit-share">
		<script type="text/javascript" src="//www.redditstatic.com/button/button1.js?newwindow=1"></script>
	</a>
</div>