<html>
<head>
	<script type="application/javascript" src="<?=HREF?>/files/js/jquery-2.1.4.min.js"></script>
</head>
<body>
<style>
	.bar .current{color:#90EE90;text-decoration:underline;}
	.moderatebox{}
	.moderatebox .line{
		margin-bottom:30px;
		border-bottom:1px dashed #BFBFBF;
		clear:both;
	}
	.visual{float:left;}
	.visual span{
		z-index:3;
		position:absolute;
		color:red;
	}
	.visual>div{
		width: 150px;
		margin:0 12px 0 0;
	}
	.visual>div>p{
		font-size:0.6em;
		max-width: 300px;
		margin: 0;
	}
	.visual>div>img{
		height:100px;
		max-width: 300px;
	}
	.active span{display:block;}
	.passive span{display:none;}
	.active img{opacity:0.5}
	.passive img{}
	.imgpopup {
		background-color: #E5E5E5;
		border-radius: 10px 10px 10px 10px;
		box-shadow: 0 0 25px #000000;
		display: none;
		left: 20%;
		padding: 15px;
		position: absolute;
		text-align: left;
		z-index: 9999;
		width: 65%;
	}
	.pagination2 li{display: inline-block; float: left;margin-right: 5px;}
</style>
<script>
	function setApprove(objId){
		var input = $('#input'+objId);
		var visual = $('#visual'+objId);
		if(input.attr('value')!=2){
			input.attr('value','2');
			visual.attr('class','visual active');
		}else{
			input.attr('value','1');
			visual.attr('class','visual passive');
		}
	}
</script>
<div class="bar">
	<table>
		<tr>
			<td><a <?=($data->currentStatus=='-1')?'class="current" ':''?> href="<?=url::imagesModerate()?>&filter[status]=-1">All</a></td>
			<td><a <?=($data->currentStatus=='1')?'class="current" ':''?> href="<?=url::imagesModerate()?>&filter[status]=1">Approved (<?=$data->count[1]?>)</a></td>
			<td><a <?=($data->currentStatus=='2')?'class="current" ':''?>href="<?=url::imagesModerate()?>&filter[status]=2">Not approved (<?=$data->count[2]?>)</a></td>
			<td><a <?=($data->currentStatus=='0')?'class="current" ':''?>href="<?=url::imagesModerate()?>&filter[status]=0">Not defined (<?=$data->count[0]?>)</a></td>
		</tr>
	</table>
</div>
<div class="moderatebox">
	<?=$data->paginator?>
	<form method="post" action="">
	<input type="button" value="submit" onclick="javascript:form.submit();" />
	<div class="line"></div>
	<?
	$i=-1;
	foreach($data->imgs as $img){
		if(!(++$i%7)&&$i) echo '<div class="line"></div>';?>
		<div id="visual<?=$img->id?>" class="visual <?=$img->approve!=2?'passive':'active'?>">
			<input id="input<?=$img->id?>" type="hidden" name="approve[<?=$img->id?>]" value="<?=$img->approve?>" />
			<span>&#10005;</span>
			<div>
				<img id="img<?=$img->id?>" src="<?=url::imgThumb('250_',$img->url)?>" class="" onclick="javascript:setApprove(<?=$img->id?>);"/>
				<p><?=$img->keyword?></p>
			</div>
			<script>
				$('#img<?=$img->id?>').dblclick(function(){
						$('#visual<?=$img->id?>').append('<img id="imgFull<?=$img->id?>" class="imgpopup" src="<?=url::image($img->url)?>" />');
						var popupObj = $('#imgFull<?=$img->id?>');
						popupObj.fadeIn();
						popupObj.click(function(){
								$(this).fadeOut();
								$(this).remove();
							});
					});
			</script>
		</div>
	<?}?>
	<div class="line"></div>
	<input type="button" value="submit" onclick="javascript:form.submit();" />
	</form>
	<?=$data->paginator?>
</div>
</body>
</html>
