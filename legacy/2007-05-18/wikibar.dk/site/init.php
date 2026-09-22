<?php

require_once '../config.php';

define('HOST', $host);
date_default_timezone_set($timezone);

// smarty setup
define('SMARTY_DIR', $smartylib);
require_once(SMARTY_DIR . 'Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = $smartylocal . 'templates';
$smarty->compile_dir =  $smartylocal . 'templates_c';
$smarty->config_dir =   $smartylocal . 'config';
$smarty->cache_dir =    $smartylocal . 'cache';
define('PLUGINS_DIR',   $smartylocal . 'plugins'); // den bruger vi senere
$smarty->plugins_dir[] = PLUGINS_DIR;

$smarty->assign('scriptversion', 1);

// klasser & funktioner der skal bruges
require_once "../../_utf8/utf8.php";
require_once "../../_utf8/ucfirst.php";
require_once "../../_utf8/strcasecmp.php";

require_once "../classes/database.php";
require_once "../classes/formatting.php";
require_once "../classes/urls.php";
require_once "../classes/datetools.php";
require_once "../classes/auth.php";

$database = new database($dbhost, $dbuser, $dbpass, $dbbase);
$formatting = new Formatting();
$datetools = new Datetools();

// auth
define('USERID', $_SESSION['userId']);

unsetConfigVars();

?>