<?php
function smarty_function_link($params, &$smarty) {
  $urls = Factory::getUrls();

  $type = $params['type'];
  $row = $params['row'];
  $class = $params['class'];
  $id = $params['id'];

  if (!$row) {
    $rowApi = Factory::getRowApi();
    $row = $rowApi->getRow($type, $id);
  }


  $action = null;
  $url = $urls->getUrl($type, $row['id'], $action);

  if ($url) {
    $text = $row['name'];
    if ($class) {
      $class = "class=\"$class\" ";
    }
    $link = "<a $class href=\"$url\">$text</a> ";
    
    if ($type == 'band' && $row['country']) {
      $link .= "(${row['country']}) ";
    }
    
    if ($row['url_website']) {
      $link .= "<a href=\"http://${row['url_website']}\">" .
        "<img src=\"layout/linkicon-website.png\" width=\"18\" height=\"18\" ". 
        "alt=\"Website\" title=\"Website\" /></a>";
    }
    if ($row['url_myspace']) {
      $link .= "<a href=\"http://myspace.com/${row['url_myspace']}\">" .
        "<img src=\"layout/linkicon-myspace.png\" width=\"17\" height=\"19\" ". 
        "alt=\"MySpace\" title=\"MySpace\" /></a>";
    }
    if ($row['url_facebook']) {
      $link .= "<a href=\"http://facebook.com/${row['url_facebook']}\">" .
        "<img src=\"layout/linkicon-facebook.png\" width=\"18\" height=\"18\" ". 
        "alt=\"Facebook\" title=\"Facebook\" /></a>";
    }

    
    return "<span class=\"item\">$link</span>";
  } else {
    return null;
  }
}
?>
