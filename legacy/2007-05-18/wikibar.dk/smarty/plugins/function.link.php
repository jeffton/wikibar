<?php
function smarty_function_link($params, &$smarty) {
  $type = $params['type'];
  $row = $params['row'];
  $class = $params['class'];

  $action = null;
  $url = Urls::getUrl($type, $row['id'], $action);

  if ($url) {
    $text = $row['name'];
    if ($class) {
      $class = "class=\"$class\" ";
    }
    $link = "<a $class href=\"$url\">$text</a> ";
    
    if ($row['url_website']) {
      $link .= "<a href=\"http://${row['url_website']}\">" .
        "<img src=\"layout/linkicon-website.png\" width=\"18\" height=\"18\" ". 
        "alt=\"Website\" title=\"website\" /></a>";
    }
    if ($row['url_myspace']) {
      $link .= "<a href=\"http://myspace.com/${row['url_myspace']}\">" .
        "<img src=\"layout/linkicon-myspace.png\" width=\"17\" height=\"19\" ". 
        "alt=\"MySpace\" title=\"MySpace\" /></a>";
    }
    return "<span class=\"item\">$link</span>";
  } else {
    return null;
  }
}
?>