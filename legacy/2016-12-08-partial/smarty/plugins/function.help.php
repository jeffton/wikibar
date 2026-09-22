<?php
function smarty_function_help($params, &$smarty) {
  $smarty->assign('helptopic', $params['topic']);
  $smarty->display('components/helplink.tpl');
}
?>