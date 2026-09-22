<?php

class HistoryApi {

  private $database;
  private $stringTools;

  public function __construct($database) {
    $this->database = $database;
    $this->stringTools = Factory::getStringTools();
  }

  public function getHistory($type, $id) {
    $sql = "SELECT history.*, user.name AS userName FROM history
            LEFT JOIN user ON history.userID = user.id
            WHERE history.type = ? AND history.id = ?
            ORDER BY historyID DESC";
    $smt = $this->database->prepare($sql);
    $smt->bindParam(1, $type, PDO::PARAM_STR);
    $smt->bindParam(2, $id, PDO::PARAM_INT);
    $smt->execute();
    $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
    $smt->closeCursor();
    
    $actions = Array(
      'create' => 'Oprettet',
      'edit' => 'Redigeret',
      'revert' => 'Gendannet',
      'delete' => 'Slettet'
    );
    
    for ($i = 0; $i < count($rows); $i++) {
      $rows[$i]['user']['id'] = $rows[$i]['userID'];
      $rows[$i]['user']['name'] = $rows[$i]['userName'];
      unset($rows[$i]['userID']);
      unset($rows[$i]['userName']);
      
      $rows[$i]['action'] = $actions[$rows[$i]['action']];
    }
    
    return $this->stringTools->escape($rows);
  }

}

?>