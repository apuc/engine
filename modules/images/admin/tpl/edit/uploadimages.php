<script>
	$(document).ready(function(){
		// imageform control function
		$('#chInputList').click(function (){
			menuControl('fromUrlList');
			$('#fromUrlList a').click(function (){
				if(!confirm('Apply?')){ return; }
				var textareaStr;
				var id;
				if(textareaStr=$('#fromUrlList textarea').val()){
					//clear prev data
					$('#fieldUploadCounter').val(0);
					$('#fieldUpload #addbInput').html('');
					//set new data
					linkArr=textareaStr.split("\n");
					for(var i=0;i<linkArr.length;i++){
						upload(i,linkArr[i]);
					}
					$('#fieldUploadCounter').val(i);
					$('#fromUrlList textarea').val('');
				}
				menuControl('fromUrlUpload');
			});
		});
		$('#chInputUrl').click(function (){
			menuControl('fromUrlUpload');
		});
		$('#chInputFile').click(function (){
			menuControl('multiFileUpload');
		});
		addUpload(1);
		multiUploadInit();	
	});
	function menuControl(open){
		var menu=['fromUrlList','fromUrlUpload','multiFileUpload'];
		var indx=menu.indexOf(open);
		delete menu[indx];
		var i;
		for(i in menu){
			$('#fieldUpload #'+menu[i]).css('display','none');
		}
		//alert($('#fieldUpload #'+open).html());
		$('#fieldUpload #'+open).fadeIn(200);
	}
	function upload(id,val){
		var objId='imagesInput'+id;
		var input='<input id="'+objId+'" style="width:50%" size="255" type="text" name="images['+id+']" placeholder="'+(id+1)+' url..."/>'
			+'<span id="imagesInputTitle'+id+'" style="display:none;"> <input style="width:45%" size="255" type="text" class="input-title" name="imagesInputTitle['+id+']" />'
			+'<input size="255" type="hidden" class="input-description" name="imagesInputDescription['+id+']" />'
			+'<input type="hidden" class="input-href" name="imagesInputHref['+id+']" />'
			+'<input type="hidden" name="imagesInputTbn['+id+']" />'
			+'<input type="hidden" name="imagesInputPt['+id+']" />'
			+'</span>';
		$('#fieldUpload #addbInput').append('<div id="im'+id+'"></div>')
		$("#im"+id).html(input);
		var jqObj=$('#'+objId);
		if(val){
			jqObj.val(val);
			setInputSource(jqObj.val(),id);
		}else{
			jqObj.focusout(function (){
					setInputSource(jqObj.val(),id);
				});
		}
		return false;
	}
	function addUpload(clear){
		if(clear) $('#fieldUploadCounter').val(0);
		var c = Number($('#fieldUploadCounter').val());
		var num = Number($('#addbInputNum option:selected').val());
		for(var i=0;i<num;i++){
			var id=i+c;
			upload(id);
		}
		$('#fieldUploadCounter').val(c+num);
	}
	function setInputSource(val,id){
		$('#imagesInputTitle'+id+' input.input-title').val($('[name=title]').val());
		$('#imagesInputTitle'+id).css('display','inline');
	}

	function insertImageToEditor(obj){
		if($(obj).data("type")=="gif"){
			var imgSrc=window.location.basepath+"images/"+$(obj).data("url");
		}
		else{
			var imgSrc=$(obj).attr('data-src');
		}
		var imgUrl=$(obj).attr('data-url');
		var galleryUrl=$(obj).attr('data-gurl');
		var title=$(obj).attr('alt');
		if(!title){
			//try get the title from input
			title=$('input[name="title"]').val();
		}
		//console.log(imgSrc);
		if(tinyMCE!=undefined){
			tinyMCE.execCommand(
				'mceInsertContent',
				false,
				'<a class="overlay-enable" href="'+galleryUrl+'" data-tbl="<?=$data->tbl?>" data-pid="<?=$data->pid?>" data-url="'+imgUrl+'" name="'+imgUrl+'" title="'+title+'">'+
				'<img alt="'+title+'" src="'+imgSrc+'"/></a>'
			);
		}
	}
	var multiUploadInit=function (){
		var files;
		var uploadButton=$('#multiFileUpload input[name=multiUpload]');
		if(!uploadButton.length) return;
		uploadButton.after('<div id="statusField"></div>');
		var statusField=$('#multiFileUpload #statusField');
		$('#multiFileUpload input[type=file]').change(function(){
			files=this.files;
		});

		uploadButton.click(function(){
			var pid=Number($('input[name=pid]').val());
			var tbl=$('input[name=tbl]').val();
			var keyword=$('#multiFileUpload #imagesMultiUploadTitle').val();
			var title=$('input[name=title]').val();
			if(!files) return;
			if(!pid) {
				console.log('Error: undefined pid');
				return;
			}
			
			$.each(files,function(i,file){
				statusField.html('processing '+i+' file');
				var data=new FormData();
				data.append('act','saveImagesMultiUpload');
				data.append('pid',pid);
				data.append('tbl',tbl);
				data.append('title',title);
				data.append('keyword',keyword);
				data.append('images',file);
				$.ajax({
					url: document.location.basepath+'?module=posts/admin',
					type: 'POST',
					data: data,
					cache: false,
					processData: false,
					contentType: false,
					success: function(respond, textStatus, jqXHR){
						var status;
						if(respond=='done')
							status='OK';
						else
							status='FAIL';
						statusField.after('<div>'+file.name+' - '+status+'</div>');
						image_update();
					},
					error: function(jqXHR, textStatus, errorThrown){
						console.log('Error request: ' + textStatus);
					}
				});
			});
			statusField.html('done');
		});
	}
</script>
<style type="text/css">
	fieldset.img-input{
		border: 2px groove threedface;
		display: block;
		margin-left: 2px;
		margin-right: 2px;
		padding: 0.35em 0.625em 0.75em;
	}
</style>
<fieldset class="img-input">
	<legend>upload images</legend>
	<span style="font-size:0.9em"><b>change for:</b>
		<a href="" id="chInputUrl" onclick="return false;">from url</a>
		&nbsp;|&nbsp;
		<a href="" id="chInputList" onclick="return false;">from list</a>
		&nbsp;|&nbsp;
		<a href="" id="chInputFile" onclick="return false;">local file</a>
	</span>
	<div id="fieldUpload">
		<div id="fromUrlUpload">
			<div id="addbInput"></div>
			<div>
				<a href="#add" onclick="addUpload();return false;">add</a>
				<input type="hidden" id="fieldUploadCounter" name="fieldUploadCounter"/>
				<select id="addbInputNum">
					<option value="1">1</option>
					<option value="10" selected>10</option>
					<option value="20">20</option>
				</select>
				<br/><br/>
				<textarea id="search_keywords" cols="30" rows="3" placeholder="input keyword..." name="new_keywords"><?=$data->key?></textarea>
				<input type="button" value="Parse" onclick="search_request(this);" class="button"/>
			</div>
		</div>
		<div style="display:none;" id="fromUrlList">
			<textarea style="height: 200px; width: 92%;"></textarea><a href="#">apply</a>
		</div>
		<div style="display:none;" id="multiFileUpload">
		<?if($data->pid){?>
			<small>(max filesize <?=$data->limitFileUpload?>)</small>
			<div>
				<input id="imagesMultiUploadTitle" style="width:50%" size="255" placeholder="keyword" type="text" value=""/>
				<br/><small>Multiple upload (use Ctrl to choose images)</small><br/>
				<input style="display:block;" type="file" accept="image/*" multiple/>
				<?if($data->pid){?><input type="button" name="multiUpload" value="upload"/><?}?>
			</div>
		<?}else echo '<small>at first save post</small>';?>
		</div>
	</div>
</fieldset>