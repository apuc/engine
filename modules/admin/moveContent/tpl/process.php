<?php
$tpl->title="Move content";
$tpl->desc="";
?>

<style>
	#imgs4dl td {padding:2px 5px;}
</style>

<h1>Move content</h1>
<br>
<?=$data->countPosts!==false?"{$data->countPosts} post(s) was ".($data->type=='copy'?'copied':'merged').'.':'No posts to process.'?>
<br>
<br>

<?if(!empty($data->imgs4dl)){?>
	<script>
		var imgs4dl = <?=json_encode($data->imgs4dl)?>;
		var currImg = 0;
		var completedCnt = 0
		var queueLimit = 10;

		function addNextImageToDownloadQueue() {
			if (currImg < imgs4dl.length) {
				downloadImage(imgs4dl[currImg].id, imgs4dl[currImg].url, imgs4dl[currImg].link, checkDownloadProgress);
				currImg++;
			}
		}

		function checkDownloadProgress() {
			if (++completedCnt == imgs4dl.length)
				alert('All downloads are completed.');
		}

		function downloadImages() {
			for (var i = 0; i < queueLimit; i++)
				addNextImageToDownloadQueue();
		}

		function setImageStatus(id, status) {
			$('.status', '#img4dl_'+id).text(status);
		}

		function downloadImage(id, url, link, cb) {
			setImageStatus(id, 'Download');
			$.ajax({
				url: <?=json_encode(url::moveContentDownloadImage())?>,
				type: 'GET',
				data: {url: url, link: link},
				dataType: 'json',
				success: function(resp) {
					setImageStatus(id, resp.result ? 'Complete' : 'Error');
				},
				error: function() {
					setImageStatus(id, 'Error');
				},
				complete: function() {
					if (typeof(cb) == 'function')
						cb();
					addNextImageToDownloadQueue();
				}
			});
		}

		$(window).load(function() {
			downloadImages();
		});
	</script>

	Images for download (please wait):
	<table id="imgs4dl" border="1">
		<tr>
			<th>Image</th>
			<th>Status</th>
		</tr>
		<?foreach($data->imgs4dl as $img4dl){?>
		<tr id="img4dl_<?=$img4dl->id?>">
			<td class="image"><a href="<?=htmlspecialchars($img4dl->link)?>" target="_blank"><?=htmlspecialchars($img4dl->url)?></a></td>
			<td class="status">Pending</td>
		</tr>
		<?}?>
	</table>
	<br>
	<br>
<?}?>

<a href="<?=url::moveContentForm()?>">Move another content</a>
<br>
<br>
<br>


