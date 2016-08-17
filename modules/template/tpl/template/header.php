<div class="header">
	<div class="inner">
		<a href="<?=HREF?>"><img src="<?=HREF?>/files/icons/logo.png" height="63" style="padding-left:20px;" alt=""></a>
		<div class="nav">
			<div class="nav-buttons">
				<a href="<?=url::listTop()?>">Popular</a>
				<?=$data->userMenu?>
			</div>
			
			<div class="soc">
				<a rel="nofollow" href="#" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+document.location);return false;" class="fb" target="_blank"></a>
				<a rel="nofollow" href="https://twitter.com/share" data-lang="en" target="_blank" class="tw"></a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				<a rel="nofollow" href="#" class="gp no-margin" onclick="javascript:window.open('https://plus.google.com/share?url='+document.location,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;"></a>
			</div>
		</div>
	</div>
</div>