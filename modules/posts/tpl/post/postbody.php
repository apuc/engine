<div class="title"><h1><?=$post->title?></h1></div>
<small><?=$post->datePublish?$post->datePublish:$post->date?>&nbsp;/&nbsp;author:
<?if(!empty($post->user->mail)){?><a href="<?=url::author($post->user->id)?>"><i><?=$post->user->name?></i></a><?}
else{?><i><?=$post->user->name?></i><?}?>
</small>
<small style="float:right;">
	views&nbsp;<span class="tooltip"><?=$post->statViews?><em>Total views:&nbsp;<?=$post->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$post->statViewsShort?><em>Views for 7 days:&nbsp;<?=$post->statViewsShort?><i></i></em></span>
</small>
<?=$post->txt?>
<?if($post->data!=''){?><pre><?print_r($post->data);?></pre><?}?>
<?if(empty($post->txt)){?>
	<?if(isset($data->post->imgs[5])){
		$img=$data->post->imgs[5];
		if(!empty($img)){?>
			<a href="<?=url::img('post',$img->pid,$img->url)?>">
				<img src="<?=url::image($img->url)?>" width="640">
			</a><br>
			<?=$img->text?>
			<br/><br/>
		<?}
	}
	if(isset($data->post->imgs[6])){
		$img=$data->post->imgs[6];
		if(!empty($img)){?>
			<a href="<?=url::img('post',$img->pid,$img->url)?>">
				<img src="<?=url::image($img->url)?>" width="640">
			</a><br>
			<?=$img->text?>
			<br/><br/>
		<?}
	}
	if(isset($data->post->imgs[7])){
		$img=$data->post->imgs[7];
		if(!empty($img)){?>
			<a href="<?=url::img('post',$img->pid,$img->url)?>">
				<img src="<?=url::image($img->url)?>" width="640">
			</a><br>
			<?=$img->text?>
			<br/><br/>
		<?}
	}
}?>
