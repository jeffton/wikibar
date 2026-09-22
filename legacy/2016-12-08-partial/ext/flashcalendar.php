<?php

require_once '../init.php';

$userid = $_GET['user'];
settype($userid, 'int');

$smarty->assign('user', $userid);

echo '&calendar=' . urlencode($smarty->fetch('ext/flashcalendar.tpl'));

?>