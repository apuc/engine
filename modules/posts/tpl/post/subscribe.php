<?if(defined('FACEBOOKPAGE')){?>
<ul class="post-soc">
	<li><a rel="nofollow" href="#" class="self">Subscribe</a></li>
	<li><a rel="nofollow" href="#" class="fb" onclick="javascript:window.open('https://www.facebook.com/sharer/sharer.php?u=<?=urlencode(url::post($post->url,$data->prfxtbl))?>', '', 'menubar=no,toolbar=no,resizable=yes,width=600');return false;">Share on Facebook</a></li>
	<li><a rel="nofollow" href="https://twitter.com/share" data-lang="en" target="_blank" class="tw">Twiter</a></li>
</ul>
<div class="clearfix"></div>
<div class="subscribe-post"><span>Guys, we put our hearts into this site. Thank you for your visit. Thank you for the inspiration.
Join us on <b>Facebook</b></span>
	<div id="fb-root"></div>
	<div align="center" class="fb-like" data-href="https://www.facebook.com/<?=FACEBOOKPAGE?>" data-layout="button_count" data-action="like" data-ref="art-bottom" data-show-faces="false" data-share="false"></div>
</div>
<script type="text/javascript">
	//subscribe dialog
	$('.post-soc .self').click(function(){
		$(this).toggleClass('self self-on');
		$('.left-col-post .subscribe-post').toggle(100);
		return false;
	});
</script>
<?}?>