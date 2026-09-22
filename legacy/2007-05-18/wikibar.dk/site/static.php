<?php

require_once "init.php";

$template = $_GET['page'];
if (strpos($template, '/') !== FALSE) {
  die('stop hax');
}
@$smarty->display("static/$template.tpl");

?>