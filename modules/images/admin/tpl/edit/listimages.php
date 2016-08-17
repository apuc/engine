<style type="text/css">
	div.imageBox{
		float: left;
		margin:0 2px 4px 2px;
		width: 48%;
	}
	.imageBox img:hover{opacity:0.7;}
	.imageBox img{max-width:139px;height:80px;}
	.imageBox a{
		display:inline;
	}
	.imageBox a:last-child{
		float:right;
	}
</style>
<script type="application/javascript" src="<?=HREF?>/files/images/admin/js/sortable.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		//check images status for downloads from urls
		var postExist=Boolean(parseInt($('input[name=pid]').val()));
		if(postExist)
			image_interval=setInterval(image_update,3000);
		ImagesStatusCheck('<?=$data->tbl?>',<?=$data->pid?>,<?=$data->user?>);

		sortableImages();
	});
	//functions
	function image_update(){
		var image_sort = {};
		$('#savedImages > div').each(function( index ) {
			var image_id = $(this).find('a').data('id');
			image_sort[$('#savedImages > div').length-index]=image_id;
		});

		$.post(window.location.basepath+'?module=images/admin&act=listofimages',{pid: '<?=$data->pid?>',tbl: '<?=$data->tbl?>',easy: 1, image_sort: image_sort},
			function (serverAnswer){
				$('#savedImages').html(serverAnswer);
				image_sortable.destroy();
				sortableImages();
			}
		);
		ImagesStatusCheck('<?=$data->tbl?>',<?=$data->pid?>,<?=$data->user?>);
		var loadStatus=$('#status').children('span').attr('data-check');
		if(loadStatus=='done'){
			clearInterval(image_interval);
		}
	}
	function ImagesStatusCheck(tbl,pid,uid){
		if(pid==0) return;
		$.ajax({
		type: "POST", url: window.location.basepath+"?module=images/admin&act=checkStatus", data: "tbl="+tbl+"&pid="+pid+"&uid="+uid,
			success: function(data){
				$('#status').html('images uploading: '+data+'<hr />');
			}
		});
	}
	function sortableImages(){
		image_sortable_el=document.getElementById('savedImages');
		image_sortable=new Sortable(image_sortable_el,{
			animation: 150,
			onStart: function(evt) {
				clearInterval(image_interval);
			},
			onUpdate: function (evt) {
				var image_sort = {};
				$('#savedImages > div').each(function( index ) {
					var image_id = $(this).find('a').data('id');
					image_sort[$('#savedImages > div').length-index]=image_id;
				});
				$.post(window.location.basepath+'?module=images/admin&act=listofimages',{pid: '<?=$data->pid?>',tbl: '<?=$data->tbl?>',easy: 1, image_sort: image_sort},
					function (serverAnswer){
						image_interval=setInterval(image_update,3000);
					}
				);
			},
		});
	}
	function ajaxDelImage(obj){
		if(!confirm('Delete image?')){
			return;
		}
		var imgId=Number($(obj).attr('data-id'));
		var data = {imid: imgId, ajax: true};
		$.post('<?=url::imagesAdminDel()?>',data,
			function (serverAnswer){
				$('#savedImages').html(serverAnswer);
			}
		);
	}
</script>
<div id="savedImages"><?=$data->images?></div>