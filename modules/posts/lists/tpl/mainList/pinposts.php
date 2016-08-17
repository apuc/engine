<?foreach($data->pins as $p){?>
	<div class="model">
			<?=$p->funcPanel?>
			<p><?=str_replace("[[]]",'',$p->txt)?></p>
		<div class="clearfix"></div>
	</div>
<?}?>