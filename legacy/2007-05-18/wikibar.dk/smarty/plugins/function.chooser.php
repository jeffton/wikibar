<?php
function smarty_function_chooser($params, &$smarty) {
  $type = $params['type'];
  $mode = $params['mode'];
  $values = $params['values'];
  $value = $params['value'];
  $name = $params['name'];
  $id = "chooser_$name";

  if ($mode == "single") {
    unset($values);
    $values[0] = $value;
  }
  
  // bevar værdier ved valideringsfejl
  $posted = $_POST[$id . "field"];
  if ($posted) {
    unset($values);
    foreach ($posted as $post) {
      $values[]['name'] = $post;
    }
    $posted = $_POST[$name];
    if ($posted) {
      foreach ($posted as $post) {
        if (substr($post, 0, 5) == "name:") {
          $selected[] = "create";
        } else if (substr($post, 0, 3) == "id:") {
          $selected[] = substr($post, 3);
        } else {
          $selected[] = null;
        }
      }
    }
  } else if (!$values) {
    $values[0]['name'] = ""; // ingen data; lav én tom chooser
  }

  $smarty->assign('chooser_type', $type);
  $smarty->assign('chooser_mode', $mode);
  $smarty->assign('chooser_values', $values);
  $smarty->assign('chooser_selected', $selected);
  $smarty->assign('chooser_name', $name);
  $smarty->assign('chooser_id', $id);

  $smarty->display('components/chooser.tpl');
}
?>