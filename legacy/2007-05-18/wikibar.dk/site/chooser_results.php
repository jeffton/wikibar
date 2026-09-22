<?php

require_once "init.php";
require_once (PLUGINS_DIR ."/function.chooser_results.php");

$params['search'] = $_POST['search'];
$params['name'] = $_POST['name'];
$params['index'] = $_POST['index'];
$params['type'] = $_POST['type'];

smarty_function_chooser_results($params, $smarty);

?>