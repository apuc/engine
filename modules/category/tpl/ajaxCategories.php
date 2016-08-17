<?php
if(empty($data->categories)) echo ' ';
echo implode('|', $data->categories);
?>