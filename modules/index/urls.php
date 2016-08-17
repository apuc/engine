<?php
#маршрут
route::set('^/[^/]*$',array(),0);
route::set('^/main/([0-9]+)?/?$',array('page'),2);
#url модуля
?>