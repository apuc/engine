<label for="category"><span>Category</span></label>
<ul>
<?if($data->accessPinpost){?><li><label><input type="radio" name="pincid" value="" <?=!empty($post->pincid)?'':'checked="checked"'?>/> NOT PIN</label></li><?}?>
<?foreach ($data->category as $key=>$val) {?>
	<li id="catshow<?=$key?>">
		<?if($data->accessPinpost){?><input type="radio" name="pincid" value="<?=$val->url?>" <?=@$post->pincid==$val->url?'checked="checked"':''?>/><?}?>
		<?=$val->title?>
		<a href="#del_cat" onclick="$('#cat<?=$key?>').remove();$('#catshow<?=$key?>').remove();return false;" title="del" style="color:red;text-decoration:none;">x</a>
		<input type="hidden" name="cat[]" value="<?=$val->url?>" id="cat<?=$key?>" />
	</li>
<?}?>
</ul>
<div id="category-inputs"><input type="text" style="width:50%" class="category-input" name="cat[]" size="255" placeholder="add title or url of category..." autocomplete="off"/></div>