<?if($data->mes){?>
    <script type="text/javascript">alert('<?=$data->mes?>');</script>
<?}else{?>
    <li>
        <span><?=$data->filename?></span><small class="del-icon del-tpl" title="delete"></small>
        <small class="new-file-icon newfile-tpl" title="new file"></small>
    </li>
<?}?>
<?php //var_dump($data->dir) ?>
