<script>
var userDivOpened=[];
function userDiv(eid){
	userDivOpened[userDivOpened.length]=eid;
	for(var i in userDivOpened){
		$("#"+userDivOpened[i]).css("display","none")
	}
	$("#"+eid).css("display","block")
	var left=(document.width-560)/2;
	var top=(window.innerHeight-document.getElementById(eid).offsetHeight)/2;
	if(top<0)top=10;
	if(left<0)left=10;
	$("#"+eid).css("left",left);
	$("#"+eid).css("top",top);
	return false;
}
function mclose(eid){
	$("#"+eid).css("display","none")
	return false;
}
</script>
<div id="login" class="userDiv">
	<div class="box">
		<?include $template->inc('login.php');?>
	</div>
	<a href="#" onclick="return mclose('login')" class="close"><img title="Close this window" alt="Close" src="<?=HREF?>/modules/user/tpl/files/icons/close.png"></a>
</div> 

<div id="register" class="userDiv">
	<div class="box">
		<?include $template->inc('register.php');?>
	</div>
	<a href="#" onclick="return mclose('register')" class="close"><img title="Close this window" alt="Close" src="<?=HREF?>/modules/user/tpl/files/icons/close.png"></a>
</div> 
