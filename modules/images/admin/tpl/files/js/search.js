function search_request(e, nocheck) {
	var el = $(e);
	var keywords = $('#search_keywords').val();
	if (!keywords) {
		return;
	}
	if (typeof(nocheck) == 'undefined') {
		nocheck = 0;
	}
	el.attr('disabled', true);
	$.ajax({
		type: "POST",
		url: window.location.basepath+"?module=images/admin&act=searchRequest",
		dataType: "json",
		data: { keywords: keywords, nocheck: nocheck }
	})
	.done(function( data ) {
		if (data == 'exists') {
			if (confirm('Are you sure to re-parsing?')) {
				search_request(e, 1);
			} else {
				el.attr('disabled', false);
			}
			return;
		}
		
		el.attr('disabled', false);
		if (data && data.length > 0) {
			var list = '';
			for (var k in data) {
				var href = data[k][0];
				var kw = data[k][1];
				var description = data[k][2];
				var url = data[k][3];
				var tbn = data[k][4];
				var pt = data[k][5];
				list += '<div class="search_img"><img src="' + url + '" datakw="' + kw + '" datahref="' + href + '" datadescription="' + description + '" data-tbn="'+tbn+'" data-pt="'+pt+'" onerror="$(this).parent().remove();" onclick="search_result_show_image(this);"/><div><input type="checkbox"/> '+kw+'</div></div>';
			}
			$('<div class="search_result"></div>')
			.html(list)
			.dialog({ 
				height: $(window).height()-50,
				width: $(window).width()-50,
				title: 'Search Results',
				autoOpen: false,
				modal: true,
				buttons: [ { text: "Save", click: function() { 
					var urls = [];
					$('.search_result .search_img').each(function(id, div){
						div = $(div);
						if (div.find('input')[0].checked) {
							var img = div.find('img');
							urls.push([img.attr('datakw'), img.attr('src'), img.attr('datahref'), img.attr('datadescription'), img.attr('data-tbn'), img.attr('data-pt')]);
						}
					});
					var ir = 100;
					while ($('#fromUrlUpload #addbInput div').length < urls.length) {
						if (ir-- <= 0) {
							break;
						}
						addUpload();
					}
					
					var inputs = $('#fromUrlUpload #addbInput div > input');
					for (var k in urls) {
						inputs[k].value = urls[k][1];
						inputs[k].style.fontStyle = 'normal';
						inputs[k].style.color = 'black';
						var id = inputs[k].id.replace('imagesInput', '');
						$("input[name='imagesInputTitle["+id+"]']").val(urls[k][0]);
						$("input[name='imagesInputHref["+id+"]']").val(urls[k][2]);
						$("input[name='imagesInputDescription["+id+"]']").val(urls[k][3]);
						$("input[name='imagesInputTbn["+id+"]']").val(urls[k][4]);
						$("input[name='imagesInputPt["+id+"]']").val(urls[k][5]);
						$('#imagesInputTitle'+id).css('display','inline');
					}
					
					$( this ).dialog( "close" ); 
				} } ]
			})
			.parent().css({position:"fixed"})
			.end()
			.dialog('open');
		}
	});
}

function search_result_show_image(e) {
	var url = $(e).attr('src');
	if (!url) {
		return false;
	}
	$('<div class="search_result_image"><img src="' + url +'"/></div>')
	.dialog({ 
		height: $(window).height()-100,
		width: $(window).width()-100,
		title: url,
		autoOpen: false,
		modal: true,
	})
	.parent().css({position:"fixed"})
	.end()
	.dialog('open');
}