<?php
function smarty_function_chooser_results($params, &$smarty) {
  global $database;
  $search = trim($params['search']);
  if (!$search) {
    echo "&nbsp;";
    return;
  }
  $type = $params['type'];
  $selected = $params['selected'];
  $name = $params['name'];
  $index = $params['index'];
  if ($index) {
    settype($index, 'int');
  }

  $results = $database->getListing($type, $search, 4);
  $createallowed = ($type != 'user' && !$database->nameTaken($type, $search));

  $smarty->assign('results_createallowed', $createallowed);
  $smarty->assign('results_type', $database->escape($type));
  $smarty->assign('results_selected', $selected);
  $smarty->assign('results_name', $database->escape($name) . "[" . $index . "]");
  $smarty->assign('results', $results);
  $smarty->assign('results_search', $database->escape($search));

  $smarty->display('components/chooser_results.tpl');
}
?>