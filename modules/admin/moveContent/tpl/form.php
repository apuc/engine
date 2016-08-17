<?php
$tpl->title="Move content";
$tpl->desc="";
?>
<style>
	.cats {color:#666666;}
	#changeCountForm {margin: 0 0 20px;}
	#changeCountForm input[type="text"] {width:auto; height:30px; line-height:30px;}
	#changeCountForm .button {width:auto; margin-top:0; height:30px; line-height:30px; padding:0 10px; font-size:14px;}
	#processForm {margin-bottom:0;}
	#processForm .button {margin-top:20px; width:auto; padding:0 20px;}
	#processForm .button[disabled] {background:#999; color:#666;}
	#changeCountForm .nomore.button {
		background: red;
	}
	.right-col{margin-top:20px;}
	.form-mvcontent p {color: #293848; font-size: 18px; line-height: 22px; margin-bottom: 20px;}
	.form-mvcontent .title {margin-bottom: 16px;}
	.form-mvcontent form span {color: #607586; text-transform: uppercase;}
	.form-mvcontent form {background: #f8f8f8; border: 1px solid #d1d5dc; padding: 15px 16px 12px;}
	.form-mvcontent input[type="text"] {border: 1px solid #d1d5dc; color: #607586; font-size: 14px; background: #fff; padding: 0 10px; width: 96%; height: 40px; line-height: 40px;}
	.form-mvcontent .right-col input[type="text"] {border: 1px solid #d1d5dc; color: #607586; font-size: 12px; background: #fff; padding: 0; width: 96%; height: 15px;line-height: 15px;}
	.form-mvcontent {margin-bottom: 47px;}
	.form-mvcontent textarea {width: 90%; padding: 10px; font-size: 14px;}
	.form-mvcontent .left-col textarea{min-height: 500px;}
	.form-mvcontent .button-submit{width:100%;text-align: right;}
	.form-mvcontent .button {width: 104px; height: 41px; margin: 5px 0 0; background: #2b3b4e; color: #fff; border: 0; font-size: 18px; line-height: 39px;display: inline-block;}
</style>
<h1>Move content</h1>
<div class="cols">
	<div class="left-col">
		<form action="<?=url::moveContentForm()?>" method="post" class="form-mvcontent" id="changeCountForm">
			<div>
			<select name="siteForCopying">
				<option value="" <?if ($data->selectedSite==''){?>selected<?}?>>Free</option>
				<?foreach($data->sitesWithCopiedContent as $site){?>
					<option value="<?=htmlspecialchars($site->site)?>" <?if ($data->selectedSite==$site->site){?>selected<?}?>><?=$site->site?></option>
				<?}?>
			</select>
			</div><br />
			<?$more=(count($data->posts) < $data->count);?>
			Count <input type="text" value="<?=$data->count?>" name="count"> <input class="button<?=$more ? ' nomore' : ''?>" type="submit" value="OK">
			<?=$more ? ' &nbsp; Not enough free posts' : ''?>
		</form>
		<?if($data->posts){?>
			<form action="<?=url::moveContentProcess()?>" method="post" class="form" id="processForm">
			<?foreach($data->posts as $i=>$post){?>
				<?=$i+1?><input type="checkbox" value="<?=$post->id?>" checked name="pid[]">
				<?=htmlspecialchars($post->title)?>
				<span class="cats">(<?=!empty($post->cats)?htmlspecialchars($post->cats):'no category'?>)</span><br>
			<?}?>
			<input class="button" type="submit" value="Move">
			</form>
			<script>
				$(document).ready(function() {
					$('#processForm').submit(function() {
						$('.button', this).prop('disabled', 1).val('Please Wait...');
					});
				});
			</script>
		<?}else{?>
			Free posts not found.
		<?}?>
	</div>
	<div class="right-col">
		<br />
		<h3>All Posts: <?=$data->allPosts?></h3>
		<h3>Free Posts: <?=$data->totalFreePosts?></h3>
		<?if($data->selectedSite){?><h3>Posts in <?=$data->selectedSite?>: <?=$data->customFreePosts?></h3><?}?>
		<br>
		<?if($data->sitesWithCopiedContent){?>
			<h3>Site:</h3>
			<?foreach($data->sitesWithCopiedContent as $site){?>
				<a target="_blank" href="<?=htmlspecialchars($site->site)?>"><?=htmlspecialchars($site->site)?></a><br>
			<?}?>
		<?}?>
		<br/>
		<?if($data->categories){?>
			<h3>Category:</h3>
			<?foreach($data->categories as $cat){?>
				<?=htmlspecialchars($cat->title)?> (<?=$cat->count?>)<br>
			<?}?>
		<?}?>
	</div>
</div>
