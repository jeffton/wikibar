<?php

function smarty_function_editor($params, &$smarty) {
  $smarty->assign('text', $params['text']);
  $smarty->display('components/editor.tpl');
}

?>