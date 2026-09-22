<?php
function smarty_function_editbar($params, &$smarty) {
  $type = $params['type'];
  $id = $params['id'];
  $action = $params['action'];
  $class = $params['class'];

  if ($action=="edit" && $type) {
    if ($id) {
      $backurl = Urls::getUrl($type, $id);
    } else {
      $backurl = Urls::getUrl($type);
    }
    $smarty->assign('backurl', $backurl);
    $smarty->assign('savebutton', true);
  } else if ($type) {
    if ($id) {
      $editurl = Urls::getUrl($type, $id, 'edit');
      $smarty->assign('editurl', $editurl);
    } else {
      $createurl = Urls::getUrl($type, 0, 'edit');
      $smarty->assign('createurl', $createurl);
    }

  }
  $smarty->assign('class', $class);
  $smarty->assign('type', $type);
  $smarty->display('components/editbar.tpl');
}
?>