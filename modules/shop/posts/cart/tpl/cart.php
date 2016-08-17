<?php
$posts=$data->posts;
?>
<style type="text/css">
	.cart{}
	.cart table td{padding: 5px 7px}
</style>
<script type="text/javascript">
	$(document).ready(function (){
		$('.cart input[type=number]').each(function (indx,el){
			$(el).change(function(){
				var jqEl=$(this);
				$.post(document.location.basepath+'?module=shop/posts/cart',
					{act:'tocart',gid:jqEl.attr('data-id'),q:jqEl.val(),increment:0}
				);
			});
		});
		$('.cart input[type=button]').each(function (indx,el){
			$(el).click(function(){
				var jqEl=$(this);
				$.post(document.location.basepath+'?module=shop/posts/cart',
					{act:'outcart',gid:jqEl.attr('data-id')},
					function (response){
						if(response=='success'){
							var jqElParent=jqEl.parent('td').parent('tr');
							jqElParent.fadeOut('500',function(){
								//corrent amount
								var jqAmount=$('.cart .amount');
								var jqPrice=jqElParent.children('td').children('.price');
								jqAmount.html(parseInt(jqAmount.html())-parseInt(jqPrice.html()));
								jqElParent.detach();	
							});
						}
					}
				);
			});
		});
	});
</script>
<div class="cols">
	<div class="left-col cart">
		<h1>Cart</h1>
		<h3>Amount: <span class="amount"><?=$data->sum?></span></h3>
		<?if(!empty($posts)){?>
			<table border="0" cellpadding="0" cellspacing="0">
			<?foreach($posts as $p){?>
			<tr>
				<td><a href="<?=url::shop_post($p->url)?>" title="<?=$p->title?>"><?=$p->title?></a></td>
				<td><span class="price"><?=$p->price?></span></td>
				<td><input data-id="<?=$p->id?>" type="number" value="<?=$p->want?>" min="1" style="width:50px;" /></td>
				<td><input data-id="<?=$p->id?>" type="button" value="del"/></td>
			</tr>
			<?}?>
			</table>
			<form action="">
				<input type="Submit" value="Chekout">
			</form>
		<?}?>
	</div>
</div>
