<?php
function smarty_function_foot($params, &$smarty) {
  global $database;
  
  $user = $database->getRow('user', USERID, false);
  $smarty->assign('user', $user);
  
  $smarty->display('components/foot.tpl');
}


?>