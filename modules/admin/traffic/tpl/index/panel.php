<style type="text/css">
	.traffic-panel>div:first-child{
		display: inline-block;
		padding: 0 5px 0 0;
	}
	.traffic-panel>div{
		display: inline-block;
		padding: 0 0 0 5px;
		vertical-align: top;
	}
	.traffic-menu a{padding: 2px 5px;}
	.traffic-menu a.active{background-color: #C1FFC8;}
	#cron{cursor: pointer;}
	#cron span{padding: 3px;}
</style>
<script type="text/javascript">
	$(document).ready(function(){
		cronstatus();
		$('#cron').click(function(){
			var type='';
			if($(this).children('span').html()=='On') type='unset';
			cronset(type);
		});
	});
	function cronstatus(){
		$.post(
			document.location.basepath+'?module=admin/traffic',
			{act:'statusCron'},
			function success(answer){
				var field=$('#cron span');
				if(answer=='on'){
					field.html('On');
					field.css({'background-color':'#c1ffc8'});
				}else if(answer=='off'){
					field.html('Off');
					field.css({'background-color':'#FF8A8A'});
				}else{
					field.html('error');
					field.css({'background-color':'#FF8A8A'});
				}
			}
		);
	}
	function cronset(type){
		$.post(
			document.location.basepath+'?module=admin/traffic',
			{act:'setCron','type':type},
			function success(answer){
				cronstatus();
			}
		);
	}
</script>
<div class="traffic-panel">
	<a target="_blank" href="<?=url::admin_traffic_rivals()?>">список конкурентов за выбранную дату</a><br/>
	<div><?=$data->dateForm?></div>
	<div>
		<form method="post" action="<?=url::admin_traffic_status()?>">
			<input type="submit" name="repl_parsing_run" value="Run parsing">
		</form>
		<div style="margin-top:3px;">
			<a href="<?=url::admin_traffic_status()?>">parsing status</a>
		</div>
	</div>
	<div id="cron" title="set up">
		cron: <span></span>
	</div>
</div>
<div class="traffic-menu">
	<a href="<?=url::admin_traffic()?>"<?=$data->act=='index'?' class="active"':''?>>Общая</a>
</div><br/>