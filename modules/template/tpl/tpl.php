<!DOCTYPE html>
<html>
<head><?=timer(2);?>
<meta charset="utf-8">
<meta name="description" content="<?=@htmlspecialchars($data->desc)?>" />
<?if(defined('FACEBOOKAPPID')){?><meta property="fb:app_id" content="<?=FACEBOOKAPPID?>" /><?}?>
<?if(defined('FACEBOOKLANG')){?><meta property="og:locale" content="<?=FACEBOOKLANG?>" /><?}?>
<?=@$data->meta?>
<?=@$data->headlink?>
<link href="<?=HREF?>/favicon.ico" rel="shortcut icon"/>
<link rel="stylesheet" type="text/css" href="<?=HREF?>/style.css?default=1"/>
<title><?=@$data->title?></title>
<script type="application/javascript" src="<?=HREF?>/files/js/jquery-2.1.4.min.js"></script>
<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if lt IE 9]>
<script type="text/javascript" src="<?=HREF?>/files/js/jquery-1.8.3.min.js"></script>
<![endif]-->
<?include $template->inc('template/basejs.php');?>
</head>
<body><?=$data->status?>
<div class="wrapper">
	<?include $template->inc('template/header.php');?>
	<div class="content clearfix">
	<?=$data->body?>
	</div>
</div>
<?include $template->inc('template/footer.php');?>
<?=$data->panel?>
<?include $template->inc('template/stat.php');?>
</body>
</html>