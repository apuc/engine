<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/jquery-ui.min.js"></script>
<script type="text/javascript">
	$(function() {
		var left = $('[name=leftside]');
		var right = $('[name=rightside]');
		regroup = function() {
			var l, r;
			left.each(function(i, el) {
				if (el.checked == true) {
					l = i;
				}
			});
			right.each(function(i, el) {
				if (el.checked == true) {
					r = i;
				}
			});
			
			left.each(function(i, el) {
				el = $(el);
				if (i <= r) {
					el.css('visibility', 'hidden');
				} else {
					el.css('visibility', 'visible');
				}
			});
			right.each(function(i, el) {
				el = $(el);
				if (i >= l) {
					el.css('visibility', 'hidden');
				} else {
					el.css('visibility', 'visible');
				}
			});
		};
		left.change(regroup);
		right.change(regroup);
		regroup();
		show_diff = function() {
			var url = '<?=url::post_archive_diff()?>';
			url = url.replace(/FROM_PID/, left.filter(':checked').val());
			url = url.replace(/TO_PID/, right.filter(':checked').val());
			
			$.ajax({
				url: url,
				success: function(data) {
					var dialog = $('<div></div>');
					dialog.html(data);
					dialog.dialog({
						title: 'Diff',
						minWidth: 900,
					});
				},
			});
		};
	});
</script>
<style type="text/css">
	@import '/files/posts/admin/css/jquery-ui.css';
	p {margin-top:0}
	ins {color:green;background:#dfd;text-decoration:none}
	del {color:red;background:#fdd;text-decoration:none}
</style>
<div class="form">
<?if(!empty($data->history)){?>
	<h3><?=$data->history[0]->title?></h3>
	<br/>
	<input type="button" value="Compare" onclick="show_diff()" class="button"/>
	<br/>
	<br/>
	<?foreach ($data->history as $k => $row) {?>
	<div>
		<input type="radio" name="leftside"<?=($k == 1 ? ' checked' : '')?> value="<?=$row->indexId?>"/>
		<input type="radio" name="rightside"<?=($k == 0 ? ' checked' : '')?> value="<?=$row->indexId?>"/>
		<?=$row->date?> / <?=$row->name ?: $row->mail?>
	</div>
	<?}?>
	<br/>
	<input type="button" value="Compare" onclick="show_diff()" class="button" />
	<br/>
	<br/>
<?}?>
</div>