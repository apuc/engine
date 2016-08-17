<?php
/*
 * Входные двнные находятся в stdClass $data
 * */
$posts=$data->posts;
$forDesc=$data->forDesc;
$tpl->title="Posts by {$data->userData->authorName}".($data->page>1?" - Page {$data->page}":'');
$tpl->desc="{$tpl->title}.".(empty($forDesc)?'':' "'.implode('","',$forDesc).'"');
?>
<?if($data->accessPublish){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<div class="cols">
	<div class="left-col author">
		<h1>All articles by <?=$data->userData->authorName?></h1>
		<table border="0" style="width:100%;">
			<thead>
				<tr>
					<th>№</th>
					<th>Article</th>
					<th>Date</th>
					<th><img class="views_icon" alt="Views" title="Views" src="/files/template/icons/views_icon.png" /></th>
					<th><img class="count_photo_icon" alt="Count photos" title="Count photos" src="/files/template/icons/count_photo.png" /></th>
					<th>Preview</th>
				</tr>
			</thead>
			<tbody>
<?
	if (!empty($posts)) {
		$i = 1 + ($data->page-1)*20;
		foreach ($posts as $p) {
			$date = explode(' ', $p->datePublish);?>
			   <tr>
					<td><?=$i++;?></td>
					<td><a href="<?=url::post($p->url)?>" title="<?=$p->title?>"><?=$p->title?></a></td>
					<td><?=$date[0];?></td>
				   <td><?=$p->statViews?></td>
				   <td><?=$p->countPhoto?></td>
					<td>
						<span><img src="<?=url::imgThumb('50_',$p->imgs[0])?>" alt="<?=$p->title?>" title="<?=$p->title?>"/></span>
						<?if(isset($p->imgs[1])){?><span><img src="<?=url::imgThumb('250_',$p->imgs[1])?>" alt="<?=$p->title?>" title="<?=$p->title?>"/></span><?}?>
					</td>
				</tr>
				<?
	}
}
?>
		   </tbody>
		</table>
		<div class="clearfix"></div>
		<?=$data->paginator?><small>items:&nbsp<i><?=@$data->count?></i></small>
	</div>
	<div class="right-col">
		<?=$data->topLevelCats?>
	</div>
</div>