<style type="text/css">
	legend{font-weight: bold;}
</style>
<script>
	$(document).ready(function () {
		setInterval(
			function (){
				$.get("<?=url::admin_multiPostShowLog()?>",{
					tail:100
				},function answer(data){
					$('#log').html(data);
				});
			}
			,1000
		);
	});
</script>
<table width=100%><tr>
	<td valign=top><fieldset><legend>script log</legend><pre class="log" id="log"></pre></fieldset></td>
</tr></table>
 
