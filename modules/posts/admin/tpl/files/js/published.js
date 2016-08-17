$(function(){
	//listbyuser||main list
	$(document).on('click','button[name="published"]',function(){
		var el = $(this),
			id = el.data('id');
			var prfxtbl=el.data('prfxtbl')
		if(el.hasClass('published-no')) {
			$.get(window.location.basepath+'?module=posts/admin&act=published_update&pid='+id+'&prfxtbl='+prfxtbl+'&published=published');
			el.removeClass('published-no');
			el.addClass('published-yes');
			el.find('img').attr('src',window.location.basepath+'files/icons/pause.jpg')
		} else if(el.hasClass('published-yes')) {
			$.get(window.location.basepath+'?module=posts/admin&act=published_update&pid='+id+'&prfxtbl='+prfxtbl+'&published=unpublished');
			el.removeClass('published-yes');
			el.addClass('published-no');
			el.find('img').attr('src',window.location.basepath+'files/icons/play.jpg');
		}
	});
	
});
