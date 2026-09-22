<?php

function smarty_block_column($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    $content = trim($content);
    if ($content) {
      if ($params['side'] == 'right') {
        $class = " rightcolumn";
      }
      echo "<div class=\"column$class\">$content</div>";
    }
  }

}

?>