<?
/*
 * Special panel
*/
if($data->access->specPanel){
	ob_start();
	?>
	<?
	if($data->access->addNews){?>
		<li><a href="<?=url::post_adminAdd()?>">Add article</a></li>
		<li><a href="<?=url::author($data->user->id)?>">My articles</a></li>
	<?}if($data->access->listOfArticles){?>
		<li><a href="<?=url::post_byUser()?>">List of articles</a></li>
	<?}if($data->access->statPost){?>
		<li><a href="<?=url::post_stat()?>">Statistic of articles</a></li>
		<li><a href="<?=url::post_soc_stat()?>">Social stat</a></li>
	<?}if($data->access->moderateComments){?>
		<li><a href="<?=url::comments_admin()?>">Moderate comments</a></li>
	<?}if($data->access->moderImage){?>
		<li><a href="<?=url::imagesModerate()?>">Moderate images</a></li>
	<?}if($data->access->privilegeSet){?>
		<li><a href="<?=url::userPrivileges()?>">Privileges</a></li>
	<?}if($data->access->moveContent){?>
		<li><a href="<?=url::moveContentForm()?>">Move content</a></li>
	<?}if($data->access->autoPosting){?>
		<li><a href="<?=url::autoPosting()?>">AutoPosting</a></li>
	<?}if($data->access->update){?>
		<li><a href="<?=url::update()?>">Update</a></li>
	<?}if($data->access->voteAccess){?>
		<li><a href="<?=url::listVote()?>">Vote</a></li>
	<?}if($data->access->myVoteList){?>
		<li><a href="<?=url::listMyVote()?>">Check vote</a></li>
	<?}if($data->access->downloadImages){?>
		<li><a href="<?=url::downloadImages()?>">Download Images</a></li>
	<?}if($data->access->loadStruct){?>
		<li><a href="<?=url::post_uploadStruct()?>">Load Struct</a></li>
		<li><a href="<?=url::admin_multiPost()?>">Multi Post</a></li>
	<?}if($data->access->parseKeywords){?>
		<li><a href="<?=url::keywords_PostList()?>">Parse keywords</a></li>
	<?}if($data->access->parseRepl){?>
		<li><a href="<?=url::admin_traffic()?>">Repl stat</a></li>
	<?}if($data->access->themesSet){?>
		<li><a href="<?=url::admin_themes()?>">Set themes</a></li>
	<?}
	if($data->access->loadStruct && count($data->tbls)>1){?>
		<br><br>tables:<br>
		<?
		foreach($data->tbls as $tbl){
			?><li><a href="<?=url::category('',$tbl)?>"><?=$tbl?$tbl:'post'?></a></li><?
		}?>
	<?}
	$panelHtml=ob_get_clean();
	?>
	<?if($panelHtml!=''){?>
		<script>
		$('body').prepend('<div class="sp"><ul><?=str_replace("\n",'',$panelHtml)?></ul></div>');
		</script>
	<?}?>
<?}?>