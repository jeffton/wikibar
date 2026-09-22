<?php

require_once 'init.php';

Auth::authenticate();

$id = $_REQUEST['id'];
$row = $database->getEvent($id);

$smarty->assign('row', $row);

$smarty->display('event_edit.tpl');


?>