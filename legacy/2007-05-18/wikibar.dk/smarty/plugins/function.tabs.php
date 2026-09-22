<?php

function smarty_function_tabs($params, &$smarty) {
  $group = $params['group'];
  $labels = $params['labels'];
  $labels = explode(', ', $labels);
  $selected = $params['selected'];

  $smarty->assign('tabs_group', $group);
  $smarty->assign('tabs_labels', $labels);
  $smarty->assign('tabs_selected', $selected);
  $smarty->assign('tabs_count', count($labels));

  $smarty->display('components/tabs.tpl');  
}

?>