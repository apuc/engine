//facebook
//facebook init properties from meta tags
FB_APPID=false;
FB_LANG=false;
var metaTags=document.getElementsByTagName("meta");
for (var i = 0; i < metaTags.length; i++) {
	if (metaTags[i].getAttribute("property")=="fb:app_id"&&!FB_APPID) {
		FB_APPID=metaTags[i].getAttribute("content");
	}
	if (metaTags[i].getAttribute("property")=="og:locale"&&!FB_LANG) {
		FB_LANG=metaTags[i].getAttribute("content");
	}
}
if(!FB_LANG) FB_LANG="en_US";
if(FB_APPID){
	window.fbAsyncInit = function() {
		FB.init({
			appId: FB_APPID,
			xfbml: true,
			version: 'v2.4'
		});
	};
}
//load facebook SDK
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/"+FB_LANG+"/sdk.js#xfbml=1&version=v2.0";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
//google plus
(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
//twitter
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
//pinterest
(function(d){
    var f = d.getElementsByTagName('SCRIPT')[0], p = d.createElement('SCRIPT');
    p.type = 'text/javascript';
    p.async = true;
    p.src = '//assets.pinterest.com/js/pinit.js';
    f.parentNode.insertBefore(p, f);
}(document));