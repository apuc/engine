&nbsp;
<?
if(!empty($data->images)){
	foreach($data->images as $img){?>
		<div class="imageBox">
			<img title="click to insert" alt="<?=$img->title?>" src="<?=url::imgThumb('250_',$img->url)?>" 
				data-src="<?=url::imgThumb('600_',$img->url)?>" 
				data-gurl="<?=url::img($data->tbl,$data->pid,$img->url)?>" 
				data-url="<?=$img->url?>"
				<?=$img->gif?'data-type="gif"':''?>
				onclick="insertImageToEditor(this)" />
			<br/><input type="text" name="image_title[<?=$img->id?>]" value="<?=$img->title?>" size="10"/>
			<br/><input type="text" name="image_description[<?=$img->id?>]" value="<?=$img->text?>" size="10"/>
			<a href="#del" data-id="<?=$img->id?>" onclick="ajaxDelImage(this);return false;">del</a>
			<a href="#crop" data-id="<?=$img->id?>" data-name="<?=$img->url?>" onclick="cropImageUI(this);return false;">crop</a>
		</div>
	<?}
}
?>