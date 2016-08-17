$(document).ready(function() {
	var popupFirst=false;
	if(getCookie('subscribe')==''){
		popupFirst=true;
		setTimeout(function (){
			openPopup();
		},5000);
	}
	if(!popupFirst&&getCookie('subscribe-side')==''){
		$(window).scroll(function (){
			var content=$('.left-col-post').height()-300;
			var currentScroll=$(window).scrollTop();
			//alert(currentScroll+' '+content);
			if(currentScroll>content-600){
				openPopupSide('show');
			}else if($('.popup-side').get()){
				openPopupSide('hide');
			}
		});
	}
});

function openPopup(){
	$('.popup-overlay').remove();
	//создаем блок overlay
	if($('.popup').html()==undefined){
		return false;
	}
	$('body').append('<div class="popup-overlay"></div>');
	$('.popup-overlay').css('height', '100%');
	$('.popup').fadeIn(500);
	$('.popup .close').click(function (){
		setCookie('subscribe','1',21);
		$('.popup').remove();
		$('.popup-overlay').remove();
		return false;
	});
}
function openPopupSide(act){
	if(act=='show'){
		$('.popup-side').fadeIn(500);
	}else if(act=='close'){
		setCookie('subscribe-side','1',21);
		$('.popup-side').fadeOut(500);
	}else if(act=='hide'){
		$('.popup-side').fadeOut(500);
	}
}
function setCookie(cname,cvalue,exdays){
	var d = new Date();
	d.setTime(d.getTime()+(exdays*24*60*60*1000));
	var expires = "expires="+d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}
function getCookie(cname){
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++){
		var c = ca[i].trim();
		if (c.indexOf(name)==0) return c.substring(name.length,c.length);
	}
	return "";
}
