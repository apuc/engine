<?if(empty($data->breadCrumbs))return;?>
<div class="breadcrumbs">
	<a href="<?=HREF?>">HOME</a> >
	<?foreach($data->breadCrumbs as $val){
		if($val->url==$cat->url){
			print "<h1>$val->title</h1>";
			continue;
		}?>
		<a href="<?=url::category($val->url,$data->prfxtbl)?>" title="<?=$val->title?>"><?=$val->title?></a>
	<?}?>
</div>