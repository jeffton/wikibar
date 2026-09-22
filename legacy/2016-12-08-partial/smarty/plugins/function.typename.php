<?php

function smarty_function_typename($param, &$smarty) {
  $formatting = Factory::getFormatting();

  $type = $param['type'];
  $cap = $param['cap'];
  $plur = $param['plur'];
  return $formatting->typeName($type, $cap, $plur);
}

?>