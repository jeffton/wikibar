<?php

function smarty_function_head($params, &$smarty) {
  $datetools = Factory::getDateTools();
  
  $smarty->assign('today', $datetools->getDateString($datetools->today(true), true, false));
  
  $scripts = $params['scripts'];
  if ($scripts) {
    $scripts = split(',', $scripts);
    array_unshift($scripts, 'common');
    for ($i = 0; $i < count($scripts); $i++) {
      $scripts[$i] = trim($scripts[$i]);
      if ($scripts[$i] == "help") {
        $smarty->assign('help', true);
      }
      if (substr($scripts[$i], 0, 7) != 'http://') {
        $scripts[$i] = 'scripts/' . $scripts[$i];
      }
    }
    $smarty->assign('scripts', $scripts);
  }

  $rowApi = Factory::getRowApi();
  $user = $rowApi->getRow('user', USERID, false);
  $smarty->assign('user', $user);

  $smarty->assign('onload', $params['onload']);
  $smarty->assign('onunload', $params['onunload']);
  $smarty->assign('title', $params['title']);
  $smarty->assign('at', $params['at']);
  $smarty->display('components/head.tpl');
}

?>