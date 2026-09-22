<?php

class Urls {

  function getUrl($type, $id=0, $action=null, $absolute=false) {
    if (!$type) {
      return null;
    }
    
    // auth ved url'er til brugerredigering
    if ($type == "user" && $action) {
      if (!$id || !Auth::authed($id)) {
        return null;
      }
    }
    
    // event-url'er klares her pt
    if ($type == "event") {
      return ($action ? "event.php?id=$id" : "index.php");
    }
    
    if ($id || $action) {
      $url = "display.php?type=$type&id=$id";
    } else {
      $url = "browse.php?type=$type";
    }
    if ($action) {
      $url = "$url&action=$action";
    }

    if ($absolute) {
      $url = HOST . $url;
    }
    return ($absolute ? $url : htmlspecialchars($url));
  }
  
  
  function getCurrent($absolute=false) {
    $url = $_SERVER['REQUEST_URI'];
    if ($absolute) {
      $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
    }
    return $url;
  }


  function redirect($url) {
    if (substr($url, 0, strlen(HOST)) != HOST) {
      $url = HOST . $url;
    }
    header("Location: $url");
  }

}

?>