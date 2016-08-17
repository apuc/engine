<?if(!$data->cacheclear){?>
	<script type="text/javascript">
		$(document).ready(function(){
			//clear cache
			$('#cacheclear').click(function(){
				$(this).html('Please wait...(10-30 second)');
				$.post(
					document.location.basepath+'?module=admin/traffic',
					{act:'getRivals',cacheclear:'1'},
					function(answer){
						$('#cacheclear').html('Cache renewed');
						$('.traffic-rivals').replaceWith(answer);
					}
				);
			});
		});
	</script>
	<style type="text/css">
		.traffic-rivals{margin-top:10px;padding:0 2px 0 2px;width: 300px;max-height: 600px;overflow-y:scroll;border: 1px solid silver;}
	</style>
	<button id="cacheclear">Clear cache</button>
<?}?>
<div class="traffic-rivals">
<div>Total: <?=$data->rivals->count?>, last 1000</div>
<?foreach ($data->rivals->data as $host=>$v) {?>
	<a href="http://<?=$host?>" target="_blank"><?=$host?></a> <small><?=$v?></small>
	<a href="http://www.alexa.com/siteinfo/<?=$host?>" target="_blank"><img src="<?=HREF?>/files/icons/admin/traffic/alexa.png" width="15"/></a><br/>
<?}?>
</div>