<?php
function smarty_function_editbar($params, &$smarty) {
  $type = $params['type'];
  $id = $params['id'];
  $action = $params['action'];
  $class = $params['class'];

  $urls = Factory::getUrls();

  if ($action && $type) {
    if ($id) {
      $backurl = $urls->getUrl($type, $id);      
    } else {
      $backurl = $urls->getUrl($type);
    }
    $smarty->assign('backurl', $backurl);
    if ($action == "edit") {
      $smarty->assign('savebutton', true);
    }
  } else if ($type) {
    if ($id) {
      $editurl = $urls->getUrl($type, $id, 'edit');
      $smarty->assign('editurl', $editurl);
      $historyurl = $urls->getUrl($type, $id, 'history');
      $smarty->assign('historyurl', $historyurl);
    } else {
      $createurl = $urls->getUrl($type, 0, 'edit');
      $smarty->assign('createurl', $createurl);
    }

  }
  $smarty->assign('class', $class);
  $smarty->assign('type', $type);
  $smarty->display('components/editbar.tpl');
}
?>