<?php

class StatsApi {

  private $dateTools;
  private $stringTools;
  private $database;
  private $valid;

  function __construct($database) {
    $this->database = $database;
    $this->dateTools = Factory::getDateTools();
    $this->stringTools = Factory::getStringTools();
    $this->valid = Factory::getValid();
  }

  function getCounts() {
    $counts['event'] = $this->database->executeScalar("SELECT COUNT(*) FROM event");
    $counts['band'] = $this->database->executeScalar("SELECT COUNT(*) FROM band");
    $counts['venue'] = $this->database->executeScalar("SELECT COUNT(*) FROM venue");
    $counts['user'] = $this->database->executeScalar("SELECT COUNT(*) FROM user");
    return $counts;
  }
  
  function getPopularBands($number=5) {
    $smt = $this->database->query("
      SELECT band.*, COUNT(*) AS num FROM band
      INNER JOIN appearance ON band.id = appearance.bandID
      INNER JOIN event ON appearance.eventID = event.id
      INNER JOIN guest ON guest.eventID = event.id
      GROUP BY band.id
      ORDER BY num DESC, MAX(event.date) DESC
      LIMIT $number");
    return $smt->fetchAll();
  }
  
  function getPopularVenues($number=5) {
    $smt = $this->database->query("
      SELECT venue.*, COUNT(*) AS num FROM venue
      INNER JOIN event ON event.venueID = venue.id
      INNER JOIN guest ON guest.eventID = event.id
      GROUP BY venue.id
      ORDER BY num DESC, MAX(event.date) DESC
      LIMIT $number");
    return $smt->fetchAll();
  }
  
  function getPartyUsers($number=5) {
    $smt = $this->database->query("
      SELECT user.*, COUNT(*) AS num FROM guest
      INNER JOIN user ON user.id = guest.userID
      INNER JOIN event ON event.id = guest.eventID
      GROUP BY user.id
      ORDER BY num DESC, MAX(event.date) DESC
      LIMIT $number");
    return $smt->fetchAll();
  }
  
  function getPopularEvents($number=5) {
    $smt = $this->database->query("
      SELECT event.*, COUNT(*) AS num FROM
      guest INNER JOIN event ON event.id = guest.eventID
      GROUP BY event.id
      ORDER BY num DESC, event.date DESC
      LIMIT $number");
    return $smt->fetchAll();
  }
  
  function getLatest($type) {
    $this->valid->typeOrDie($type);
    $smt = $this->database->query("
      SELECT * FROM $type ORDER BY id DESC LIMIT 1");
    $rows = $smt->fetchAll();
    return $rows[0];
  }
  
  function getFriends($user, $number=5) {
    $smt = $this->database->query("
      SELECT them.*, COUNT(*) AS num FROM user you
      INNER JOIN guest youguest ON you.id = youguest.userID
      INNER JOIN event ON event.id = youguest.eventID
      INNER JOIN guest themguest ON themguest.eventID = event.id
      INNER JOIN user them ON them.id = themguest.userID
      WHERE you.id = $user
      AND them.id <> $user
      GROUP BY them.id
      ORDER BY num DESC, MAX(event.date) DESC
      LIMIT $number");
    return $smt->fetchAll();
  }
  
  

}

?>
