<?php

function smarty_function_urlchooser($params, &$smarty) {
  $row = $params['row'];
  $smarty->assign('urlchooser_website', $row['url_website']);
  $smarty->assign('urlchooser_myspace', $row['url_myspace']);
  $smarty->assign('urlchooser_facebook', $row['url_facebook']);

  $smarty->display('components/urlchooser.tpl');
}

?>
