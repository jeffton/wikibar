<?php

function smarty_block_item($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    $content = trim($content);
    if ($content) {
      $smarty->assign('item_style', $params['style']);
      $smarty->assign('item_helptopic', $params['helptopic']);
      $smarty->assign('item_title', $params['title']);
      $smarty->assign('item_content', $content);
      $smarty->display('components/item.tpl');
    }
  }

}

?>