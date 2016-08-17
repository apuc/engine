<?php
#url модуля
$GLOBALS['urls']->image=HREF.'/images/$url';
$GLOBALS['urls']->imageDownload=HREF.'?module=images&act=download&file=$url';
$GLOBALS['urls']->imageDownloadResize=HREF.'?module=images&act=downloadResize&file=$url&x=$x&y=$y';
$GLOBALS['urls']->imgThumb=HREF.'/images$size/$url';# examples size: 600_, 200_150
