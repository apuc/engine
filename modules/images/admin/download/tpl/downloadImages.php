<script>
	function getKidPids(){
		var vals=[];
		$('.download_checkbox_kidpids:checked').each(function(i,e){
			vals.push(e.value);
		});
		return vals.join(',');
	}
	function getPids(){
		var vals=[];
		$('.download_checkbox_pids:checked').each(function(i,e){
			vals.push(e.value);
		});
		return vals.join(',');
	}
	function showKeywords(e){
		var td=$(e).parent();
		td.parent().parent().find('.keywords_tr').css('display','table-row');
		td.prop('rowspan', 1).html('<i>Total</i>');
	}
	$(function(){
		$('.download_checkbox_post').change(function(){
			$(this).parent().parent().parent()
					.find('.download_checkbox_pids, .download_checkbox_kidpids')
					.prop('checked',$(this).is(':checked'));
		});
	});
	$(document).ready(function(){
		$('#m_opt_adddim button').click(function(){
			var text=$('#m_opt_adddim textarea').val();
			var strArr=text.split("\n");
			for(i=0;i<strArr.length;i++){
				var dim=strArr[i].split("x");
				dim[0]=parseInt(dim[0]);
				dim[1]=parseInt(dim[1]);
				if(dim[0]<=0||dim[1]<=0||isNaN(dim[0])||isNaN(dim[1])) {alert("incorrect"); return false;}
				var dimval=dim[0]+'x'+dim[1];
				str=dim[0]+' x '+dim[1];
				$('#m_opt_adddim').before('<label><input type="checkbox" name="man[imsize][]" value="'+dimval+'" checked=""/> '+str+'</label><br/>');
			}
			return false;
		});
		$('#m_opt_addratio button').click(function(){
			var str=$('#m_opt_addratio input').val();
			var rat=str.split(":");
			rat[0]=parseInt(rat[0]);
			rat[1]=parseInt(rat[1]);
			if(rat[0]<=0||rat[1]<=0||isNaN(rat[0])||isNaN(rat[1])) {alert("incorrect"); return false;}
			rat=rat[0]+':'+rat[1];
			$('#m_opt_addratio').before('<label><input type="checkbox" name="man[ratio][]" value="'+rat+'" checked=""/> '+rat+'</label><br/>');
			return false;
		});
		//событие для mainkeyword
		$('input[name="mainkey"]').change(function(){
			var url=document.location.href;
			if($(this).prop('checked'))
				document.location=url+'&mainkey=1';
			else
				document.location=url.replace('&mainkey=1','');
		});
		//
		anotherRunningAtt();
	});
	function anotherRunningAtt(){
		$.get("<?=HREF?>/?module=images/admin/download",{
			act:'anotherRunning',
		},function answer(data){
			if(data) {
				if(!confirm("HAVE TO CONTINUE?\nAnother daemon runnig\n"+data)){
					$('[name=upload_new_submit]').attr('disabled','');
					$('[name=upload_to_submit]').attr('disabled','');
				}
			}
		});
	}
</script>
<style type="text/css">
	.leftcol {
		width: 70%;
		float: left;
	}
	.leftcol table tr * {
		padding: 5px;
	}
	.leftcol table tbody:nth-child(odd) {
		background: #eee;
	}
	.rightcol {
		width: 25%;
		float: right;
	}
	.rightcol input {
		padding: 5px;
	}
	.keywords_tr{
		display:none;
	}
</style>
<div class="leftcol">
	<?if(!$data->tmpwritable){?><span style="color:red">Set permission chmod 0777 <?=$data->tmp?></span><?}?><br/>
	<?if($data->freespace<30){?><span style="color:red">Free space <?=$data->freespace?></span><?}?>
	<div>
		<a href="<?=url::status()?>">status</a> |
		<small>free space <?=$data->freespace?> Gb</small>
	</div>
	<table width="100%">
		<thead>
			<tr>
				<th width="10"></th>
				<th width="40">Pid</th>
				<th width="125">Date <a href="<?=url::downloadImages().'&sort=datePublish+asc'.($data->mainkey?'&mainkey=1':'')?>">ASC</a><a href="<?=url::downloadImages().'&sort=datePublish+desc'?>">DESC</a></th>
				<th>Title</th>
				<th>Keyword</th>
				<th width="95">Images Count <a href="<?=url::downloadImages().'&sort=countPhoto+asc'.($data->mainkey?'&mainkey=1':'')?>">ASC</a><a href="<?=url::downloadImages().'&sort=countPhoto+desc'?>">DESC</a></th>
			</tr>
		</thead>
		<?
		foreach($data->posts as $pid=>$post){
			$i=0;
			echo '<tbody>';
			if(!$post->keywords){
				?>
				<tr>
					<td><input data-id="<?=$post->id?>" class="download_checkbox_post" type="checkbox" checked="checked"/></td>
					<td><?=$post->id?></td>
					<td><?=$post->datePublish?></td>
					<td><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
					<td><label><input class="download_checkbox_pids" type="checkbox" value="<?=$post->id?>" checked="checked"/></label></td>
					<td align="right"></td>
				</tr>
				<?
			}else{
				$rowspan='rowspan="'.(count($post->keywords)+1).'"';
				$sum=0;
				foreach($post->keywords as $keyword){
					$sum+=$keyword->countPhoto;
				}
				if(count($post->keywords)==1){
					?>
					<tr>
						<td><input data-id="<?=$post->id?>" class="download_checkbox_post" type="checkbox" checked="checked"/></td>
						<td><?=$post->id?></td>
						<td><?=$post->datePublish?></td>
						<td><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
						<td><label><input class="download_checkbox_kidpids" type="checkbox" value="<?=$post->keywords[0]->kidpid?>" checked="checked"/> <?=$post->keywords[0]->title?></label></td>
						<td align="right"><?=$sum?></td>
					</tr>
					<?
				}else{
					?>
					<tr>
						<td <?=$rowspan?>><input data-id="<?=$post->id?>" class="download_checkbox_post" type="checkbox" checked="checked"/></td>
						<td <?=$rowspan?>><?=$post->id?></td>
						<td <?=$rowspan?>><?=$post->datePublish?></td>
						<td <?=$rowspan?>><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
						<td <?=$rowspan?>><a href="javascript:void(0);" onclick="showKeywords(this);">show keywords (<?=count($post->keywords)?>)</a></td>
						<td align="right"><?=$sum?></td>
					</tr>
					<?foreach($post->keywords as $keyword){?>
						<tr class="keywords_tr">
							<td><label><input class="download_checkbox_kidpids" type="checkbox" value="<?=$keyword->kidpid?>" checked="checked"/> <?=$keyword->title?></label></td>
							<td align="right"><?=$keyword->countPhoto?></td>
						</tr>
					<?
					}
				}
			}
			echo '</tbody>';
			?>
		<?}?>
	</table>
	<?include $template->inc('downloadImages/delposts.php');?>
	<?=$data->paginator?>
</div>
<div class="rightcol">
	<div>
		<form action="<?=url::status()?>" onsubmit="$('#upload_kidpids').val(getKidPids());$('#upload_pids').val(getPids());" method="POST">
			<label>
				Upload <small>(max 100)</small><input name="upload_new" type="text" value="20" size="5"/> images
			</label>
			<input type="submit" name="upload_new_submit" value="Ok"<?=!$data->tmpwritable?' disabled=""':''?>>
			<br/>
			<br/>
			<label>
				Load up to <input name="upload_to" type="text" value="20" size="5"/> images
			</label>
			<input type="submit" name="upload_to_submit" value="Ok"<?=!$data->tmpwritable?' disabled=""':''?>><br/><br/>
			<label>Main keyword only <input type="checkbox" <?=$data->mainkey?'checked="" ':''?>name="mainkey" /></label><br/><br/>
			<label>Allow in gallery block <input type="checkbox" checked="" name="allowgallery" /></label><br/><br/>
			<label>Skip if exists for any post<input type="checkbox" checked="" name="skipExists" /></label><br/><br/>
			<label>Add word<input type="text" name="addword"></label>
			<input id="upload_kidpids" name="kidpids" type="hidden"/>
			<input id="upload_pids" name="pids" type="hidden"/>
			<br/>
			<div><strong>Google params</strong>
				<fieldset>
				<legend>Google options</legend>
					<table border="0">
						<tr><td><label for="r_any">Any</label></td><td><input id="r_any" type="radio" name="imsize[natsize]" checked="" value="a"/></td></tr>
						<tr><td><label for="r_large">Large</label></td><td><input id="r_large" type="radio" name="imsize[natsize]" value="l" /></td></tr>
						<tr><td><label for="r_medium">Medium</label></td><td><input id="r_medium" type="radio" name="imsize[natsize]" value="m"/></td></tr>
						<tr><td><label for="r_icon">Icon</label></td><td><input id="r_icon" type="radio" name="imsize[natsize]" value="i"/></td></tr>
						<tr><td><label for="s_largethan">Large than</label></td><td><select id="s_largethan" name="imsize[lth]">
							<option value="">-</option>
							<option value="qsvga">400×300</option>
							<option value="vga">640×480</option>
							<option value="svga">800×600</option>
							<option value="xga">1024×768</option>
							<option value="2mp">2 MP (1600×1200)</option>
							<option value="4mp">4 MP (2272×1704)</option>
							<option value="6mp">6 MP (2816×2112)</option>
							<option value="8mp">8 MP (3264×2448)</option>
							<option value="10mp">10 MP (3648×2736)</option>
							<option value="12mp">12 MP (4096×3072)</option>
							<option value="15mp">15 MP (4480×3360)</option>
							<option value="20mp">20 MP (5120×3840)</option>
							<option value="40mp">40 MP (7216×5412)</option>
							<option value="70mp">70 MP (9600×7200)</option>
							</select></td></tr>
						<tr><td><label for="t_exactly">Exactly</label></td><td><input id="t_exactly" type="text" style="width:50px" name="imsize[ex][w]"/>x<input style="width:50px" type="text" name="imsize[ex][h]" /></td></tr>
					</table>
				</fieldset>
				<fieldset>
					<legend>Manual options</legend>
					<table border="0">
						<tr><td>
							<label><input type="checkbox" name="man[imsize][]" value="1024x768"/> 1024 x 768</label><br/>
							<label><input type="checkbox" name="man[imsize][]" value="1280x960"/> 1280 x 960</label><br/>
							<label><input type="checkbox" name="man[imsize][]" value="1280x1024"/> 1280 x 1024</label><br/>
							<label><input type="checkbox" name="man[imsize][]" value="1280x800"/> 1280 x 800</label><br/>
							<label><input type="checkbox" name="man[imsize][]" value="1600x600"/> 1600 x 600</label><br/>
							<label><input type="checkbox" name="man[imsize][]" value="1600x1200"/> 1600 x 1200</label><br/>
							<div id="m_opt_adddim"><textarea style="width:70%;" placeholder="1024x768"></textarea><button>+</button></div>
						</td><td>
							<label><input type="checkbox" name="man[ratio][]" value="16:10"/> 16:10</label><br/>
							<label><input type="checkbox" name="man[ratio][]" value="16:9"/> 16:9</label><br/>
							<label><input type="checkbox" name="man[ratio][]" value="5:4"/> 5:4</label><br/>
							<label><input type="checkbox" name="man[ratio][]" value="5:3"/> 5:3</label><br/>
							<label><input type="checkbox" name="man[ratio][]" value="4:3"/> 4:3</label><br/>
							<label><input type="checkbox" name="man[ratio][]" value="3:2"/> 3:2</label><br/>
							<div id="m_opt_addratio"><input style="width:70%;" type="text" placeholder="16:9" /><button>+</button></div>
						</td></tr>
					</table>
					<br/><hr/>
					<table border="0">
						<tr>
							<td><label><input type="radio" name="man[lim][type]" value="m"/> More than</label>&nbsp;&nbsp;</td>
							<td><label><input type="radio" name="man[lim][type]" value="lth"/> Less than</label>&nbsp;&nbsp;</td>
							<td><label><input type="radio" name="man[lim][type]" value="e"/> Equal</label>&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3">
								<label><input type="text" style="width:50px" placeholder="width" name="man[lim][w]"/></label>or(and)
								<label><input type="text" style="width:50px" placeholder="height" name="man[lim][h]"/></label>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
		</form>
	</div>
</div>
