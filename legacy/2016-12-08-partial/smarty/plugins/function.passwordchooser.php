<?php

function smarty_function_passwordchooser($params, &$smarty) {
  $row = $params['row'];
  if (!$row) {
    $row = $params;
  }
  
  $password = $row['password'];
  $passwordrepeat = $row['passwordrepeat'];
  
  $smarty->assign('passwordchooser_password', $password);
  $smarty->assign('passwordchooser_passwordrepeat', $passwordrepeat);
  $smarty->assign('passwordchooser_edit', $params['edit']);
  $smarty->assign('passwordchooser_helptopic', ($params['edit'] ? 'user_password_edit' : 'user_password'));
  $smarty->display('components/passwordchooser.tpl');
}

?>