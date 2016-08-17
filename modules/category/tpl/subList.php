<?
if(empty($data->sub)&&!$data->accessShowAll){?>
	<!---->
<?}else{?>
	<div>
	<?if($data->cur->id){?>
		<h2><?=$data->cur->title?><?=empty($data->funcPanel)?'':"&nbsp;{$data->funcPanel}"?></h2>
		<?if(!empty($data->sub)){?>
		<ul>
			<?foreach ($data->sub as $val){?>
			<li>
				<?if(!empty($val->funcPanel)){?><?=$val->funcPanel?><?}?>
				<a href="<?=url::category($val,$data->prfxtbl)?>"><?=$val->title?></a>&nbsp;<small title="count of posts">(<?=$val->count?>)</small>&nbsp;
			</li>
			<?}?>
		</ul>
		<?}?>
	<?}?>
	</div>
<?}?>
