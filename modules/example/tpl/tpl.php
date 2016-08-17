<?php
/*
	Доступные переменные:
	- (std object) $tpl - данные для вывода в основной шаблон
	- (subTemplate object) $template - объект подшаблонов
		- $template->inc([path to sub template]) - метод подключения файлов подшаюблонов
	- (std object) $data - данные вернувшиеся из обработчика
*/
$tpl->title=NAME;
$tpl->desc="";

?>

<?
#include $template->inc('[template folder]/subtemplate.php');
?>

test
