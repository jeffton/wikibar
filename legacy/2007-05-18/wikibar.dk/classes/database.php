<?php

class Database {

  private $dbh;
  private $tables = Array('venue', 'band', 'user');
  private $perpage;


  function validTypeOrDie($type, $cause=null) {
    if (!in_array($type, $this->tables)) {
      die("Ukendt type ($cause)");
    }
  }

  function __construct($host, $user, $pw, $db, $perpage=15) {
    try {
      $this->dbh = new PDO("mysql:host=$host;dbname=$db", $user, $pw);
    } catch (PDOException $e) {
      die("Databasefejl");
    }
    $this->dbh->query("SET NAMES 'utf8'");
    $this->perpage = $perpage;
  }
  
  
/***************************************
    VÆRKTØJSFUNKTIONER
 ***************************************/

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
        $rs[$key] = $this->escape($val, $skiphtml);
      }
      return $rs;
    }
  }
  
  // trim og hav max 2 linieskift i træk
  private function trim_etc(&$text) {
    $text = trim($text);
    $text = preg_replace("@(\r?\n\s*){3,}@", "\r\n\r\n", $text);
  }
  
  // ret tomme strenge til null-værdier før save
  function nullifyArray(&$array) {
    foreach ($array as $key => $value) {
      if (is_array($array[$key])) {
        continue;
      }
      if (gettype($array[$key] == "string")) {
        $array[$key] = trim($array[$key]);
      }
      if ($value === '') {
        $array[$key] = null;
      }
    }
  }


/***************************************
    LÆSNING FRA DATABASEN
 ***************************************/

  // hent række til visning (user/venue/band)
  function getRow($type, $id, $full=true) {
    $this->validTypeOrDie($type, "Database::getRow");
    settype($id, 'int');
    if ($id == 0) {
      return null;
    }
    $columns = ($full ? '*' : 'id, name');
    $result = $this->dbh->query("SELECT $columns FROM $type WHERE ID = $id", PDO::FETCH_ASSOC);
    $row = $result->fetch();
    if ($full) {
      if ($type == 'user') {
        unset($row['password']);
        unset($row['passwordsalt']);
      }
    }
    $result->closeCursor();
    return $this->escape($row);
  }

  // hent en sides ting
  function getListingByPage($type, $pagenum=1) {
    $this->validTypeOrDie($type, "Database::getListingByPage()");
    $table = $type;
    $sql = "SELECT id, name, url_website, url_myspace FROM $table ORDER BY name LIMIT ?,?";
    $smt = $this->dbh->prepare($sql);
    $startrow = ($pagenum-1) * $this->perpage;
    $smt->bindParam(1, $startrow, PDO::PARAM_INT);
    $smt->bindParam(2, $this->perpage, PDO::PARAM_INT);
    return $this->makeListing($smt);
  }
  
  // hent ting ved søgning
  function getListing($type, $search=null, $limit=0) {
    $this->validTypeOrDie($type, "Database::getListing");
    $table = $type;
    $limit = $limit ? $limit : $this->perpage;

    if ($search) {
      $sql = "SELECT id, name, url_website, url_myspace FROM $table WHERE name LIKE ? ORDER BY name=? DESC, name LIKE ? DESC, name ASC LIMIT ?";
      $smt = $this->dbh->prepare($sql);
      $searchwild = "$search%";
      $searchwilder = "%$search%";
      $smt->bindParam(1, $searchwilder, PDO::PARAM_STR);
      $smt->bindParam(2, $search, PDO::PARAM_STR);
      $smt->bindParam(3, $searchwild, PDO::PARAM_STR);
      $smt->bindParam(4, $limit, PDO::PARAM_INT);
    } else {
      $sql = "SELECT id, name, url_website, url_myspace FROM $table ORDER BY name LIMIT ?";
      $smt = $this->dbh->prepare($sql);
      $smt->bindParam(1, $limit, PDO::PARAM_INT);
    }

    return $this->makeListing($smt);
  }

  private function makeListing($smt) {
    $results = $smt->execute();
    $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
    $smt->closeCursor();
    return $this->escape($rows);
  }

  // hent sideinddelingsbogstaver
  function getPageLetters($type) {
    $this->validTypeOrDie($type);
    $table = $type;
    $smt = $this->dbh->query("SELECT COUNT(*) FROM $table");
    $countarr = $smt->fetch(PDO::FETCH_NUM);
    $count = $countarr[0];
    $smt->closeCursor();
    $numpages = ceil($count / $this->perpage);

    for ($page = 1; $page <= $numpages; $page++) {
      $firstrow = ($page-1)*$this->perpage + 1;
      $lastrow = min($count, $page * $this->perpage);

      $first[] = utf8_ucfirst(utf8_strtolower($this->getName($type, $firstrow)));
      $last[] = utf8_ucfirst(utf8_strtolower($this->getName($type, $lastrow)));
    }

    $lastNames = $last;

    for ($i = -1; $i<$numpages; $i++) {
      $this->expandUntilDifferent($last[$i], $first[$i+1]);
    }

    for ($i = 0; $i < $numpages; $i++) {
      $different = (strcmp($first[$i], substr($lastNames[$i], 0, strlen($first[$i]))) != 0);
      $letters[$i][0] = $first[$i];
      if ($different) {
        $letters[$i][1] = $last[$i];
      }
    }
    return $this->escape($letters);
  }

  private function expandUntilDifferent(&$a, &$b) {
    for ($i = 1; $i <= max(utf8_strlen($a), utf8_strlen($b)); $i++) {
      $cuta = trim(utf8_substr($a, 0, $i));
      $cutb = trim(utf8_substr($b, 0, $i));
      if ($cuta != $cutb) {
        break;
      }
    }
    $a = $cuta;
    $b = $cutb;
  }

  private function getName($type, $number) {
    $number--;
    $sql = "SELECT name FROM $type ORDER BY name LIMIT $number, 1";
    $smt = $this->dbh->query($sql);
    $name = $smt->fetch(PDO::FETCH_NUM);
    $smt->closeCursor();
    return $name[0];
  }


/***************************************
    LAGRING I DATABASEN
 ***************************************/

  function saveRow($type, $row, &$error) {
    setType($row['id'], 'int');

    if ($type == "event") {
      $this->saveEvent($row, &$error);
      return;
    }

    $this->validTypeOrDie($type, "Database::saveRow");
    if (!trim($row['name'])) {
      $error = "Der skal angives et navn.";
      return null;
    }

    if ($this->nameTaken($type, $row['name'], $row['id'])) {
      $error = "Det valgte navn er allerede brugt.";
      return null;
    }
    $this->trim_etc($row['text']);

    $this->nullifyArray(&$row);

    if ($type == "venue") {
      return $this->saveVenue($row, $error);
    } else if ($type == "band") {
      return $this->saveBand($row, $error);
    } else if ($type == "user") {
      return $this->saveUser($row, $error);
    }
  }
  
  private function saveUser($user, &$error) {
    if ($user['password'] != $user['passwordrepeat']) {
      $error = "De indtastede passwords er ikke ens.";
      return;
    }
    if ($user['password']) {
      $this->makePassword($user['password'], $user['passwordsalt']);
    }
    
    if (!$user['id']) {
      if (!$user['password'] && !$user['passwordrepeat']) {
        $error = "Du skal indtaste et password.";
        return;
      }
      
      $user['id'] = $this->insert('user', 
          array('id', 'name', 'password', 'passwordsalt'), $user);
    } else {    
      $columns = array('name', 'url_website', 'url_myspace', 'text');
      if ($user['password']) {
        $columns[] = 'password';
        $columns[] = 'passwordsalt';
      }
      $this->update('user', "id=" . $user['id'], $columns, $user);
    }
    
    return $user['id'];
  }
  
  private function makePassword(&$password, &$salt) {
    $salt = hash('whirlpool', uniqid(rand(), true));
    $password = $this->hashPassword($password, $salt);
  }
  
  private function hashPassword($password, $salt) {
    return hash('whirlpool', $salt . $password);
  }

  private function saveVenue($venue, &$error) {
    if (!$venue['id']) { // new
      $venue['id'] = $this->insert('venue', 
              array('name', 'address_street', 'address_postalcode', 
              'address_city', 'address_country', 'price_entry', 'price_bottle', 
              'price_draught', 'price_shot', 'price_drink', 'wardrobe', 
              'wardrobe_price', 'music_starts_at', 'url_website', 'url_myspace', 
              'text'), $venue);
    } else { // edit
      $this->update('venue', "id=" . $venue['id'],
            array('name', 'address_street', 'address_postalcode',
                  'address_city', 'address_country', 'price_entry', 'price_bottle',
                  'price_draught', 'price_shot', 'price_drink', 'wardrobe', 
                  'wardrobe_price', 'music_starts_at', 'url_website', 'url_myspace',
                  'text'),
            $venue);
    }
    return $venue['id'];
  }

  private function saveBand($band, &$error) {
    if (!$band['id']) {
      $band['id'] = $this->insert('band',
           array('name', 'url_website', 'url_myspace', 'text'),
           $band);
    } else {
      $this->update('band', "id=" . $band['id'],
           array('name', 'url_website', 'url_myspace', 'text'),
           $band);
    }
    return $band['id'];
  }

  public function saveEvent($event, &$error) {
    $this->nullifyArray(&$event);
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

    $this->begin();

    // GEM VENUE
    $venue = $event['venue'][1];
    $venue = explode(':', $venue, 2);
    if ($venue[0] == "id") {
      if ($this->rowExists('venue', $venue[1])) {
        $venueId = $venue[1];
      } else {
        $error = "Det valgte spillested er blevet slettet";
        $this->rollback();
        return;
      }
    } else {
      if ($venue[1]) {
        $venueRow['name'] = $venue[1];
        if (!($venueId = $this->saveRow('venue', $venueRow, $error))) {
          $error = "Spillestedet kunne ikke oprettes:<br />$error";
          $this->rollback();
          return;
        }
      } else {
        $venueId = null;
      }
    }

    // GEM BANDS
    $bands = array();
    if ($event['bands']) {
      foreach ($event['bands'] as $band) {
        $band = explode(':', $band, 2);
        if ($band[0] == "id") {
          if ($this->rowExists('band', $band[1])) { // her burde man nok have nogle foreign-keys; det her er ikke amok-proof
            $bands[] = $band[1];
          } else {
            $error = "Et af de valgte bands er blevet slettet.";
            $this->rollback();
            return;
          }
        } else {
          $bandRow['name'] = $band[1];
          if ($bandId = $this->saveRow('band', $bandRow, $error)) {
            $bands[] = $bandId;
          } else {
            $error = "Bandet ". $this->escape($bandRow['name']) ." kunne ikke oprettes:<br />$error";
            $this->rollback();
            return;
          }
        }
      }
    }

    if (!($event['name'] || count($bands) || $venueId)) {
      $error = "Der skal angives en titel, et eller flere bands og/eller et spillested";
      $this->rollback();
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
    
    $columns = array('name', 'eventtype', 'venueID', 'date', 'time', 
          'enddate', 'price', 'text', 'url_website', 'url_myspace');
    
    if ($event['id']) {
      $smt = $this->dbh->prepare("DELETE FROM appearance WHERE eventID = ?");
      $smt->bindParam(1, $event['id'], PDO::PARAM_INT);
      $smt->execute();
      
      $smt = $this->dbh->prepare("DELETE FROM guest WHERE eventID = ? AND userID = ?");
      $userId = USERID;
      $smt->bindParam(1, $event['id'], PDO::PARAM_INT);
      $smt->bindParam(2, $userId, PDO::PARAM_INT);
      $smt->execute();  
      
      $this->update('event', "id=" . $event['id'], $columns, $event);
    } else { // ny event
      $event['id'] = $this->insert('event', $columns, $event);
    }
    
    // INDSÆT BANDS
    for ($i = 0; $i < count($bands); $i++) {
      $this->insert('appearance', array('eventID', 'bandID', 'sequence'), 
      array('eventID' => $event['id'], 'bandID' => $bands[$i], 'sequence' => $i));
    }

    // INDSÆT GÆST
    if ($event['upforit']) {
      $this->insert('guest', array('eventID', 'userID'), 
          array('eventID' => $event['id'], 'userID' => USERID));
    }
    $this->commit();
  }

  public function getEvent($id) {
    settype($id, 'int');
    if (!$id) {
      return null;
    } else {
      $events = $this->_getCalendar($id);
      return count($events) ? $events[0] : null;
    }
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
    $this->dbh->query("DELETE FROM guest WHERE eventID = $event AND userID = $user");
    if ($add) {
      $this->dbh->query("INSERT INTO guest (eventID, userID) VALUES ($event, $user)");
    }
  }

  public function getCalendar($venue=null, $band=null, $user=null) {
    return $this->_getCalendar(null, $venue, $band, $user);
  }

  private function _getCalendar($id=null, $venue=null, $band=null, $user=null) {
              // todo tidszoner (not important though)
    global $datetools;
    // fang events
    settype($id, 'int');
    if ($id) {
      $where = " WHERE event.id = $id ";
    } else {
      $today = $datetools->today(true);
      $today = "CAST('$today' AS DATE)";
      $where = "WHERE ((event.date >= $today) OR (event.enddate >= $today)) ";
      settype($venue, 'int'); settype($band, 'int'); settype($user, 'int');
      if ($venue) {
        $where .= "AND venueID = $venue ";
      }
      if ($band) {
        $where .= "AND (SELECT COUNT(*) FROM appearance WHERE eventID = event.id AND bandID = $band) ";
      }
      if ($user) {
        $where .= "AND (SELECT COUNT(*) FROM guest WHERE eventID = event.id AND userID = $user) ";
      }
    }

    $sql = "SELECT event.*, venue.name AS venueName, " . 
           "venue.url_website AS venueSite, venue.url_myspace AS venueMyspace " .
           "FROM event " .
           "LEFT JOIN venue ON event.venueID = venue.id " .
           $where .
           "ORDER BY event.date ASC, event.time ASC, event.enddate ASC";

    $eventResult = $this->dbh->query($sql);
    $events = $eventResult->fetchAll(PDO::FETCH_ASSOC);
    $eventResult->closeCursor();
    
    $bandSql = "SELECT band.id, band.name, band.url_website, band.url_myspace FROM appearance, band " .
               "WHERE appearance.eventID = :eventID " .
               "AND appearance.bandID = band.id ORDER BY appearance.sequence ASC";
    $bandSmt = $this->dbh->prepare($bandSql);
    $guestSql = "SELECT user.id, user.name FROM guest, user " .
                "WHERE guest.eventID = :eventID " .
                "AND guest.userID = user.id ORDER BY user.name ASC";
    $guestSmt = $this->dbh->prepare($guestSql);
    $binding = array();
    
    for ($i = 0; $i<count($events); $i++) {

      $events[$i]['venue']['id'] = $events[$i]['venueID'];
      $events[$i]['venue']['name'] = $events[$i]['venueName'];
      $events[$i]['venue']['url_website'] = $events[$i]['venueSite'];
      $events[$i]['venue']['url_myspace'] = $events[$i]['venueMyspace'];
      unset($events[$i]['venueID']);
      unset($events[$i]['venueName']);
      unset($events[$i]['venueSite']);
      unset($events[$i]['venueMyspace']);

      foreach (array('date', 'enddate') as $key) {
        $events[$i]["${key}value"] = strtotime($events[$i][$key]);
        $events[$i]["${key}text"] = $datetools->getDateString($events[$i][$key], false);
        $events[$i][$key] = $datetools->getDateString($events[$i][$key], $key =='date');
      }
      
      if ($events[$i]['time']) {
        $events[$i]['time'] = substr($events[$i]['time'], 0, 5);
      }

      $binding[':eventID'] = $events[$i]['id'];

      // fang bands pr. event
      $bandSmt->execute($binding);
      while ($bandRow = $bandSmt->fetch(PDO::FETCH_ASSOC)) {
        $events[$i]['bands'][] = $bandRow;
      }
      $bandSmt->closeCursor();
      
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
    return $this->escape($events);
  }

  // tjek om et navn er optaget
  public function nameTaken($type, $name, $ignoredid=null) {
    $this->validTypeOrDie($type, "Database::nameTaken");
    $table = $type;
    if ($ignoredid) {
      $smt = $this->dbh->prepare("SELECT COUNT(*) FROM $table WHERE name = ? AND id <> ? ");
      $smt->bindParam(1, $name, PDO::PARAM_STR);
      $smt->bindParam(2, $ignoredid, PDO::PARAM_INT);
    } else {
      $smt = $this->dbh->prepare("SELECT COUNT(*) FROM $table WHERE name = ?");
      $smt->bindParam(1, $name, PDO::PARAM_STR);
    }
    $smt->execute();
    $count = $smt->fetch(PDO::FETCH_NUM);
    $smt->closeCursor();
    return ($count[0] != 0);
  }

  private function rowExists($table, $id) {
    $smt = $this->dbh->prepare("SELECT COUNT(*) FROM $table WHERE id = ?");
    $smt->bindParam(1, $id, PDO::PARAM_INT);
    $smt->execute();
    $count = $smt->fetch(PDO::FETCH_NUM);
    $smt->closeCursor();
    return ($count[0] != 0);
  }

/***************************************
    LOGIN
 ***************************************/

  public function checkLogin($user, $password) {
    $sql = "SELECT id, password, passwordsalt FROM user " .
           "WHERE name=?";
    $smt = $this->dbh->prepare($sql);
    $smt->bindParam(1, $user, PDO::PARAM_STR);
    $smt->execute();
    $user = $smt->fetch(PDO::FETCH_ASSOC);
    $smt->closeCursor();
    if ($user) {
      if ($this->hashPassword($password, $user['passwordsalt']) == 
          $user['password']) {
        return $user['id'];
      }
    }
    return 0;
  }
  
/***************************************
    GENERELLE DATABASEFUNKTIONER
 ***************************************/
  
  private function begin() {
    $this->dbh->beginTransaction();
  }

  private function rollback() {
    $this->dbh->rollBack();
  }

  private function commit() {
    $this->dbh->commit();
  }
  
  private function insert($table, $columns, $data, $specialcolumns = array()) {
    $querynames = "INSERT INTO $table (";
    $queryvalues = ") VALUES (";
    foreach ($columns as $column) {
      $querynames .= "$column, ";
      if ($specialcolumns[$column]) {
        $queryvalues .= $specialcolumns[$column] . ", ";
      } else {
        $queryvalues .= ":$column, ";
      }
      $filtereddata[":$column"] = $data[$column];
    }
    $querynames = substr($querynames, 0, -2); // fjern sidste ', '
    $queryvalues = substr($queryvalues, 0, -2); // fjern sidste ', '
    $query = "$querynames $queryvalues )";
    $smt = $this->dbh->prepare($query);
    $smt->execute($filtereddata);
    return $this->dbh->lastInsertId();
  }
  
  private function update($table, $where, $columns, $data, $specialcolumns = array()) {
    $query = "UPDATE $table SET ";
    foreach ($columns as $column) {
      if ($specialcolumns[$column]) {
        $query .= "$column = " . $specialcolumns[$column] . ", ";
      } else {
        $query .= "$column = :$column, ";
      }
      $filtereddata[":$column"] = $data[$column];
    }
    $query = substr($query, 0, -2); // fjern sidste ', '
    if ($where) {
      $query .= " WHERE $where";
    }
    $smt = $this->dbh->prepare($query);
    $smt->execute($filtereddata);
  }
  
  

}

?>