<a href="<?=url::admin_multiPostStatus()?>">status</a><br>
Count all posts: <?=$data->cnPosts?> 
<table width="100%" border=1>
	<form action="<?=url::admin_multiPostSave()?>"  method="POST">
		<thead>
			<tr>
				<th width="40">Pid</th>
				<th width="125">Date</th>
				<th>Title</th>
				<th>Count images 
					<a href="<?=url::admin_multiPost()."&order=DESC"?>">+</a>/
					<a href="<?=url::admin_multiPost()."&order=ASC"?>">-</a>
				</th>
				<th>Count Keywords
				</th>
				<th>Use</th>
			</tr>
		</thead>
		<?
		foreach($data->posts as $pid=>$post){
			$i=0;
			echo '<tbody>';
				?>
				<tr>
					<td><?=$post->id?></td>
					<td><?=$post->datePublish?></td>
					<td><a href="<?=url::post($post->url)?>"><?=$post->title?></a></td>
					<td><?=$post->countPhoto?></td>
					<td><?=$post->kcn?></td>
					<td><label><input type="checkbox" name="pids[]" value="<?=$post->id?>" checked="checked"/></label></td>
				</tr>
				<?
			echo '</tbody>';
			?>
		<?}?>
	</table>
	<br>
	<center><input type="submit" value="create new posts"></center>
	<br>
	</form>
	<?=$data->paginator?>
</div>

