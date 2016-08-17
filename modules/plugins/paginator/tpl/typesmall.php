<?if(!empty($data->pages)){?>
	<div class="pagination2">
	<?if($data->page==1){
		if($data->page<$data->allPage){?>
		<ul>
			<li><a href="<?=$data->nav->next?>" rel="prev" title="Previous"><span>Previous</span></a></li>
		</ul>
		<?}
	}else{?>
		<ul>
			<li><a href="<?=$data->nav->first?>" title="First"><span>First</span></a></li>
			<li><a href="<?=$data->nav->prev?>" rel="prev" title="Previous"><span>Previous</span></a></li>
		<?foreach ($data->pages as $i => $p) {
			if($data->page!=$i){?><li><a href="<?=$p?>"><span><?=$i?></span></a></li><?}
			else {?><li><span class="active" title="<?=$i?>"><?=$i?></span></li><?}
		}?>
		<?if($data->page<$data->allPage){?>
			<li><a href="<?=$data->nav->next?>" rel="next" title="Next"><span>Next<span></a></li>
			<li><a href="<?=$data->nav->end?>" title="End"><span>End</span></a></li>
		<?}?>
		</ul>
	<?}?>
	<?if($data->showPagen){?>
	<div class="countOnPage">
		Results on page:
		<select name="pagen" onchange="location.href='<?=$data->firstPage.(strpos($data->firstPage,"?")?"&":'?')?>num='+this.value;">
		<?foreach(array(1,5,10,20,50,100,300,500,1000,5000) as $v){?>
			<option value="<?=$v?>"<?=($data->num==$v?' selected="selected"':'')?>><?=$v?></option>
		<?}?>
		</select>
	</div>
	<?}?>
	</div>
<?}else echo ' ';?>