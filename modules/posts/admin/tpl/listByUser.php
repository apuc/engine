<?php
$tpl->title="Articles by author: ".(empty($data->userData)?'all authors':$data->userData->longName);
$tpl->desc="";
$posts=$data->posts;
?>
<div class="breadcrumbs">
	<a href="<?=url::post_byUser(0,$data->type)?>"><?=$data->type=='text'?'With text':'Empty articles'?></a>
	<a><?=empty($data->userData)?'all users':$data->userData->longName?></a>
</div>
<?if($data->accessPublished){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/published.js"></script>
<?}?>
<?if($data->accessHistory){?>
	<script type="application/javascript" src="<?=HREF?>/files/posts/admin/js/jquery-ui.min.js"></script>
	<script type="application/javascript">
		$(function() {
			show_diff = function(e) {
				var a = $(e);
				var url = a.attr('href');
				$.ajax({
					url: url,
					success: function(data) {
						var dialog = $('<div></div>');
						dialog.html(data);
						dialog.dialog({
							title: 'Diff',
							minWidth: 900,
						});
					},
				});
			};
		});
	</script>
	<style type="text/css">
		@import '<?=HREF?>/files/posts/admin/css/jquery-ui.css';
		p {margin-top:0}
		ins {color:green;background:#dfd;text-decoration:none}
		del {color:red;background:#fdd;text-decoration:none}
	</style>
<?}?>
<div class="cols">
	<div class="left-col main-list">
		<form action="<?=HREF?>?module=posts/admin&act=setAuthors" method="POST">
		<div class="models">
			<?foreach($posts as $p){?>
			<div>
				<?if(!empty($p->img)){?>
				<a href="<?=url::post($p->url)?>" title="<?=$p->title?>"><img src="<?=url::imageOverviewThumb('250_',$p->img)?>" title="<?=$p->title?>" alt="<?=$p->title?>"/></a>
				<?}?>
				<div class="model-text">
					<?=$p->funcPanel?>		
					<div class="model-title">
						<?if($data->type!='history'&&$data->editorList) { ?>
							<input type="checkbox" name="select_author_checkbox[<?=$p->id?>]"/> &nbsp;
						<?}?>
						<a href="<?=url::post($p->url)?>" title="<?=$p->title?>"><?=$p->title?></a>
					</div>
					<small><?=$p->datePublish?$p->datePublish:$p->date?>&nbsp;/&nbsp;<img class="author_icon" alt="Author" title="Author" src="<?=HREF?>/files/template/icons/author_icon.png" />
					<?if(!empty($p->authorMail)){?><a href="<?=url::author($p->user)?>"><i><?=$p->authorName?></i></a><?}
					else{?><i><?=$p->authorName?></i><?}?>
					</small>
					<small style="clear:both;float:right;">
						status:&nbsp;<?=$p->published?>&nbsp;|
						<img class="views_icon" alt="Views" title="Views" src="<?=HREF?>/files/template/icons/views_icon.png" />&nbsp;<span class="tooltip"><?=$p->statViews?><em>Total views:&nbsp;<?=$p->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$p->statViewsShort?><em>Views for 7 days:&nbsp;<?=$p->statViewsShort?><i></i></em></span>
					</small>
					<p><?=$p->txt?></p>
				</div>
			</div>
			<?}?>
			<?if($data->type!='history'&&$data->editorList){?>
				<script type="text/javascript">
					$(function() {
						select_author_all = function(e) {
							var select = $(e);
							var checkboxes = $('[name^=select_author_checkbox]');
							if (select.prop('checked')) {
								checkboxes.prop('checked', true);
							} else {
								checkboxes.prop('checked', false);
							}
						}
					});
				</script>
				<label><input type="checkbox" onchange="select_author_all(this);"/> &nbsp; Select all</label>
				<br/>
				<br/>
				<select name="select_author_options">
				<?foreach ($data->authors as $author){?>
					<option value="<?=$author->id?>"><?=$author->longName?></option>
				<?}?>
				</select> &nbsp; <input type="submit" value=" Apply " style="vertical-align: initial;"/>
				<br/>
				<br/>
			<?}?>
			<?=$data->paginator?>
		</div>
		</form>
	</div>
	<div class="right-col">
		<h4>Types:</h4>
		<ul>
			<?
				$user_id=empty($data->userData->id)?'':$data->userData->id;
			?>
			<?if($data->authorList){?>
				<li><a <?=($data->type=='todo')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'todo')?>">To do</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->author_todo)?>)</li>		
			<?}
			if($data->authorList||$data->searcherList){?>
				<li><a <?=($data->type=='remake')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'remake')?>">Remake</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->author_remake)?>)</li>			
				<li><a <?=($data->type=='moderate')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'moderate')?>">Moderate</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->author_moderate)?>)</li>
			<?}?>
			<?if($data->editorList){?>
				<li><a <?=($data->type=='notext')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'notext')?>">Empty articles</a>&nbsp;(<?=@(int)array_sum((array)$data->postsCounter->notext)?>)</li>
				<li><a <?=($data->type=='text')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'text')?>">With text</a>&nbsp;(<?=@(int)array_sum((array)$data->postsCounter->text)?>)</li>
				<li><a <?=($data->type=='unpublished')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'unpublished')?>">Not published</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->unpublished)?>)</li>
				<?if($data->accessAutoPosting){?>
				<li><a <?=($data->type=='autoposting')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'autoposting')?>">Auto Posting</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->autoposting)?>)</li>
				<?}?>
				<li><a <?=($data->type=='researchdone')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'researchdone')?>">Research Done</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->researchdone)?>)</li>			
				<li><a <?=($data->type=='researchchecked')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'researchchecked')?>">Research checked</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->researchchecked)?>)</li>
				<li><a <?=($data->type=='waitcopywrite')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'waitcopywrite')?>">Wait copywriting</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->waitcopywrite)?>)</li>
				<li><a <?=($data->type=='remakesearch')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'remakesearch')?>">Remake search</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->remakesearch)?>)</li>
				<li><a <?=($data->type=='remakecontent')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'remakecontent')?>">Remake content</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->remakecontent)?>)</li>
				<li><a <?=($data->type=='readytopublish')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'readytopublish')?>">Ready to publish</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->readytopublish)?>)</li>
				<li><a <?=($data->type=='published')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'published')?>">Published</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->published)?>)</li>
				<li><a <?=($data->type=='history')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'history')?>">History</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->history)?>)</li>
				<li><a <?=($data->type=='free_posts')?"style='font-weight:bold;'":''?> 
					href="<?=url::post_byUser($user_id,'free_posts')?>">Free posts</a>&nbsp;(<?=@array_sum((array)$data->postsCounter->free_posts)?>)</li>
				<li><a href="<?=url::post_byUser(0,$data->type)?>">without user</a>&nbsp;(<?=@(int)$data->postsCounter->{$data->type}[0]?>)</li>
			<?}?>
		</ul>
	<?if(!empty($data->usersList) && $data->editorList){?>
		<?
		$roles = array();
		foreach ($data->usersList as $u){
			if (!isset($roles[$u->rbac])) {
				$roles[$u->rbac] = array();
			}
			$roles[$u->rbac][] = "
				<li><a ".((@$data->userData->id==$u->id)?"style='font-weight:bold;'":'')." href=\"".url::post_byUser($u->id,$data->type)."\">{$u->longName}</a>&nbsp;(".(@(int)$data->postsCounter->{$data->type}[$u->id]).")</li>
			";
		}?>
		<?if (isset($roles[4])){?>
			<h4>Editors:</h4>
			<ul>
				<?php foreach ($roles[4] as $row) {
					echo $row;
				} ?>
			</ul>
		<?}if(isset($roles[5])){?>
			<h4>Searchers:</h4>
			<ul>
				<?php foreach ($roles[5] as $row){
					echo $row;
				}?>
			</ul>
		<?}if(isset($roles[2])){?>
			<h4>Authors:</h4>
			<ul>
				<?php foreach ($roles[2] as $row) {
					echo $row;
				} ?>
			</ul>
		<?}?>
	<?}?>
	</div>
</div>
