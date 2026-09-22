<?php

class RowApi {
  private $perpage;
  private $database;
  private $valid;
  private $stringTools;
  private $auth;
  private $mailApi;

  public function __construct($perpage=30) {
    $this->perpage = $perpage;
    $this->valid = Factory::getValid();
    $this->database = Factory::getDatabase();
    $this->stringTools = Factory::getStringTools();
    $this->auth = Factory::getAuth();
    $this->mailApi = Factory::getMailApi();
  }

  // hent rÃ¦kke til visning (user/venue/band)
  function getRow($type, $id, $full=true) {
    $this->valid->typeOrDie($type, "RowApi::getRow");
    if (!$this->valid->posInt($id)) {
      return null;
    }
    $columns = ($full ? '*' : 'id, name');
    $result = $this->database->query("SELECT $columns FROM $type WHERE ID = $id");
    $row = $result->fetch();
    if ($full) {
      if ($type == 'user') {
        unset($row['password']);
        unset($row['passwordsalt']);
      }
    }
    $result->closeCursor();
    return ($row ? $this->stringTools->escape($row) : null);
  }

  // hent en sides ting
  function getListingByPage($type, $pagenum=1) {
    $this->valid->typeOrDie($type, "RowApi::getListingByPage()");
    $table = $type;
    $this->valid->posInt($pagenum);
    $country = ($type == 'band' ? ', country' : '');
    $sql = "SELECT id, name, url_website, url_myspace, url_facebook $country FROM $table ORDER BY name_sort LIMIT ?,?";
    $smt = $this->database->prepare($sql);
    $startrow = ($pagenum-1) * $this->perpage;
    $smt->bindParam(1, $startrow, PDO::PARAM_INT);
    $smt->bindParam(2, $this->perpage, PDO::PARAM_INT);
    return $this->makeListing($smt);
  }
  
  // hent ting ved sÃ¸gning
  function getListing($type, $search=null, $limit=0) {
    $this->valid->typeOrDie($type, "Database::getListing");
    $table = $type;
    $limit = $limit ? $limit : $this->perpage;
    $country = ($type == 'band' ? ', country' : '');
    if ($search) {
      $sql = "SELECT id, name, url_website, url_myspace, url_facebook  $country FROM $table " .
             "WHERE name LIKE ? ORDER BY name=? DESC, name LIKE ? DESC, name_sort ASC LIMIT ?";
      $smt = $this->database->prepare($sql);
      $searchwild = "$search%";
      $searchwilder = "%$search%";
      $smt->bindParam(1, $searchwilder, PDO::PARAM_STR);
      $smt->bindParam(2, $search, PDO::PARAM_STR);
      $smt->bindParam(3, $searchwild, PDO::PARAM_STR);
      $smt->bindParam(4, $limit, PDO::PARAM_INT);
    } else {
      $sql = "SELECT id, name, url_website, url_myspace, url_facebook $country FROM $table ORDER BY name_sort LIMIT ?";
      $smt = $this->database->prepare($sql);
      $smt->bindParam(1, $limit, PDO::PARAM_INT);
    }

    return $this->makeListing($smt);
  }

  private function makeListing($smt) {
    $smt->execute();
    $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
    $smt->closeCursor();
    return $this->stringTools->escape($rows);
  }

  // hent sideinddelingsbogstaver
  function getPageLetters($type) {
    $this->valid->typeOrDie($type);
    $table = $type;
    $count = $this->database->executeScalar("SELECT COUNT(*) FROM $table");
    if ($count == 0)
      return null;
      
    $numpages = ceil($count / $this->perpage);

    for ($page = 1; $page <= $numpages; $page++) {
      $firstrow = ($page-1)*$this->perpage + 1;
      $lastrow = min($count, $page * $this->perpage);

      $first[] = utf8_ucfirst(utf8_strtolower($this->getSortName($type, $firstrow)));
      $last[] = utf8_ucfirst(utf8_strtolower($this->getSortName($type, $lastrow)));
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
    return $this->stringTools->escape($letters);
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

  private function getSortName($type, $number) {
    $number--;
    return $this->database->executeScalar(
        "SELECT name_sort FROM $type ORDER BY name_sort LIMIT $number, 1");
  }


/***************************************
    LAGRING I DATABASEN
 ***************************************/

  function saveRow($type, $row, &$error) {
    setType($row['id'], 'int');

    if ($type == "event") {
      return Factory::getEventApi()->saveEvent($row, $error);
    }

    $this->valid->typeOrDie($type, "Database::saveRow");
    if (!trim($row['name'])) {
      $error = "Der skal angives et navn.";
      return null;
    }

    if ($this->nameTaken($type, $row['name'], $row['id'])) {
      $error = "Det valgte navn er allerede brugt.";
      return null;
    }
    $this->stringTools->reduceLines($row['text']);

    $this->stringTools->nullifyAndTrim($row);
    
    $this->stringTools->stripHttp($row);
    
    $row['name_sort'] = $row['name'];
    foreach (array('the', 'a') as $prefix) {
      $length = utf8_strlen($prefix) + 1;
      if (utf8_strtolower(utf8_substr($row['name'], 0, $length)) == "$prefix ") {
        $strippedname = utf8_substr($row['name'], $length);
        $row['name_sort'] = $strippedname . ', ' . utf8_ucfirst($prefix);
      }
    }

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
      $this->auth->makePassword($user['password'], $user['passwordsalt']);
      if ($user['id']) {
        $this->auth->clearToken($user['id']);
      }
    }
    $confirmMail = false;
    if (!$user['id']) {
      if (!$user['password'] && !$user['passwordrepeat']) {
        $error = "Du skal indtaste et password.";
        return;
      }
      if (isset($user['mail']) && $user['mail']) {
        if (!$this->valid->mail($user['mail'])) {
          $error = "Mail-adressen er ikke gyldig";
          return;
        }
        $confirmMail = true;
      }
      $user['id'] = $this->database->insert('user', 
          array('id', 'name', 'name_sort', 'password', 'passwordsalt'), $user);
    } else {    
      $columns = array('name', 'name_sort', 'url_website', 'url_myspace', 'url_facebook', 'text', 'mail');
      
      $oldUser = $this->getRow('user', $user['id']);
      $oldMail = $oldUser['mail'];
      if (!$user['mail'] || ($user['mail'] != $oldMail)) { // mail unset or changed
        $user['mailConfirmed'] = 0;
        $columns[] = 'mailConfirmed';
        if ($user['mail']) { // changed then
          if (!$this->valid->mail($user['mail'])) {
            $error = "Mail-adressen er ikke gyldig";
            return;
          }
          $confirmMail = true;
        } else { // and unset.
          $columns[] = 'mailToken';
          $user['mailToken'] = null;
        }
      }
      
      if ($user['password']) {
        $columns[] = 'password';
        $columns[] = 'passwordsalt';
      }
      $this->database->update('user', "id=" . $user['id'], $columns, $user);
    }
    if ($confirmMail) {
      $this->mailApi->sendConfirmationMail($user['id']);
    }
    return $user['id'];
  }

  private function saveVenue($venue, &$error) {
    if (!$venue['id']) { // new
      $venue['id'] = $this->createRow('venue', 
          array('name', 'name_sort', 'address_street', 'address_postalcode', 
              'address_city', 'address_country', 'price_entry', 'price_bottle', 
              'price_draught', 'price_shot', 'price_drink', 'wardrobe', 
              'wardrobe_price', 'music_starts_at', 'url_website', 'url_myspace', 
              'url_facebook', 'text'), $venue);
    } else { // edit
      $this->createVersion('venue', 
          array('id', 'name', 'name_sort', 'address_street', 'address_postalcode',
                  'address_city', 'address_country', 'price_entry', 'price_bottle',
                  'price_draught', 'price_shot', 'price_drink', 'wardrobe', 
                  'wardrobe_price', 'music_starts_at', 'url_website', 'url_myspace',
                  'url_facebook', 'text'), $venue);
    }
    return $venue['id'];
  }

  private function saveBand(&$band, &$error) {
    if (!$band['id']) {
      $band['id'] = $this->createRow('band', 
          array('name', 'name_sort', 'country', 'url_website', 'url_myspace', 'url_facebook', 'text'),
          $band);
    } else {
      $this->createVersion('band', 
          array('id', 'name', 'name_sort', 'country', 'url_website', 'url_myspace', 'url_facebook', 'text'), $band);
    }
    return $band['id'];
  }
  
  public function deleteRow($type, $id, &$error) {
    if (!$this->valid->posInt($id)) {
      die("Ugyldigt ID til RowApi::deleteRow");
    }
    if ($type == 'venue') {
      $used = $this->database->executeScalar(
          "SELECT 1 FROM event WHERE venueID = $id LIMIT 1");
    } else if ($type == 'band') {
      $used = $this->database->executeScalar(
          "SELECT 1 FROM appearance WHERE bandID = $id LIMIT 1");
    } else if ($type == 'event') {
      $used = $this->database->executeScalar(
          "SELECT 1 FROM guest 
           INNER JOIN event ON event.id = guest.eventID
           WHERE guest.userID <> " . USERID . " LIMIT 1");
    } else {
      die("Ugyldig type til RowApi::deleteRow");
    }
    if ($used) {
      $error = "Kan ikke slettes, da der refereres til siden.";
      return;
    }
    
    $columns = array('type', 'id', 'userID', 'action');
    $data = array_combine($columns, array($type, $id, USERID, 'delete'));
    $this->database->begin();
    if (!$this->database->executeScalar(
        "SELECT 1 FROM ${type}_version WHERE id = $id")) {
      $this->database->rollback();
      return; // så slipper vi for at gøre noget
    }
    $this->database->insert('history', $columns, $data);
    $this->database->query("DELETE FROM current WHERE type = '$type' AND id = $id");
    $this->database->commit();
  }
  
  public function versionRevert($type, $id, $version) {
    $this->valid->typeOrDie($type, "RowApi::versionRevert");
    if (!$this->valid->posInt($id) || !$this->valid->posInt($version)) {
      die("Ugyldige parametre til RowApi::versionRevert");
    }
    
    if (!$this->database->executeScalar(
        "SELECT 1 FROM ${type}_version WHERE id = $id AND version = $version LIMIT 1")) {
      die("RowApi::versionRevert - version findes ikke");
    }
    $columns = array('type', 'id', 'userID', 'action');
    $data = array_combine($columns, array($type, $id, USERID, 'revert'));
    $this->database->insert('history', $columns, $data);
    $hasCurrentVersion = $this->database->executeScalar(
        "SELECT 1 FROM current WHERE type = '$type' AND id = $id");
    if ($hasCurrentVersion) {
      $this->database->update('current', "type='$type' AND id = $id",
          array('version'), array('version' => $version));
    } else {
      $this->database->insert('current', array('type', 'id', 'version'),
          array('type' => $type, 'id' => $id, 'version' => $version));
    }
  }
  
  public function createRow($type, $columns, &$data) {
    $this->database->begin();
    $id = 1 + $this->database->executeScalar("SELECT MAX(id) FROM ${type}_version");
    array_push($columns, 'version', 'id');
    $data['version'] = 1;
    $data['id'] = $id;
    $this->database->insert("${type}_version", $columns, $data);
    
    $columns = array('type', 'id', 'version');
    $this->database->insert('current', $columns,
        array_combine($columns, array($type, $id, 1)));
    array_push($columns, 'userID', 'action');
    $this->database->insert('history', $columns, 
        array_combine($columns, array($type, $id, 1, USERID, 'create')));
    $this->database->commit();
    return $id;
  }
  
  public function createVersion($type, $columns, &$data) {
    $this->database->begin();
    array_push($columns, 'version');
    $data['version'] = 1 + $this->database->executeScalar(
        "SELECT MAX(version) FROM ${type}_version WHERE id = ${data['id']}");
    $this->database->insert("${type}_version", $columns, $data);
    
    $this->database->update('current', "type='$type' AND id = ${data['id']}",
        array('version'), array('version' => $data['version']));
    $columns = array('type', 'id', 'version', 'userID', 'action');
    $this->database->insert('history', $columns, 
        array_combine($columns, array($type, $data['id'], $data['version'], USERID, 'edit')));
    $this->database->commit();
    return $data['id'];
  }
  

  // tjek om et navn er optaget
  public function nameTaken($type, $name, $ignoredid=null) {
    $this->valid->typeOrDie($type, "Database::nameTaken");
    $table = $type;
    if ($ignoredid) {
      $smt = $this->database->prepare("SELECT COUNT(*) FROM $table WHERE name = ? AND id <> ? LIMIT 1");
      $smt->bindParam(1, $name, PDO::PARAM_STR);
      $smt->bindParam(2, $ignoredid, PDO::PARAM_INT);
    } else {
      $smt = $this->database->prepare("SELECT COUNT(*) FROM $table WHERE name = ? LIMIT 1");
      $smt->bindParam(1, $name, PDO::PARAM_STR);
    }
    return ($this->database->executeScalarSmt($smt) != 0);
  }

}
?>
