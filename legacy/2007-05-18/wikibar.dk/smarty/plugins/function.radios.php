<?php

function smarty_function_radios($params, &$smarty) {
  $name = $params['name'];
  $values = $params['values'];
  $labels = $params['labels'];
  $checked = $params['checked'];
  $values = explode(', ', $values);
  $labels = explode(', ', $labels);

  $smarty->assign('radio_name', $name);
  $smarty->assign('radio_values', $values);
  $smarty->assign('radio_labels', $labels);
  $smarty->assign('radio_checked', $checked);
  $smarty->display('components/radios.tpl');
}

?>