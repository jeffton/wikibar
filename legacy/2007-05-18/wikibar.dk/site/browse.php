<?php

require_once "init.php";

$search = $_REQUEST['search'];
$page = $_REQUEST['page'];
$type = $_REQUEST['type'];
$database->validTypeOrDie($type, "browse.php");

if ($page) {
  $results = $database->getListingByPage($type, $page);
} else {
  $results = $database->getListing($type, $search);
}

$smarty->assign(results, $results);
$search = htmlspecialchars($search);
$smarty->assign('search', $search);
$smarty->assign('title', $formatting->typeName($type, true, true));
$smarty->assign('type', $type);

$pageletters = $database->getPageLetters($type);
$smarty->assign('pageletters', $pageletters);
$smarty->assign('pages', count($pageletters) > 1);

$smarty->display('browse.tpl');


?>