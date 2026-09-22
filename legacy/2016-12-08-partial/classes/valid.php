<?php

class Valid {

  private $tables = Array('venue', 'band', 'user');

  function typeOrDie($type, $cause=null) {
    if (!in_array($type, $this->tables)) {
      die("Ukendt type ($cause)");
    }
  }

  function posInt(&$i) {
    $copy = $i;
    settype($i, 'int');
    $i = max(1, $i);
    return ($copy == $i);
  }
  
  function mail($mail) {
    $at = strpos($mail, '@');
    if ($at === false)
      return false;
    if (strpos($mail, '.', $at) === false)
      return false;
      
    return true;
  }


}

?>