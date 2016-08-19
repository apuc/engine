<?php
/**
 * @var $data object
 */
?>
    <div id="themefiles">
        <h2><?= $data->theme ?></h2>
        <?= listRec($data->dir, $data) ?>
        <? function listRec($list, $d)
        { ?>
            <ul>
                <? foreach ($list as $key => $v): ?>
                    <?php if (is_array($v)): ?>
                        <li<?= !count($v) ? ' style="color:#CBCBCB;"' : '' ?>>
                            <span><?= $key ?></span>
                            <small class="del-icon del-tpl" title="delete"></small>
                            <small class="new-file-icon newfile-tpl" title="new file"></small>
                            <? listRec($v, $d) ?>
                        </li>
                    <? else: ?>
                        <?php if (\admin_themes\isHistory($key)): ?>
                            <?php if ($d->history): ?>
                                <li><a style="cursor:pointer;"><?= $key ?></a>
                                    <small class="del-icon del-tpl" title="delete"></small>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a style="cursor:pointer;"><?= $key ?></a>
                                <small class="del-icon del-tpl" title="delete"></small>
                            </li>
                        <?php endif; ?>
                    <?php endif ?>
                <?php endforeach; ?>
            </ul>
        <? } ?>
    </div>
