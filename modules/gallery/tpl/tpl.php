<?
$img=&$data->img;
$post=&$data->post;
$prev=&$data->prev;
$next=&$data->next;

$img->tag=&$img->keyword_type;
?>
<!doctype html>
<html lang="en">
<head><?=timer(2);?>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="description" content="Gallery for <?=$img->title?> - image #<?=$img->num?>" />
	<?if(defined('FACEBOOKAPPID')){?><meta property="fb:app_id" content="<?=FACEBOOKAPPID?>" /><?}?>
	<?if(defined('FACEBOOKLANG')){?><meta property="og:locale" content="<?=FACEBOOKLANG?>" /><?}?>
	<?=@$data->meta?>
	<?=@$data->headlink?>
	<link href="<?=HREF?>/favicon.ico" rel="shortcut icon"/>
	<title><?=$img->title?> - image #<?=$img->num?></title>  
	<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<script type="application/javascript" src="<?=HREF?>/files/js/jquery-2.1.4.min.js"></script>
	<script type="application/javascript" src="<?=HREF?>/files/posts/js/gallery.js"></script>
	<script type="application/javascript" src="<?=HREF?>/files/posts/js/social-load.js"></script>
	<?include $template->inc('gallery/basejs.php');?>
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
		body{background-color: #000000;}	
		h1{margin:0 0 0.5em;color:white;text-transform: uppercase;float: left;}
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
		.bigimg{min-width: 300px;padding-top: 25px;vertical-align: top;width: 65%;}
		.bigimg .g-nav{width: 100%;clear: both;}
		.bigimg .g-nav div{display:inline-block;}
		.bigimg .g-nav .g-nav-count{font-size: 1.7em;text-align: center;vertical-align: top;width: 70%;padding-top: 50px;}
		.bigimg .g-source{text-align: right;}
		.bigimg .g-text{text-align: center;}
		.gal-right{padding-left: 35px;padding-top: 16px;vertical-align:top;}
		.topOfImg h1{display:inline;}
		.topOfImg .viewsNum{width: 80px;text-align: right;}
		.g-text small{float: right;}
		.gallery-content .breadcrumbs {margin-top: 12px;margin-bottom: 8px;}
		.gallery-content .breadcrumbs a {background-color: #E6E6E6;color: #607586 !important;font-size: 0.97em;padding: 2px 5px;text-decoration: none;text-transform: uppercase;}
		.gallery-content .breadcrumbs span{text-transform: uppercase;font-weight: bold;color: #607586;}
		/* tooltip */
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
</head>
<body>
<div>
	<?if($data->del==1){?>
		<center><a href="<?=url::imagesAdminDel($img->id)?>" onclick="return confirm('Are you sure?')" style="color:red;">
			<b>DELL THIS IMAGE</b>
		</a></center>
	<?}?>
	<div class="gallery-content clearfix">
		<div class="breadcrumbs">
		<?if(!empty($post->cats)){
			foreach ($post->cats as $val) {?>
				<a href="<?=url::category($val,$data->prfxtbl)?>"><?=$val->title?></a>
			<?}
		}?>
			<a href="<?=url::post($post->url,$data->prfxtbl)?>"><?=$post->title?></a>
		</div>
		<table>
			<tr>
				<td class="bigimg">
					<h1><?=$img->title?> Image #<?=$img->num?></h1>
					<span class="imgViews"><img src="<?=HREF?>/files/gallery/icons/img_views.png" /><span class="tooltip"><?=$img->statViews?><em>Total views:&nbsp;<?=$img->statViews?><i></i></em></span>&nbsp;/&nbsp;<span class="tooltip"><?=$img->statViewsShort?><em>Views for 7 days:&nbsp;<?=$img->statViewsShort?><i></i></em></span></span>
					<a href="<?=url::image($img->url)?>" target="_blank">
						<img style="width:100%;" 
							src="<?=url::image($img->url)?>" 
							alt="<?=$img->title." #".$img->num?>" 
							title="<?=$img->text?>" 
						/>
					</a>
					<div class="g-text"><?=$img->title?></div>
					<?include $template->inc('gallery/soc-buttons.php');?>
					<?include $template->inc('gallery/details.php');?>
					<?include $template->inc('gallery/usethis.php');?>
					<div style="clear:both;"></div><br/>
					<div class="g-nav">
						<div class="prev-next"><a href="<?=url::img($data->tbl,$post->id,$prev->url)?>" title="<?=$prev->title?>" rel="prev"><img src="/files/icons/arrow-left.png"/></a></div>
						<div class="g-nav-count"><?="{$img->num}&nbsp;of&nbsp;{$data->count}"?></div>
						<div class="prev-next"><a href="<?=url::img($data->tbl,$post->id,$next->url)?>" title="<?=$next->title?>" rel="next"><img src="/files/icons/arrow-right.png"/></a></div>
					</div>
					<?include $template->inc('gallery/related.php');?>
				</td>
				<td class="gal-right"></td>
			</tr>
		</table>
	</div>
</div>
<script type="text/javascript">window.location.hash='gal_<?=$data->tbl?>_<?=$post->id?>_'+encodeURI('<?=$img->url?>');</script>
<?include $template->inc('gallery/stat.php');?>
</body>
</html>
