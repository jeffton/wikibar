<?php

function smarty_block_edititem($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    $smarty->assign('edititem_style', $params['style']);
    $smarty->assign('edititem_helptopic', $params['helptopic']);
    $smarty->assign('edititem_title', $params['title']);
    $smarty->assign('edititem_content', $content);
    $smarty->display('components/edititem.tpl');
  }

}

?>