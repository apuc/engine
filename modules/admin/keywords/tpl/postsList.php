<script type="text/javascript">
	$(function(){
		$('.download_checkbox_post').change(function(){
			$(this).parent().parent()
					.find('.download_checkbox_pids, .download_checkbox_kidpids')
					.prop('checked',$(this).is(':checked'));
		});
	});
</script>
<style type="text/css">
	.admin_keywords{}
	.admin_keywords .leftcol {
		width: 70%;
		float: left;
	}
	.admin_keywords .leftcol table tr * {
		padding: 5px;
	}
	.admin_keywords .leftcol table tr:nth-child(even){
		background: #eee none repeat scroll 0 0;
	}
	.admin_keywords .rightcol {
		width: 25%;
		float: right;
	}
	.admin_keywords .rightcol input {
		padding: 5px;
	}
	.admin_keywords .posts_keywords {display: none;}
	.admin_keywords .sumbitbtn{width: 100%;text-align: right;}
</style>
<div class="admin_keywords">
	<div class="leftcol">
		<?if(!$data->tmpwritable){?><span style="color:red">Set permission chmod 0777 <?=$data->tmp?></span><?}?><br/>
		<div>
			<a href="<?=url::keywords_initParsing()?>">status</a>
		</div>
		<form method="POST" action="<?=url::keywords_initParsing()?>" onsubmit="$('[name=nested]').val($('#nestedval').val());$('[name=addword]').val($('#addwordval').val());">
			<div class="sumbitbtn"><input type="submit" value="Download"/></div>
			<table width="100%">
				<thead>
					<tr>
						<th width="10"></th>
						<th width="125">Date <a href="<?=url::keywords_PostList().'&sort=datePublish+asc'?>">ASC</a><a href="<?=url::keywords_PostList().'&sort=datePublish+desc'?>">DESC</a></th>
						<th>Title</th>
						<th>Keyword</th>
					</tr>
				</thead>
				<tbody>
				<?
				foreach($data->posts as $pid=>$post){
					$i=0;
					if(count($post->keywords)==1){
						?>
						<tr>
							<td><input class="download_checkbox_post" type="checkbox" checked="checked"/></td>
							<td><?=$post->datePublish?></td>
							<td><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
							<td><label><input class="download_checkbox_kidpids" type="checkbox" value="<?=$post->keywords[0]->kidpid?>" checked="checked" name="kidpids[]"/> <?=$post->keywords[0]->title?></label></td>
						</tr>
						<?
					}else{?>
						<tr>
							<td><input class="download_checkbox_post" type="checkbox" checked="checked"/></td>
							<td><?=$post->datePublish?></td>
							<td><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
							<td><a href="javascript:void(0);" onclick="$(this).parent().children('.posts_keywords').css('display','block');$(this).remove();">show keywords (<?=count($post->keywords)?>)</a>
								<?foreach($post->keywords as $keyword){?>
									<div class="posts_keywords">
										<label><input class="download_checkbox_kidpids" type="checkbox" value="<?=$keyword->kidpid?>" checked="checked" name="kidpids[]"/> <?=$keyword->title?></label>
									</div>
								<?}?>
							</td>
						</tr>
					<?}?>
				<?}?>
				</tbody>
			</table>
			<input type="hidden" name="nested"/>
			<input type="hidden" name="addword"/>
			<div class="sumbitbtn"><input type="submit" value="Download"/></div>
		</form>
		<?=$data->paginator?>
	</div>
	<div class="rightcol">
		<label>nested request count<input id="nestedval" type="number" size="3" min="0" max="10" value="0" style="width:50px"/></label><br/><br/>
		<label>Add word<input type="text" id="addwordval"/></label>
	</div>
</div>