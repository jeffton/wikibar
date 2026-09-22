<?php

require_once 'init.php';

$id = $_REQUEST['id'];
$action = $_REQUEST['action'];
$type = $_REQUEST['type'];

if ($action == "edit") { // så skal vi auth'e
  if ($action == "edit" && $type == "user") {
    if ($id) {
      Auth::authenticate($id);
    } else {
      Urls::redirect(HOST); // ingen brugeroprettelse den vej rundt
    }
  } else {
    Auth::authenticate();
  }
}


$row = $database->getRow($type, $id);
if (is_null($row)) {
  $row = Array();
}


if ($action != "edit") {
  $prices = false;
  $address = false;
  $urls = false;


  foreach ($row as $key => $value) {
    if (substr($key, 0, 6) == 'price_') {
      if (!is_null($value)) {
        $prices = true;
      }
    } else if (substr($key, 0, 8) == 'address_') {
      if (!is_null($value)) {
        $address = true;
      }
    } else if (substr($key, 0, 4) == 'url_') {
      if (!is_null($value)) {
        $urls = true;
      }
    }
  }

  $info = ($address 
	|| $prices 
	|| $urls
	|| !is_null($row['music_starts_at'])
	|| !is_null($row['wardrobe'])
  );

  $smarty->assign('prices', $prices);
  $smarty->assign('address', $address);
  $smarty->assign('urls', $urls);
  $smarty->assign('info', $info);

  $row['text'] = $formatting->format($row['text']); 
}

$smarty->assign('row', $row);
$smarty->assign('type', $type);

if ($action == "edit") { // TIL REDIGERING
  $smarty->display($type . '_edit.tpl');
} else { // TIL VISNING
  if (!$row['id']) {
     Urls::redirect(Urls::getUrl($type, 0, null, true));
  }
  $smarty->display('display.tpl');
}

?>