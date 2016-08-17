<?php

?>
<script type="text/javascript">
	$(document).ready(function () {
		showLogGglkwparse();
		setInterval(showLogGglkwparse,2000);
		$('#stopd').click(function(){
			$.get("<?=HREF?>/?module=admin/keywords",{
				act:'stopDaemons',
			},function answer(data){
				$('#running_status').html('<pre>'+data+'</pre>');
			});
		});
	});
	function showLogGglkwparse(){
		$.get("<?=HREF?>/?module=admin/keywords",{
			act:'showLog',
			log:'gglkwparse.log',
			tail:300
		},function answer(data){
			$('#gglkwparse').html(data);
		});
	}
</script>
<style type="text/css">
	fieldset{width: 50%}
	.log{width:100%;}
	legend{font-weight: bold;}
</style>
<button id="stopd" title="stop daemons, clear logs">Stop daemons</button>
<div id="running_status">
	<div>keword parse daemon: <?=$data->dGglkwparse?'<span style="color:green">running</span>':'<span style="color:red">not running</span>'?></div>
</div>
<fieldset><legend>keyword parse daemon log</legend><pre class="log" id="gglkwparse"></pre></fieldset>
