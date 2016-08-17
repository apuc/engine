<script type="text/javascript">
	//base directory path for js
	window.location.basepath='<?=HREF.(substr(HREF,-1)!='/')?'/':''?>';
	//frame breakout
	if (top.location != location) {
		top.location.href = document.location.href;
	}
	// set/get cookies
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
</script>