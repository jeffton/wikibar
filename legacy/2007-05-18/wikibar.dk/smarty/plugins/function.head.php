<?php

function smarty_function_head($params, &$smarty) {
  $scripts = $params['scripts'];
  if ($scripts) {
    $scripts = split(',', $scripts);
    for ($i = 0; $i < count($scripts); $i++) {
      $scripts[$i] = trim($scripts[$i]);
      if ($scripts[$i] == "help") {
        $smarty->assign('help', true);
      }
    }
    array_unshift($scripts, 'common');
    $smarty->assign('scripts', $scripts);
  }
  $smarty->assign('onload', $params['onload']);
  $smarty->assign('title', $params['title']);
  $smarty->assign('at', $params['at']);
  $smarty->display('components/head.tpl');
}

?>