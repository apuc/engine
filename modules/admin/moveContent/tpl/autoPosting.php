<?php
$tpl->title="AutoPosting";
$tpl->desc="";
?>
<h1>AutoPosting</h1>

<script type="text/javascript">
	jQuery(function($) {
	   var per_day = $('[name=count_per_day]');
	   var at_once = $('[name=count_at_once]');
	   var days = $('#autopost_days');
	   var count = <?=$data->count?>;
	   var update = function() {
			var per_day_val = parseInt(per_day.val());
			var at_once_val = parseInt(at_once.val());
			
			per_day_val = per_day_val >= 0 ? per_day_val : 0;
			at_once_val = at_once_val >= 0 ? at_once_val : 0;
			
			var c = count - at_once_val;
			if (c < 0) {
				c = 0;
			}
			
			if (per_day_val) {
				days.html(Math.ceil(c/per_day_val));
			} else {
				days.html('?');
			}
	   };
	   per_day.keyup(update);
	   at_once.keyup(update);
	   update();
	});
</script>
<form action="<?=url::autoPosting()?>" class="form-mvcontent" method="post">
	<table border="0" cellspacing="10">
		<tbody>
			<tr>
				<td>Сколько постить каждый день</td>
				<td><input type="text" value="<?=$data->per_day?>" name="count_per_day"></td>
			</tr>
			<tr>
				<td>Постов хватит на</td>
				<td><span id="autopost_days"></span> дней</td>
			</tr>
			<tr>
				<td>Сколько постов запостить сейчас</td>
				<td><input type="text" value="0" name="count_at_once"></td>
			</tr>
		</tbody>
	</table>
	
	<input class="button" name="autopost" type="submit" value="OK">
</form>
