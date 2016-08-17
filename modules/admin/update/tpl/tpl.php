<?php
$tpl->title='Updates - '.NAME;
$tpl->desc="";
?>
<?if($data->noPerms){?><h1 style="color:red">Set permission: chmod -R 0777 <?=$data->noPerms?></h1><?}?>
<div class="form">
<div id='answer'></div>
<script>
function update(url,post){
	$('#answer').html('...');
	$.post(
		url,
		post,
		function (data){
			$('#answer').html(data);
		}
	);
}
function ask(url,post){
	if(confirm('Are you sure you want update every post?')){
		update(url,post)
	}
}
</script>

<input class="button" 
	onclick="update('<?=HREF?>/?module=category',{'act':'updateCount','tbl':'post','cats':'all'})"; 
	style="width:300px !important" name="recount" type="button" 
	value="recount posts in categories"
/>
<br/>

<input class="button" 
	onclick="update('<?=HREF?>/?module=posts/admin&act=imgRecountAll',{})"; 
	style="width:300px !important" name="recount_imgs" type="button" 
	value="recount images in posts"
/>
<br/>

<input class="button" 
	onclick="ask('<?=HREF?>/?module=posts/admin&act=updateDates',{})"; 
	style="width:300px !important" name="recount_imgs" type="button" 
	value="make post date in 4 previos weeks"
/>
<br/>

</div>

avaiable: <b><?=$data->count?></b> updates
<form class="form" method="post" action="<?=url::update()?>">
<div>
	<input type="hidden" name="act" value="applyUpdates"/>
	<input type="hidden" name="point" value="<?=$data->point?>"/>
	<input type="submit" value="update" class="button"/>
</div>
</form>
