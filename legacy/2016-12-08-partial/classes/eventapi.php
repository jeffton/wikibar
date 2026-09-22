<?php

class EventApi {
  private $dateTools;
  private $stringTools;
  private $database;
  private $calendarpage;
  private $rowApi;
  private $valid;

  function __construct($calendarpage = 12) {
    $this->database = Factory::getDatabase();
    $this->calendarpage = $calendarpage;
    $this->dateTools = Factory::getDateTools();
    $this->stringTools = Factory::getStringTools();
    $this->rowApi = Factory::getRowApi();
    $this->valid = Factory::getValid();
  }

  public function saveEvent($event, &$error) {
    $this->stringTools->nullifyAndTrim($event);
    $this->stringTools->stripHttp($event);
    $this->stringTools->reduceLines($event['text']);

    settype($event['id'], 'int');
    if (!$event['date']) {
      $error = "Dato skal angives.";
      return;
    }
    if ($event['enddate']) {
      $date = strtotime($event['date']);
      $enddate = strtotime($event['enddate']);
      if ($enddate < $date) {
        $error = "Slutdatoen skal v&aelig;re efter startdatoen";
        return;
      }
      if ($enddate == $date) {
        unset($event['enddate']);
      }
    }
    
    
    if (!in_array($event['eventtype'], array('concert', 'party', 'releaseparty',
      'release', 'festival'))) {
      $error = "Type skal angives.";
      return;
    }

    $this->database->begin();

    // GEM VENUE
    if (isset($event['venue'])) {
      $venue = $event['venue'][1];
      $venue = explode(':', $venue, 2);
      if ($venue[0] == "id") {
        if ($this->rowExists('venue', $venue[1])) {
          $venueId = $venue[1];
        } else {
          $error = "Det valgte spillested er blevet slettet";
          $this->database->rollback();
          return;
        }
      } else {
        if ($venue[1]) {
          $venueRow['name'] = $venue[1];
          if (!($venueId = $this->rowApi->saveRow('venue', $venueRow, $error))) {
            $error = "Spillestedet kunne ikke oprettes:<br />$error";
            $this->database->rollback();
            return;
          }
        } else {
          $venueId = null;
        }
      }
    } else {
      $venueId = null;
    }

    // GEM BANDS
    $bands = array();
    if (isset($event['bands'])) {
      foreach ($event['bands'] as $band) {
        $band = explode(':', $band, 2);
        if ($band[0] == "id") {
          if ($this->rowExists('band', $band[1])) {
            $bands[] = $band[1];
          } else {
            $error = "Et af de valgte bands er blevet slettet.";
            $this->database->rollback();
            return;
          }
        } else {
          $bandRow['name'] = $band[1];
          if ($bandId = $this->rowApi->saveRow('band', $bandRow, $error)) {
            $bands[] = $bandId;
          } else {
            $error = "Bandet ". $this->stringTools->escape($bandRow['name']) ." kunne ikke oprettes:<br />$error";
            $this->database->rollback();
            return;
          }
        }
      }
    }

    if (!($event['name'] || count($bands) || $venueId)) {
      $error = "Der skal angives en titel, et eller flere bands og/eller et spillested";
      $this->database->rollback();
      return;
    }

    // GEM EVENT
    if ($event['time']) {
      $event['time'] = str_replace(array('.', ',', ';', '-', '_', ' '), ':', $event['time']);
      if (strlen($event['time']) < 4) {
        $event['time'] .= ':00';
      }
    }

    $event['venueID'] = $venueId;
    
    $columns = array('id', 'name', 'eventtype', 'venueID', 'date', 'time', 'enddate', 
      'price', 'text', 'url_website', 'url_myspace', 'url_facebook', 'status');
    
    if ($event['id']) {
      $smt = $this->database->prepare("DELETE FROM guest WHERE eventID = ? AND userID = ?");
      $userId = USERID;
      $smt->bindParam(1, $event['id'], PDO::PARAM_INT);
      $smt->bindParam(2, $userId, PDO::PARAM_INT);
      $smt->execute();  
      
      $this->rowApi->createVersion('event', $columns, $event);
    } else { // ny event
      array_shift($columns); // ud med id
      $this->rowApi->createRow('event', $columns, $event);
    }
    
    // INDSÆT BANDS
    for ($i = 0; $i < count($bands); $i++) {
      $this->database->insert('appearance_version', array('version', 'eventID', 'bandID', 'sequence'), 
      array('version' => $event['version'], 'eventID' => $event['id'], 'bandID' => $bands[$i], 'sequence' => $i));
    }

    // INDSÆT GÆST
    if ($event['upforit']) {
      $this->database->insert('guest', array('eventID', 'userID'), 
          array('eventID' => $event['id'], 'userID' => USERID));
    }
    $this->database->commit();
    return $event['id'];
  }
  
  public function addGuest($event) {
    $this->toggleGuest($event, true);
  }
  
  public function removeGuest($event) {
    $this->toggleGuest($event, false);
  }
  
  private function toggleGuest(&$event, $add=true) {
    settype($event, 'int');
    if (!$event) {
      return;
    }
    $user = USERID;
    $this->database->query("DELETE FROM guest WHERE eventID = $event AND userID = $user");
    if ($add) {
      $this->database->query("INSERT INTO guest (eventID, userID) VALUES ($event, $user)");
    }
  }
  
  public function getEvent($id, $forvcal=false) {
    settype($id, 'int');
    if (!$id) {
      return null;
    } else {
      $events = $this->_getCalendar($id, null, null, null, $null, null, $null, $null, $forvcal);
      return (count($events) ? $events[0] : null);
    }
  }
  
  public function getEvents($ids) {
    return $this->_getCalendar($ids, null, null, null, $null, null, $null, $null, $null, false);
  }

  public function getCalendar($venue=null, $band=null, $user=null, &$page=1,
      $highlight=null, &$archivePages, &$pages, $perpage) {
    return $this->_getCalendar(null, $venue, $band, $user, $page,
        $highlight, $archivePages, $pages, false, $perpag);
  }
  
  private function _getCalendar($ids=null, $venue=null, $band=null, $user=null,
      &$page=1, $highlight=null, &$archivePages=null, &$pages=null, $forvcal=false, $perpage=0) {
    settype($perpage, 'int');
    if (!$perpage) {
      $perpage = $this->calendarpage;
    }
    $small = ($venue || $band || $user);
    $order = null;
    $limit = null;
    // fang events
    if (!is_null($ids)) {
      if (!is_array($ids)) {
        settype($ids, 'int');
        $where = " WHERE event.id = $ids ";
      } else {
        $where = " WHERE event.id IN (";
        $order = " ORDER BY FIELD(event.id, ";
        for ($i = 0; $i < count($ids); $i++) {
          settype($ids[$i], 'int');
          $where .= $ids[$i];
          $order .= $ids[$i];
          if ($i < count($ids)-1) {
            $where .= ", ";
            $order .= ", ";
          } else {
            $where .= ") ";
            $order .= ") ";
          }
        }
      }
    } else { // asking for a page, not specific ids:
      settype($page, 'int');
      if ($page == 0) {
        $page = 1;
      }
      $today = $this->dateTools->wikibarDay(); // døgnet skifter kl. 6

      $where = "";
      $forwardWhere = "WHERE ((event.date >= $today) OR (event.enddate >= $today)) ";
      $archiveWhere = "WHERE ((event.date < $today) AND (event.enddate IS NULL OR event.enddate < $today)) ";

      settype($venue, 'int'); settype($band, 'int'); settype($user, 'int');
      if ($venue) {
        $where .= "AND venueID = $venue ";
      }
      if ($band) {
        $where .= "AND (SELECT 1 FROM appearance WHERE eventID = event.id AND bandID = $band LIMIT 1) ";
      }
      if ($user) {
        $where .= "AND (SELECT 1 FROM guest WHERE eventID = event.id AND userID = $user LIMIT 1) ";
      }
      $eventCount = $this->database->executeScalar("SELECT COUNT(*) FROM event $forwardWhere $where");
      $archiveEventCount = $this->database->executeScalar("SELECT COUNT(*) FROM event $archiveWhere $where");
      $pages = ceil($eventCount / $perpage);
      $archivePages = ceil($archiveEventCount / $perpage);
      
      settype($highlight, 'int');
      if ($highlight) {
        $isFuture = $this->database->executeScalar(
          "SELECT ((event.date >= $today) OR (event.enddate >= $today)) FROM event WHERE id = $highlight");
      } else {
        $isFuture = ($page > 0);
      }

      if ($isFuture) {
        $where = "$forwardWhere $where";
      } else {
        $where = "$archiveWhere $where";
      }

      if ($highlight) {
        $page = $this->getPageForEvent($highlight, $where, $isFuture, $perpage);
      }
      
      if ($page > 0) {
        $startrow = ($page-1) * $perpage;
      } else {
        $startrow = (-$page-1) * $perpage;
      }      
      
      $limit = " LIMIT $startrow, $perpage";
    }
    
    if (!$venue) {
      $venueColumns = ", venue.name AS venueName, " . 
           "venue.url_website AS venueSite, venue.url_myspace AS venueMyspace, venue.url_facebook as venueFacebook ";
      $venueJoin = "LEFT JOIN venue ON event.venueID = venue.id ";
    }
    
    if (!$order) {
      if ($page > 0) {
        $order = "ORDER BY event.date ASC, event.time ASC, event.enddate ASC, id ASC $limit";
      } else {
        $order = "ORDER BY event.date DESC, event.time DESC, event.enddate DESC, id DESC $limit";
      }
    }

    $sql = "SELECT event.* $venueColumns " .
           "FROM event " .
           $venueJoin .
           $where .
           $order;
    $eventResult = $this->database->query($sql);
    $events = $eventResult->fetchAll(PDO::FETCH_ASSOC);
    $eventResult->closeCursor();
    
    $bandSql = "SELECT band.id, band.name, band.url_website, band.url_myspace, band.url_facebook, band.country " .
               "FROM appearance, band " .
               "WHERE appearance.eventID = :eventID " .
               "AND appearance.bandID = band.id ORDER BY appearance.sequence ASC";
    $bandSmt = $this->database->prepare($bandSql);
    if (!$small) {
      $guestSql = "SELECT user.id, user.name " .
                  "FROM guest, user " .
                  "WHERE guest.eventID = :eventID " .
                  "AND guest.userID = user.id ORDER BY user.name ASC";
      $guestSmt = $this->database->prepare($guestSql);
    }
    $binding = array();
    
    for ($i = 0; $i<count($events); $i++) {
      if (!$venue) {
        $events[$i]['venue']['id'] = $events[$i]['venueID'];
        $events[$i]['venue']['name'] = $events[$i]['venueName'];
        $events[$i]['venue']['url_website'] = $events[$i]['venueSite'];
        $events[$i]['venue']['url_myspace'] = $events[$i]['venueMyspace'];
        $events[$i]['venue']['url_facebook'] = $events[$i]['venueFacebook'];
        unset($events[$i]['venueID']);
        unset($events[$i]['venueName']);
        unset($events[$i]['venueSite']);
        unset($events[$i]['venueMyspace']);
        unset($events[$i]['venueFacebook']);
      }

      if (!$forvcal) { // vcal skal bruge den rå datoformatering
        foreach (array('date', 'enddate') as $key) {
          $events[$i]["${key}value"] = strtotime($events[$i][$key]);
          $events[$i]["${key}text"] = $this->dateTools->getDateString($events[$i][$key], false, false);
          $events[$i][$key] = $this->dateTools->getDateString($events[$i][$key], $key =='date', $key == 'date');
        }
        if ($events[$i]['time']) {
          $events[$i]['time'] = substr($events[$i]['time'], 0, 5);
        }
      }

      $binding[':eventID'] = $events[$i]['id'];

      // fang bands pr. event
      $bandSmt->execute($binding);
      while ($bandRow = $bandSmt->fetch(PDO::FETCH_ASSOC)) {
        if ($bandRow['id'] == $band) {
          continue;
        }
        $events[$i]['bands'][] = $bandRow;
      }
      $bandSmt->closeCursor();
      
      if (!$small) {
        // fang gæster pr. event
        $guestSmt->execute($binding);
               
        $event[$i]['upforit'] = 0;
        while ($userRow = $guestSmt->fetch(PDO::FETCH_ASSOC)) {
          $events[$i]['guests'][] = $userRow;
          if ($userRow['id'] == USERID) {
            $events[$i]['upforit'] = 1;
            // intentionally no break
          }
        }
        $guestSmt->closeCursor();
      }
    }
    return ($forvcal ? $events : $this->stringTools->escape($events));
  }
  
  private function getPageForEvent($id, $where, $isFuture, $perpage) {
    $smt = $this->database->query(
      "SELECT date, time, enddate, id FROM event WHERE id=$id");
    $hi = $smt->fetch(PDO::FETCH_ASSOC);
    $smt->closeCursor();
    $date = $hi['date'] ? "CAST('{$hi['date']}' AS DATE)" : 'NULL';
    $time = $hi['time'] ? "CAST('{$hi['time']}' AS TIME)" : 'NULL';
    $enddate = $hi['enddate'] ? "CAST('{$hi['enddate']}' AS DATE)" : 'NULL';
    $id = $hi['id'];

    if ($isFuture) {
// find ud af hvor mange der kommer før $id i en 
// ORDER BY date ASC, time ASC, enddate ASC, id ASC
    $and = 
<<<SQL
AND ( 
  (date < $date)
  OR (
    (date = $date)
    AND (
      ((time < $time) OR (ISNULL(time) AND NOT ISNULL($time)))
      OR (
        ((time = $time) OR (ISNULL(time) AND ISNULL($time)))
        AND (
          ((enddate < $enddate) OR (ISNULL(enddate) AND NOT ISNULL($enddate)))
          OR (
            ((enddate = $enddate) OR (ISNULL(enddate) AND ISNULL($enddate)))
            AND (id < $id)
          )
        )
      )
    )
  )
)
SQL;
    } else {
// find ud af hvor mange der kommer før $id i en 
// ORDER BY date DESC, time DESC, enddate DESC, id DESC
    $and = 
<<<SQL
AND ( 
  (date > $date)
  OR (
    (date = $date)
    AND (
      ((time > $time) OR (NOT ISNULL(time) AND ISNULL($time)))
      OR (
        ((time = $time) OR (ISNULL(time) AND ISNULL($time)))
        AND (
          ((enddate < $enddate) OR (NOT ISNULL(enddate) AND ISNULL($enddate)))
          OR (
            ((enddate = $enddate) OR (ISNULL(enddate) AND ISNULL($enddate)))
            AND (id > $id)
          )
        )
      )
    )
  )
)
SQL;
    }
    $findsql = "SELECT COUNT(*) FROM event $where $and";
    $before = $this->database->executeScalar($findsql);
    $pagenum = floor($before/$perpage) + 1;
    if (!$isFuture) {
      $pagenum = -$pagenum;
    }

    return $pagenum;
  }

  private function rowExists($table, $id) {
    $smt = $this->database->prepare("SELECT COUNT(*) FROM $table WHERE id = ?");
    $smt->bindParam(1, $id, PDO::PARAM_INT);
    return ($this->database->executeScalarSmt($smt) != 0);
  }

}
