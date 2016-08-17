<?if($data->samelevel&&$data->samelevel->cats){?>
<div class="toplevel">
	<h4>
		<?=$data->samelevel->baseCat->title?> 
		<?if($data->edit){?><a href="<?=url::edit(0,'',$data->prfxtbl)?>">add</a><?}?>
	</h4>
	<ul>
	<?foreach($data->samelevel->cats as $val){?>
		<li>
			<?if($data->samelevel->current==$val->url){?>
				<?=$val->title?>&nbsp;<small title="count of posts">(<?=$val->count?>)</small>&nbsp;
			<?}else{?>
				<a href="<?=url::category($val,$data->prfxtbl)?>"><?=$val->title?></a>&nbsp;<small title="count of posts">(<?=$val->count?>)</small>&nbsp;
			<?}?>
			<?if(!empty($val->funcPanel)){?><?=$val->funcPanel?><?}?>
		</li>
	<?}?>
	</ul>
</div> 
<?}?>
<div class="toplevel">
<?if(!empty($data->cats)){?>
	<h4>
		Categories 
		<?if($data->edit){?><a href="<?=url::edit(0,'',$data->prfxtbl)?>"><img width="15" src="<?=HREF?>/files/template/icons/add.png" /></a><?}?>
	</h4>
	<ul>
	<?foreach($data->cats as $val){?>
		<li>
			<?if(!empty($val->funcPanel)){?><?=$val->funcPanel?><?}?>
			<a href="<?=url::category($val,$data->prfxtbl)?>"><?=$val->title?></a>&nbsp;<small title="count of posts">(<?=$val->count?>)</small>&nbsp;
		</li>
	<?}?>
	</ul>
<?}?>
</div> 
