<?php

require_once "init.php";

$topic = $_REQUEST['topic'];

$smarty->display("help/$topic.tpl");

?>