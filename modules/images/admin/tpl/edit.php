<script type="application/javascript" src="<?=HREF?>/files/images/admin/js/search.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/images/admin/js/crop.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/images/admin/js/jquery-ui.js"></script>
<script type="application/javascript" src="<?=HREF?>/files/images/admin/js/jquery.imgareaselect.min.js"></script>
<script type="application/javascript">
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
</script>
<style type="text/css">
	/* search result */
	@import '<?=HREF?>/files/images/admin/css/jquery-ui.css';
	/* crop */
	@import '<?=HREF?>/files/images/admin/css/imgareaselect/imgareaselect-default.css';
	.search_result img {
		max-width: 250px;
		max-height: 200px;
		cursor: pointer;
	}
	.search_result .search_img {
		width: 250px;
		height: 250px;
		border: 1px solid #ccc;
		margin: 5px 0 0 5px;
		text-align: center;
		vertical-align: middle;
		display: inline-block;
		position: relative;
	}
	.search_result_image {
		text-align: center;
	}
	.search_result_image img {
		max-width: 95%;
		max-height: 95%;
	}
	.search_result .search_img div {
		position: absolute;
		bottom: 0;
		height: 40px;
		padding: 5px;
	}
	.crop_image {
		text-align: center;
	}
	.crop_image img {
		max-width: 100%;
		max-height: 100%;
	}
</style>
<div id="status"></div>
<?include $template->inc('edit/listimages.php');?>
<div style="clear:both;"></div>
<?include $template->inc('edit/uploadimages.php');?>