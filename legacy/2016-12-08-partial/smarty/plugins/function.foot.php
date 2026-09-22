<?php
function smarty_function_foot($params, &$smarty) {
  $smarty->assign('foot_ads', $params['ads'] && ADS_ENABLED);
  $smarty->display('components/foot.tpl');
}


?>