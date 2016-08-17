<?php
header("content-type: application/rss+xml");
$tpl->title=htmlentities("Blog. Fun pics & images - ".NAME);
$tpl->desc=htmlentities("Discover our funny website, interesting stories and beautiful media content.");
echo '<?xml version="1.0"?>';
?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title><?=$tpl->title?></title>
    <link><?=HREF?></link>
    <description><?=$tpl->desc?></description>
    <language>en-us</language>
    <lastBuildDate><?=date(DateTime::RSS,strtotime(current($data->posts)->datePublish))?></lastBuildDate>
    <?foreach($data->posts as $v){?>
    <item>
      <media:thumbnail url="<?=url::imgThumb('600_',$v->imgs[0])?>" width="250"/>
      <title><?=$v->title?></title>
      <link><?=url::post($v->url)?></link>
      <description><?=$v->txt?></description>
      <pubDate><?=date(DateTime::RSS,strtotime($v->datePublish))?></pubDate>
      <guid><?=url::post($v->url)?></guid>
    </item>
    <?}?>
  </channel>
</rss>
