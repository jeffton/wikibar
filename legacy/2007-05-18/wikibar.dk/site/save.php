<?php

require_once "init.php";

$row = $_POST;

$type = $_POST['type'];

if (!$type) {
  Urls::redirect(HOST);
}

if ($type == 'user' && $row['id']) {
  Auth::authenticate($row['id'], false);
} else {
  Auth::authenticate(null, false);
}


$id = $database->saveRow($type, $row, $error);

if ($error) {
  $smarty->assign('error', $error);

  if ($type == "event") {
    unset($row['venue']);
    $row['venue']['name'] = $row['venuefield'];
    unset($row['venuefield']);

    $bandcount = max(count($row['bandfield']), count($row['bandHTML']));
    for ($i = 0; $i < $bandcount; $i++) {
      $row['bands'][$i]['name'] = $database->escape($row['bandfield'][$i]);
      $row['bands'][$i]['HTML'] = $row['bandHTML'][$i];
    }
    unset($row['bandHTML']);
    unset($row['bandfield']);
  }

  $smarty->assign('row', $database->escape($row, true));
  $smarty->display($type . '_edit.tpl');
} else {
  Urls::redirect(Urls::getUrl($type, $id, null, true));
}

?>