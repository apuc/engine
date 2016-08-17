<?php
#Урлы модуля
$GLOBALS['urls']->post_adminAdd=HREF.'/?module=posts/admin&act=edit&parentID=$parent&prfxtbl=$prfx';
$GLOBALS['urls']->post_adminEdit=HREF.'/?module=posts/admin&act=edit&pid=$pid&prfxtbl=$prfx';
$GLOBALS['urls']->post_adminDel=HREF.'/?module=posts/admin&act=del&pid=$pid&prfxtbl=$prfx';
$GLOBALS['urls']->post_byUser=HREF.'/?module=posts/admin&act=listByUser&user=$uid&type=$type&page=$page';
$GLOBALS['urls']->post_stat=HREF.'/?module=posts/admin&act=stat&user=$uid';
$GLOBALS['urls']->post_soc_stat=HREF.'/?module=posts/admin&act=socStat';
$GLOBALS['urls']->post_diff=HREF.'/?module=posts/admin&act=diff&pid=$pid';
$GLOBALS['urls']->post_archive_diff=HREF.'/?module=posts/admin&act=diff&from=FROM_PID&to=TO_PID';
$GLOBALS['urls']->post_archive=HREF.'/?module=posts/admin&act=archive&pid=$pid';
