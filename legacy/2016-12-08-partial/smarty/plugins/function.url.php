<?php
function smarty_function_url($params, &$smarty) {
  $urls = Factory::getUrls();

  $type = $params['type'];
  $row = $params['row'];
  $action = $params['action'];
  $id = $params['id'];
  return $urls->getUrl($type, $id ? $id : $row['id'], $action);
}
?>