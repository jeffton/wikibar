<?php

require_once "init.php";

$action = $_REQUEST['action'];

if (Auth::authed()) {
  $action = "logout";
}

if ($action == "logout") {
  unset($_SESSION['userId']);
  Urls::redirect(HOST);
  die();
}
if (!$proceed) {
  $proceed = $_REQUEST['proceed'];
  if (!$proceed) {
    $proceed = $_SERVER['HTTP_REFERER'];
  }
}
if ($proceed == HOST . 'login.php') { // så ville man havne på login-siden efter login
  $proceed = HOST;
}

if (!$passthrough) {
  $passthrough = $_REQUEST['passthrough'];
}

if ($action == "login" || $action == "create") {
  $name = $_POST['name'];
  $password = $_POST['password'];
  if ($action == "login") {
    $userId = $database->checkLogin($name, $password);
    if (!$userId) {
      $error = "Forkert brugernavn eller password.";
    }
  } else if ($action == "create") {
    $passwordrepeat = $_POST['passwordrepeat'];
    $row = array('name' => $name, 'password' => $password,
        'passwordrepeat' => $passwordrepeat);
    $userId = $database->saveRow('user', $row, $error);
  }

  if ($userId) {
    $_SESSION['userId'] = $userId;
    define('USER_ID', $userId);
    Urls::redirect($proceed);
  } else {
    $smarty->assign("error_$action", $error);
    $smarty->assign('action', $action);
    $selectedtab = ($action == "login" ? 1 : 2);

    $smarty->assign("name_$action", htmlspecialchars($name));
    $smarty->assign("password_$action", htmlspecialchars($password));
    $smarty->assign("passwordrepeat", htmlspecialchars($passwordrepeat));
  }
}
if (!$selectedtab) {
  $selectedtab = 1;
}
for ($i = 1; $i<=2; $i++) {
  $tabselected[$i] = ($selectedtab == $i);
}

$smarty->assign('passthrough', htmlspecialchars($passthrough));
$smarty->assign('proceed', htmlspecialchars($proceed));
$smarty->assign('back', htmlspecialchars($_SERVER['HTTP_REFERER']));
$smarty->assign('selectedtab', $selectedtab);
$smarty->assign('tabselected', $tabselected);
$smarty->display('login.tpl');

?>