<div class="breadcrumbs">
<?foreach ($data->breadCrumbs as $val) {?>
	<a <?=($val->url==$cat->url?'':'href="'.url::category($val->url,$data->prfxtbl).'"')?> title="<?=$val->title?>"><?=$val->title?></a>
<?}?>
</div>