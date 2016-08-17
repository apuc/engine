$(document).ready(function () {
	$('.overlay-enable').each(function (indx) {
		$(this).click(function () {
			window.location.hash='gal_'+$(this).attr('data-tbl')+'_'+$(this).attr('data-pid')+'_'+encodeURI($(this).attr('data-url'));
			return false;
		});
	});
	//запуск при первом вызове страницы
	getOverlay();
	//запуск при изменении хэша
	var hashchange = function (event) {
		getOverlay();
	};
	if (window.addEventListener) {
		window.addEventListener('hashchange', hashchange);
	} else {
		window.attachEvent('onhashchange', hashchange);
	}
});

function getOverlay() {
	$('.overlay').remove();
	var hash=window.location.hash;
	if(!hash.match(/#gal_/)){
		if($('.overlay').lenth){
			closeOverlay();
		}
		return false;
	}
	var params=hash.match(/gal_(.+)_(\d+)_(.+)$/);
	var tbl=params[1];
	var pid=params[2];
	var imgUrl=params[3];
	window.location.hash='gal_' + tbl + '_' + pid + '_' + imgUrl;
	//создаем блок overlay
	$('body').css('overflow', 'hidden');
	$('body').append('<div class="overlay"></div>');
	$('.overlay').css('height', '100%');
	//подгружаем галерею
	$.post(
			window.location.basepath+"?module=gallery",
			{act: 'overlayGallery', tbl: tbl, pid: pid, url: imgUrl},
	onSuccess
			);
	function onSuccess(data) {
		$('.overlay').html(data);
	}
	$('body').keydown(function (obj) {
		if (obj.which == 27)
			closeOverlay();
	});
}
function closeOverlay() {
	$('.overlay').fadeOut(300, function () {
		$('.overlay').remove();
	});
	if ($('body').css('overflow') === 'hidden') {
		$('body').css('overflow', 'auto');
	}
	window.location.hash='';
}
//END overlay gallery