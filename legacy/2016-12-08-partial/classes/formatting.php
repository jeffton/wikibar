<?php

class Formatting {

  function format($text) {
    $before[] = "@\\[b\\](.*)\\[\\/b\\]@";
    $after[]  = "<strong>$1</strong>";

    $before[] = "@\\[i\\](.*)\\[\\/i\\]@";
    $after[]  = "<em>$1</em>";

    $before[] = "@\\[url=http\\:\\/\\/(.*)\\](.*)\\[\\/url\\]@";
    $after[] = "<a href=\"http://$1\">$2</a>";

    $before[] = "@\\[url\\](.*)\\[\\/url\\]@";
    $after[] = "<a href=\"http://$1\">$1</a>";

    $text = preg_replace($before, $after, $text);
    $text = nl2br($text);
    $this->makeHeadings($text);
    return $text;
  }

  function makeHeadings(&$text) {
    $text = preg_replace_callback("@(^|\n)(\={1,3}) ?(.*) ?\\2(?:$|\<br \/\>)@", 
  	array($this, 'makeHeadings_callback'), $text);
  }
  function makeHeadings_callback(&$matches) {
    $h = strlen($matches[2]) + 1;
    return $matches[1] . "<h$h>" . $matches[3] . "</h$h>";
  }

  function typeName($type, $cap=false, $plur=false) {
    switch($type) {
      case "venue":
        return (($cap ? "S" : "s") . "pillested" . ($plur ? "er" : ""));
      case "band":
        return (($cap ? "B" : "b") . "and" . ($plur ? "s" : ""));
      case "user":
        return (($cap ? "B" : "b") . "ruger" . ($plur ? "e" : ""));
      case "event":
        if ($plur) {
          return ($cap ? "K" : "k") . "alender";
        } else {
          return ($cap ? "E" : "e") . "vent";
        }
    }
  }

}

?>