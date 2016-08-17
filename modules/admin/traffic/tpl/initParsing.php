<?php
?>
<script type="text/javascript">
	$(document).ready(function () {
		showLog();
		setInterval(showLog,2000);
		$('#clearlog').click(function(){
			$.get("<?=HREF?>/?module=admin/traffic",{
				act:'clearLog',
			},function answer(data){
				$('#running_status').html('<pre>'+data+'</pre>');
			});
		});
	});
	function showLog(){
		$.get("<?=HREF?>/?module=admin/traffic",{
			act:'showLog',
			log:'gglreplparse.log',
			tail:300
		},function answer(data){
			$('#processlog').html(data);
		});
	}
</script>
<style type="text/css">
	fieldset{width: 50%}
	.log{width:100%;}
	legend{font-weight: bold;}
</style>
<button id="clearlog" title="Clear log">Clear log</button>
<div id="running_status">
	<div>status: <?=$data->status?'<span style="color:green">running</span>':'<span style="color:red">stoped</span>'?></div>
</div>
<fieldset><legend>parse process log</legend><pre class="log" id="processlog"></pre></fieldset>