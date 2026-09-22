<?php
function smarty_function_datechooser($params, &$smarty) {
  $value = $params['value'];
  $name = $params['name'];
  $text = $params['text'];
  $smarty->assign('datechooser_name', $name);
  $smarty->assign('datechooser_value', $value);
  $smarty->assign('datechooser_text', $text);
  $smarty->display('components/datechooser.tpl');
}
?>