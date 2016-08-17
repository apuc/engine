
<?php
function tplcartButton($gid){?>
	<button class="tocart" data-id="<?=$gid?>">Add to cart</button>
<?}?>
<style type="text/css">
	.tocart{}
</style>
<script type="text/javascript">
	$(document).ready(function (){
		$('.tocart').each(function(indx,el){
			$(el).click(function (){
				var jqEl=$(this);
				$.post(document.location.basepath+'?module=shop/posts/cart',
					{act:'tocart',gid:jqEl.attr('data-id'),increment:1},
					function (response){
						if(response=='success')
							jqEl.html('Added to cart');
						else
							jqEl.html('adding failed');
					}
				);
			});
		});
	});
</script>