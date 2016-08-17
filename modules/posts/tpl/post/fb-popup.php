<?if(defined('FACEBOOKPAGE')){?>
<script type="application/javascript" src="<?=HREF?>/files/posts/js/popup.js"></script>
<div class="popup-side" style="display:none;">
	<div>
		<a class="close-button close" href="#">&#10005;</a><span>Guys, we put our hearts into this site. Thank you for your visit. Thank you for the inspiration.<br/><small>Join us on <b>Facebook</b></small>
		<div class="fb-like-box" id="fb-follow-asidepopup" data-href="https://www.facebook.com/<?=FACEBOOKPAGE?>" data-width="300" data-height="258" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
		<a class="close" href="#" onclick="openPopupSide('close');return false;">Already, thank you!</a>
	</div>
</div>
<div class="popup" style="display:none;">
	<div>
		<a class="close-button close" href="#">&#10005;</a><span>Guys, we put our hearts into this site. Thank you for your visit. Thank you for the inspiration.<br/><small>Join us on <b>Facebook</b></small>
		<div class="fb-like-box" id="fb-follow-popup" data-href="https://www.facebook.com/<?=FACEBOOKPAGE?>" data-width="548" data-height="258" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false"></div>
		<a style="display:block;" class="close" href="#">Already, thank you!</a>
	</div>
</div>
<?}?>