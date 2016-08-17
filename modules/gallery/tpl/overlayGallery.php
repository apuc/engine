<?
$img=&$data->img;
$post=&$data->post;
$prev=&$data->prev;
$next=&$data->next;
?>
<div>
	<script>
		$(document).ready(function(){
			$(window).keyup(function (event) {
				if ( event.keyCode == 37 ) window.location.hash=$('#navleft').attr('href');
				if ( event.keyCode == 39 ) window.location.hash=$('#navright').attr('href');
			});
		});
	</script>
	<style type="text/css">
		.overlay{
			-moz-user-select: none;
			left: 0;
			overflow-x: hidden;
			overflow-y: scroll;
			position: fixed;
			top: 0;
			width: 100%;
			z-index: 99999 !important;
			background: none repeat scroll 0 0 #000;
		}
		h1{margin:0 0 0.5em;color:white;text-transform: uppercase;float:left;}
		#overlayClose{
			color: #fff;
			display: block;
			font-family: 'Arial';
			font-size: 45px;
			font-weight: bolder;
			line-height: 50px;
			position: absolute;
			top: 0;
			right: 20px;
			cursor: pointer;
			text-decoration: none;
			text-shadow: 2px 2px 4px white;
		}
		#overlayClose:before {
			content: "×";
		}
		.imgViews{
			display: inline-block;
			float: right;
		}
		.imgViews img {
			vertical-align: middle;
			margin-top: -4px;
			margin-right: 5px;
		}
		.gallery-content{min-width:1024px;color:white;padding: 12px 5px 10px 25px;}
		.gallery-content a{color:white;}
		.prev-next{width:14%;}
		.prev-next a{}
		.prev-next img{width:100%;float:left;}
		.bigimg{min-width: 300px;padding-top: 25px;vertical-align: top;width: 650px;}
		.bigimg .g-nav{width: 100%;}
		.bigimg .g-nav div{display:inline-block;}
		.bigimg .g-nav .g-nav-count{font-size: 1.7em;text-align: center;vertical-align: top;width: 70%;}
		.gal-right{padding-top: 16px;vertical-align:top;}
		.text iframe{display: none;}
		.topOfImg h1{display:inline;}
		.topOfImg .viewsNum{width: 80px;text-align: right;}
		.gallery-content .tooltip{
			position:relative;
			z-index:1;
			zoom:1;
			cursor: pointer;
		}
		.gallery-content .tooltip em{display:none;}
		.gallery-content .tooltip:hover em{
			display:block;
			position:absolute;
			z-index:1;
			background-color:#e6e6e6;
			-webkit-border-radius:5px;
			-moz-border-radius:5px;
			border-radius:5px;
			line-height:normal;
			color:#607586;
			text-decoration:none;
			padding:3px 5px;
			bottom:22px;
			right:0;
			-webkit-box-shadow:0 0 5px #e6e6e6;
			-moz-box-shadow:0 0 5px #e6e6e6;
			box-shadow:0 0 5px #e6e6e6;
		}
		.gallery-content .tooltip:hover em i{
			position:absolute;
			z-index:1;
			bottom:-7px;
			right:5px;
			border-top:7px solid #e6e6e6;
			border-left:7px solid transparent;
			_border-left:7px solid #e6e6e6;
			display:block;
			height:0;
			overflow:hidden;
		}
	</style>
	<a id="overlayClose" title="close gallery" onclick="closeOverlay();"></a>
	<?if($data->del==1){?>
		<center><a href="<?=url::imagesAdminDel($img->id)?>" onclick="return confirm('Are you sure?')" style="color:red;">
			<b>DELL THIS IMAGE</b>
		</a></center>
	<?}?>
	<div class="gallery-content clearfix">
		<center>
		<table>
			<tr><td colspan=3>
				<?if(!empty($post->cats)){?>
				<div class="breadcrumbs">
					<?foreach ($post->cats as $val) {?>
						<a href="<?=url::category($val,$data->prfxtbl)?>"><?=$val->title?></a>
					<?}?>
				</div>
				<?}?>
			</td></tr>
			<tr>
				<td class="bigimg">
					<h1><?=$img->title?> Image #<?=$img->num?></h1>
					<span class="imgViews"><img src="<?=HREF?>/files/gallery/icons/img_views.png" /><span class="tooltip"><?=$img->statViews?><em>Total views:&nbsp;<?=$img->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$img->statViewsShort?><em>Views for 7 days:&nbsp;<?=$img->statViewsShort?><i></i></em></span></span>
					<center>
						<iframe style="width:735px;height:100px;" src="/?module=gallery&act=overlayAds&type=top" frameborder="0" scrolling="no"></iframe>
					</center>
					<img style="width:100%;" src="<?=url::image($img->url)?>" alt="<?=$img->title." #".$img->num?><?=!empty($img->tag)?" $img->tag":''?>" title="<?=$img->title." #".$img->num?><?=!empty($img->tag)?" $img->tag":''?>" />
					<center>
						<iframe style="width:735px;height:100px;" src="/?module=gallery&act=overlayAds&type=bottom" frameborder="0" scrolling="no"></iframe>
					</center>
					<a href="<?=url::post($post->url,$data->prfxtbl)?>" style="padding-right:10px;float:left;"><?=$post->title?></a>
					<div class="props" style="text-align:right;">
						<a href="<?=url::image($img->url)?>" target="_blank" title="<?=$img->title." #".$img->num?>"><?=$img->url?></a>&nbsp;
						<?=$data->resolution?"{$data->resolution} px, ":'';?>
						<?=$data->size?"{$data->size} KB, ":'';?>
						<?=$data->author?$data->author:'';?>
					</div>
					<div class="g-nav">
						<div class="prev-next"><a id="navleft" href="<?=url::imgOverlay($data->tbl,$post->id,$prev->url)?>" title="<?=$prev->title?>"><img src="/files/icons/arrow-left.png"/></a></div>
						<div class="g-nav-count"><?="{$img->num}&nbsp;of&nbsp;{$data->count}"?></div>
						<div class="prev-next"><a id="navright" href="<?=url::imgOverlay($data->tbl,$post->id,$next->url)?>" title="<?=$next->title?>"><img src="/files/icons/arrow-right.png"/></a></div>
					</div>
				</td>
				<td style="width:35px">&nbsp;</td>
				<td class="gal-right;vertical-align:middle;">
					<iframe style="width:170px;height:620px;" src="/?module=gallery&act=overlayAds&type=right" frameborder="0" scrolling="no"></iframe>
				</td>
			</tr>
		</table>
		</center>
	</div>
</div>
