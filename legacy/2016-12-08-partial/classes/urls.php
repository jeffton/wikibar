<?php

class Urls {

  private $valid;

  function __construct() {
    $this->valid = Factory::getValid();
  }

  function getUrl($type, $id=0, $action=null, $absolute=false, $checkaccess=true) {
  
    if (!$type) {
      return null;
    }
    
    if ($type == 'calendar') {
      settype($id, 'int');
      if ($id == 0) {
        $id = 1;
      }
      $url = ($id != 1) ? "$id" : "";
    } else {
      
      // auth ved url'er til brugerredigering
      if ($type == "user" && $action) {
        if ($checkaccess && (!$id || !Auth::authed($id))) {
          return null;
        }
        if ($action == 'history') {
          return null;
        }
      }
  
      if ($id || $action) {
        $url = $type;
        if ($id) {
          $url .= "/$id";
        }
        if ($action) {
          $url .= "/$action";
        } else if ($type == 'event') { // and id
          $url .= '#e';
        }
      } else {
        if ($type == 'event') {
          $url = "";
        } else {
          $url = $type . 's';
        }
      }
    }

    $url = ($absolute ? HOST . FOLDER : FOLDER) . $url;

    return ($absolute ? $url : htmlspecialchars($url));
  }
  
  
  function getCurrent($absolute=false) {
    $url = $_SERVER['REQUEST_URI'];
    if ($absolute) {
      $url = HOST . $url;
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
