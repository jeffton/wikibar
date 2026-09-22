<?php

class Datetools {
  private $MONTHS = Array("januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  private $WEEKDAYS = Array("søndag", "mandag", "tirsdag", "onsdag", "torsdag", "fredag", "lørdag");

  public function today($asString=false, $offset=0) {
    $time = time() + $offset;
    $string = date('Y-m-d', $time);
    return ($asString ? $string : strtotime($string));
  }
  public function wikibarDay() {
    $today = $this->today(true, -6* 60*60); // døgnet skifter kl. 6
    $today = "CAST('$today' AS DATE)";
    return $today;
  }

  public function getDateString($date, $withweekday=true, $withcountdown=true) {
    if (is_null($date)) {
      return null;
    }
    $weekday = null;
    $countdown = null;
    
    $date = strtotime($date);
    $dd = date("j", $date);
    $mm = date("n", $date);
    $yyyy = date("Y", $date);
    $day = date("w", $date);
    $str = $dd . ". " . $this->MONTHS[$mm-1] . " " . $yyyy;
    if ($withweekday) {
      $weekday = $this->WEEKDAYS[$day];
    }

    if ($withcountdown) {
      $today = $this->today();
      $diff = round(($date - $today) /60/60/24);
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

    $tail = ($weekday && $countdown) ? "$weekday, $countdown" : "$weekday$countdown";
    $str = $tail ? "$str ($tail)" : $str;

    return $str;
  }


}


?>
