<?php

function smarty_function_typename($param, &$smarty) {
  global $formatting;

  $type = $param['type'];
  $cap = $param['cap'];
  $plur = $param['plur'];
  return $formatting->typeName($type, $cap, $plur);
}

?>