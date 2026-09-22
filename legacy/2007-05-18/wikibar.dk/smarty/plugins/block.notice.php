<?php

function smarty_block_notice($params, $content, &$smarty, &$repeat) {
  if (!$repeat) {
    if (trim($content)) {
      $class = $params['class'];
      echo "<div class=\"notice ${class}text\"><div>$content</div></div>";
    }
  }
}

?>