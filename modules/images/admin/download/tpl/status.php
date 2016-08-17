<?php

?>
<style type="text/css">
	legend{font-weight: bold;}
</style>
<script>
	var startPos={'gglparse':-1,'imgdownload':-1,'imgdownload.stream':-1};
	$(document).ready(function () {
		showLog('gglparse');setInterval('showLog("gglparse")',2000);
		showLog('imgdownload');setInterval('showLog("imgdownload")',2000);
		showLog('imgdownload.stream');setInterval('showLog("imgdownload.stream")',2000);
		$('#stopd').click(function(){
			$.get("<?=HREF?>/?module=images/admin/download",{
				act:'stopDaemons',
			},function answer(data){
				$('#running_status').html('<pre>'+data+'</pre>');
			});
		});
		$('#clearcache').click(function(){
			$('#clearcache').val('wait...');
			$.get("<?=HREF?>/?module=images/admin/download",{
				act:'clearCache',
			},function answer(data){
				$('#clearcache').html(data);
			});
		});
	});
	function showLog(type){
		$.get("<?=HREF?>/?module=images/admin/download",{
			act:'showLog',
			log:type+'.log',
			tail:100,
			start: startPos[type],
		},function answer(data){
			data=data.split("\n");
			startPos[type]=data[0];
			data.splice(0,1);
			$('#'+(type.replace(/\./g, ""))).append(data.join("\n"));
			//Сокрашаем лог  до 500 строк
			var len=500;
			data=$('#'+type.replace(/\./g, "")).html().split("\n")
			if(data.length>len){
				$('#'+(type.replace(/\./g, ""))).html(data.slice(-len).join("\n"));
			}
			
		});
	}
</script>
<button id="stopd" title="stop daemons, clear logs">Stop daemons</button>
<button id="clearcache" title="clear google cache">Clear google cache</button>
<div id="running_status">
	<div>gglparse daemon: <?=$data->dGglparse?'<span style="color:green">running</span>':'<span style="color:red">not running</span>'?></div>
	<div>imgdownload daemon: <?=$data->dImgdownload?'<span style="color:green">running</span>':'<span style="color:red">not running</span>'?></div>
</div>
<div style="float:left;max-width:400px;overflow:auto"><fieldset><legend>gglparse daemon log</legend><pre class="log" id="gglparse"></pre></fieldset></div>
<div style="float:left;max-width:400px;overflow:auto"><fieldset><legend>imgdownload daemon log</legend><pre class="log" id="imgdownload"></pre></fieldset></div>
<div style="float:left;max-width:400px;overflow:auto"><fieldset><legend>imgdownload stream log</legend><pre class="log" id="imgdownloadstream"></pre></fieldset></div>
<div style="clear:both"></div>

