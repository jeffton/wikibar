<?php

class StringTools {

  // escape HTML til visning
  function escape($arr = null) {
    if (!is_array($arr)) {
      if (is_null($arr)) {
        return null;
      } else {
        return htmlspecialchars($arr);
      }
    } else {
      $rs = array();
      while(list($key,$val) = each($arr)) {
        $rs[$key] = $this->escape($val);
      }
      return $rs;
    }
  }
  
  // max 2 linieskift i træk
  function reduceLines(&$text) {
    $text = preg_replace("@(\r?\n\s*){3,}@", "\r\n\r\n", $text);
  }
  
  // ret tomme strenge til null-værdier før save
  function nullifyAndTrim(&$array) {
    foreach ($array as $key => $value) {
      if (is_array($array[$key])) {
        continue;
      }
      if (gettype($array[$key] == 'string')) {
        $array[$key] = trim($array[$key]);
      }
      if ($value === '') {
        $array[$key] = null;
      }
    }
  }
  
  function stripHttp(&$row) {
    if (isset($row['url_website']) && $row['url_website']) {
      $web = str_replace('http://', '', $row['url_website']);
      if (substr($web, -1) == '/')
        $web = substr($web, 0, -1);
      $row['url_website'] = $web;
    }
    if (isset($row['url_myspace']) && $row['url_myspace']) {
      $row['url_myspace'] = str_replace(
          array('www.myspace.com/', 
                'myspace.com/',
                'http://'),
          '',
          $row['url_myspace']);
    }
    if (isset($row['url_facebook']) && $row['url_facebook']) {
      // remove the prefix
      $face = str_replace(
        array('www.facebook.com/',
              'facebook.com/',
              'http://'), 
        '', $row['url_facebook']);

      // clean up after ajax magic
      $hashNslash = strrpos($face, '#/');
      if ($hashNslash !== FALSE) {
        $face = substr($face, $hashNslash + 2);
      }

      // lose superflous parameters
      $fileparms = split('\?', $face);
      if (count($fileparms) == 2) { 
        $face = $fileparms[0] . '?';
        $parms = split('\&', $fileparms[1]);
        foreach ($parms as $parm) {
          $parmval = split('\=', $parm);
          if (count($parmval) == 2) {
            if ($parmval[0] && $parmval[0] != 'sid' && $parmval[0] != 'ref') {
              $face .= "{$parmval[0]}={$parmval[1]}&";
            }
          }
        }
        // we were lazy and ended with a '&' or a '?'
        $face = substr($face, 0, -1);
      }
      $row['url_facebook'] = $face;
    }
  }
}

?>
