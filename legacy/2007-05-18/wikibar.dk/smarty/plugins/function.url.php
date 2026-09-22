<?php
function smarty_function_url($params, &$smarty) {
  $type = $params['type'];
  $row = $params['row'];
  $action = $params['action'];
  return Urls::getUrl($type, $row['id'], $action);
}
?>