<div style="floal:left;width:450px;">
	<h3>Use this Image:</h3><br/>
	<table border="0" style="width:100%;">
		<tr><td><label for="htmlt">Direct link</label></td><td><input id="htmltl" type="text" onclick="this.select();" value="<?=url::img($data->tbl,$post->id,$img->url)?>" style="width:100%;"/></td></tr>
		<tr><td><label for="htmlth">HTML thumb link</label></td><td><input id="htmlth" type="text" onclick="this.select();" value="<a href='<?=url::post($post->url)?>'><img width='150' src='<?=url::imgThumb('',$img->url)?>'/></a>" style="width:100%;"/></td></tr>
		<tr><td><label for="forumth">Forum thumb link</label></td><td><input id="forumth" type="text" onclick="this.select();" value="[URL=<?=url::post($post->url)?>][IMG]<?=url::imgThumb('',$img->url)?>[/IMG][/URL]" style="width:100%;"/></td></tr>
	</table>
</div>