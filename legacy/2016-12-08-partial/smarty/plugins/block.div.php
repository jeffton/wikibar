<?php

function smarty_block_div($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    $content = trim($content);
    if ($content) {
      $class = $params['class'];
      if ($class) {
        $class = "class=\"$class\"";
      }
      echo "<div $class>$content</div>";
    }
  }

}

?>