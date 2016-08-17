function ias_load(img){
	ias_scale=img.width/img.naturalWidth;
	if(img.naturalWidth<=470||img.naturalHeight<=246){
		ias=null;
	}else{
		var heightRatio=img.width/1.91;
		var delta=(img.height-heightRatio)/2;
		var y1=delta;
		var y2=img.height-delta;
		ias=$(img).imgAreaSelect({aspectRatio:'1.91:1',minWidth:470*ias_scale,instance:true,x1:0,y1:y1,x2:img.width,y2:y2});
	}
}
function cropImageUI(e){
	var el = $(e);
	crop_image_url = el.attr('data-name');
	if (!crop_image_url) {
		return false;
	}
	var imgHtml='<img src="'+window.location.basepath+'images/'+crop_image_url+'?'+(new Date().getMilliseconds())+'" onload="ias_load(this)"/>';
	crop_dialog=$('<div class="crop_image">'+imgHtml+'</div>')
	.dialog({
		height: $(window).height()-100,
		width: $(window).width()-100,
		title: 'Crop Image',
		autoOpen: false,
		modal: true,
		buttons: {
			"Save changes":cropImageSave,
			Cancel:function(){
				$(this).dialog("close");
			}
		},
		close:function(){
			if(ias){
				ias.remove();
			}
			if(typeof(crop_image_temp_url)!='undefined'&&crop_image_temp_url){
				$.post(window.location.basepath+'?module=images/admin&act=playicon',{url:crop_image_temp_url,drop:1});
			}
			crop_dialog.dialog('destroy');
		},
		open:function(){
			$('.ui-dialog-buttonpane').prepend('<label id="crop_play"><input type="checkbox"/> Play Icon</label>');
			$('#crop_play input').change(function(){
				if(this.checked) {
					cropImagePreSave();
				}else{
					$('.crop_image img').remove();
					$('.crop_image').html(imgHtml);
				}
			});
		},
	})
	.parent().css({position:"fixed"})
	.end()
	.dialog('open');
}

function cropImageSave(){
	if(ias){
		var sel=ias.getSelection();
	}
	if(!ias||!sel.width||!sel.height){
		sel={x1:0,y1:0,width:0,height:0};
	}
	$.post(window.location.basepath+'?module=images/admin&act=crop',{scale:ias_scale,url:crop_image_url,x1:sel.x1,y1:sel.y1,width:sel.width,height:sel.height,playicon:($('#crop_play input').prop('checked')?crop_image_temp_url:'')},function(){crop_dialog.dialog('close');$('#savedImages img').each(function(i,e){e.src+='?'+(new Date().getMilliseconds());});});	
}
function cropImagePreSave(){
	if(ias){
		var sel=ias.getSelection();
	}
	if(!ias||!sel.width||!sel.height){
		sel={x1:0,y1:0,width:0,height:0};
	}
	$.post(window.location.basepath+'?module=images/admin&act=playicon',{scale:ias_scale,url:crop_image_url,x1:sel.x1,y1:sel.y1,width:sel.width,height:sel.height,drop:0},
		function(r){
			crop_image_temp_url=r;
			ias.remove();
			$('.crop_image img').remove();
			$('.crop_image').html('<img src="'+window.location.basepath+'images/'+r+'?'+(new Date().getMilliseconds())+'"/>');
		});
}