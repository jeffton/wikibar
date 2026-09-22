<?php

function smarty_block_tabpane($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    $smarty->assign('tabpane_id', $params['group'] . 'pane' . $params['index']);
    $smarty->assign('tabpane_selected', $params['selected']);
    $smarty->assign('tabpane_content', $content);
    $smarty->display('components/tabpane.tpl');
  }
}

?>