<?php
$from = $data->history[1];
$to = $data->history[0];
$diff=$data->diff;
?>
<div class="diff">
	<div class="one">
		<div class="title"><h1><?=$from->title?></h1>
			<small><?=$from->date?>&nbsp;/&nbsp;author:<i><?=$from->name?></i></small>
		</div>
		<div class="text"><?=$from->txt?></div>
	</div>
	<div class="two">
		<div class="title"><h1><?=$to->title?></h1>
		<small><?=$to->date?>&nbsp;/&nbsp;author:<i><?=$to->name?></i></small>
		</div>
		<div class="text"><?=$diff?></div>
	</div>
</div>