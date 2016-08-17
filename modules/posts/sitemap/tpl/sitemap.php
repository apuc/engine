<?php
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?foreach ($data->posts as $v) {?>
	<url>
		<loc><?=url::post($v->url)?></loc>
		<lastmod><?=date(DATE_W3C,strtotime($v->datePublish))?></lastmod>
		<priority>0.8</priority>
		<?if(is_array($v->imgs))foreach($v->imgs as $img){?>
		        <image:image>
                    <image:loc><?=url::image($img->url)?></image:loc>
                </image:image>
		<?}?>
	</url>
<?}?>
</urlset>
