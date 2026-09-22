<?php

class Datetools {
  private $MONTHS = Array("januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  private $WEEKDAYS = Array("søndag", "mandag", "tirsdag", "onsdag", "torsdag", "fredag", "lørdag");

  public function today($asString=false) {
    $string = date('Y-m-d');
    return ($asString ? $string : strtotime($string));
  }

  public function getDateString($date, $fordisplay=true, $withcountdown=true) {
    if (is_null($date)) {
      return null;
    }
    $date = strtotime($date);
    $dd = date("j", $date);
    $mm = date("n", $date);
    $yyyy = date("Y", $date);
    $day = date("w", $date);
    $str = $dd . ". " . $this->MONTHS[$mm-1] . " " . $yyyy;
    if ($fordisplay) {
      if ($withcountdown) {
        $today = $this->today();
        $diff = round(($date - $today) /60/60/24);
        if (abs($diff) > 60) {
          $withcountdown = false;
        } else {
          switch($diff) {
            case -1:
              $countdown = "i går";
              break;
            case 0:
              $countdown = "i dag";
              break;
            case 1:
              $countdown = "i morgen";
              break;
          }
          if (!$countdown) {
            if ($diff < 0) {
              $diff = -$diff;
              $countdown = "for $diff dage siden";
            } else {
              $countdown = "om $diff dage";
            }
          }
        }
      }
      $str .= " (" . $this->WEEKDAYS[$day];
      if ($withcountdown) {
        $str .= ", $countdown";
      }
      $str .= ")";
    }
    return $str;
  }


}


?>