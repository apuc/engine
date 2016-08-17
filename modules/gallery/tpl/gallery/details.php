<div class="props" style="width:300px;float:right;">
   <h3>Details:</h3>
	<?if($data->resolution){echo "<p>Resolution: <b>$data->resolution px</b></p>";}?>
	<?if($data->size){echo "<p>File Size: <b>$data->size Kb</b></p>";}?>
	<p>File Name: <b><?=$img->url?></b></p>
	<p>Text: <b><?=$img->text?></b></p>
	<div id="gallery-download">
		<input type="hidden" value="<?=$img->url?>"/>
		<script>
			$(document).ready(function (){
				$('#gallery-download a').click(function (){
					var res=$('#gallery-download select').val().split('x');
					var src=$('#gallery-download input').val();
					if(src)
						window.open('/?module=gallery&act=imgResolution&src='+src+'&x='+res[0]+'&y='+res[1]);
					return false;
				});
			});
		</script>
		<table><tr><td>
		<select style="height: 26px;">
			<option value="1280x720">1280x720</option>
			<option value="1280x800">1280x800</option>
			<option value="1360x768">1360x768</option>
			<option value="1366x768">1366x768</option>
			<option value="1440x900">1440x900</option>
			<option value="1600x900">1600x900</option>
			<option value="1680x1050">1680x1050</option>
			<option value="1920x1080">1920x1080</option>
			<option value="1920x1200">1920x1200</option>
		</select>
		</td><td>
		<a href="<?=url::image($img->url)?>"><p style="width:190px;background-color:green;text-align:center">download</p></a>
		</td></tr></table>
	</div>
</div>