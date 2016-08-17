<?php
list($start,$stop,$params)=array_values((array)$data);
$period=array(
	'none'=>array('Период не выбран','',''),
	'today'=>array('Сегодня',date("Y-m-d"),date("Y-m-d")),
	'yesterday'=>array('Вчера',date("Y-m-d",strtotime('yesterday')),date("Y-m-d",strtotime('yesterday'))),
	//'tomorrow'=>array('Завтра',date("Y-m-d",strtotime('tomorow')),date("Y-m-d",strtotime('tomorow'))),
	'week'=>array('Последние 7 дней',date("Y-m-d",strtotime('-7 day')),date("Y-m-d",strtotime('-0 day'))),
	'thisweek'=>array('Эта неделя',date("Y-m-d",strtotime('monday this week')),date("Y-m-d",strtotime('sunday this week'))),
	'lastweek'=>array('Прошлая неделя',date("Y-m-d",strtotime('monday last week')),date("Y-m-d",strtotime('sunday last week'))),
	'thismonth'=>array('Этот месяц',date("Y-m-01"),date("Y-m-d")),
	'lastmonth'=>array('Прошлый месяц',date("Y-m-01",strtotime('last month')),date("Y-m-t",strtotime('last month'))),
	'fullTime'=>array('Все время',date("Y-m-d",0),date("Y-m-d")),
);
?>
<script type="text/javascript" src="<?=HREF?>/modules/plugins/dateForm/files/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#istart").datepicker({dateFormat: "yy-mm-dd"});
		$("#istop").datepicker({dateFormat: "yy-mm-dd"});
	});
	//
	period={
		<?
		$str=array();foreach($period as $k=>$v){@$str[]="'$k':Array('$v[1]','$v[2]')";}
		print implode(",",$str);
		?>
		}
	function showTime(show){
		if($("#stop").css("display")=='none' || show==1){
			$("#stop").show();
			$("#time").html('Выбрать период');
		}else{
			$("#stop").hide();
			$("#time").html('Выбрать день');
		}
		return false;
	}
	function changeTime(type,val){
		if(type=='start'){
			var start=val;
			var stop=$("#istop").val();
		}else if(type=='stop'){
			var start=$("#istart").val();
			var stop=val;
		}else{
			var start=period[val][0];
			var stop=period[val][1];
		}
		$("#istart").val(start);
		if($("#stop").css("display")=='none'){
			$("#istop").val(start);
			stop=start;
		}else{
			$("#istop").val(stop);
		}
		var set=0;
		for(var i in period){
			if(period[i][0]==start && period[i][1]==stop){
				$('#Period').removeAttr('selected');
				$('#Period').val(i).attr('selected',true);
				set=1;
				break;
			}
		}
		if(set==0)$('#Period').val('week').attr('selected',true);
	}
</script>
<style>
	@import url('<?=HREF?>/modules/plugins/dateForm/files/jquery-ui/jquery-ui.min.css');
	.dateBox{font-size:12px;}
	.dateBox fieldset{display:inline;padding: 5px;}
</style>
<form method="GET" action="<?=HREF?>">
	<?foreach($params as $k=>$v){?>
		<input type="hidden" name="<?=$k?>" value="<?=$v?>"/>
	<?}?>
	<div class="dateBox">
		<fieldset><legend><a href='#' id='time' onclick="return showTime()"></a></legend>
		<span id="start">от
		<input onchange="changeTime('start',this.value)" size="7" id="istart" name="start" value="<?=$start?>">
		</span>
		<span id="stop">до
		<input onchange="changeTime('stop',this.value)" size="7" id="istop" name="stop" value="<?=$stop?>">
		</span>
		</fieldset>
		<script>showTime(<?=$data->switchType?>);</script>
		<?if($data->switchType!=0){?>
			<fieldset><legend>Быстрый выбор</legend>
			<select id="Period" onchange="changeTime('',this.value);">
				<?foreach($period as $k=>$v){?>
					<option value="<?=$k?>"><?=$v[0]?></option>
				<?}?>
			</select>
			</fieldset>
		<?}?>
		<script>changeTime('start','<?=$start?>')</script>
		<fieldset><legend>Показать</legend>
		<input type="submit" value="Показать">
		</fieldset>
	</div>
</form>
