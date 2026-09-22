<?php
function smarty_function_chooser_results($params, &$smarty) {
  $rowApi = Factory::getRowApi();

  $search = trim($params['search']);
  if (!$search) {
    echo "&nbsp;";
    return;
  }

  $type = $params['type'];
  $selected = isset($params['selected']) ? $params['selected'] : null;
  $name = $params['name'];
  $index = $params['index'];
  if ($index) {
    settype($index, 'int');
  }

  $search_unescaped = html_entity_decode($search);
  $results = $rowApi->getListing($type, $search_unescaped, 4);
  $createallowed = ($type != 'user' && !$rowApi->nameTaken($type, $search_unescaped));

  $smarty->assign('results_createallowed', $createallowed);
  $smarty->assign('results_type', $type);
  $smarty->assign('results_selected', $selected);
  $smarty->assign('results_name', $name . "[" . $index . "]");
  $smarty->assign('results', $results);
  $smarty->assign('results_search', $search);

  $smarty->display('components/chooser_results.tpl');
}
?>