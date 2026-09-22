<?php

class Database {

  private $dbh;
  private $translevel = 0;
  private $rollbackRequested = false;

  function __construct($host, $user, $pw, $db) {
    try {
      $this->dbh = new PDO("mysql:host=$host;dbname=$db", $user, $pw);
    } catch (PDOException $e) {
      die("Databasefejl");
    }
    $this->dbh->query("SET NAMES 'utf8'");
  }
  
  public function begin() {
    if (!$this->translevel) {
      $this->dbh->beginTransaction();
    }
    $this->translevel++;
  }

  public function rollback() {
    $this->translevel--;
    if ($this->translevel == 0) {
      $this->dbh->rollBack();
      $this->rollbackRequested = false;
    } else {
      $this->rollbackRequested = true;
    }
  }

  public function commit() {
    if ($this->rollbackRequested) {
      $this->rollback();
      return;
    }
  
    $this->translevel--;
    if ($this->translevel == 0) {
      $this->dbh->commit();
    }
  }
  
  public function prepare($sql) {
    return $this->dbh->prepare($sql);
  }

  public function query($sql) {
    return $this->dbh->query($sql, PDO::FETCH_ASSOC);
  }
  
  public function exec($sql) {
    return $this->dbh->exec($sql);
  }
  
  public function printError() {
    print_r($this->dbh->errorInfo());
  }

  public function insert($table, $columns, $data) {
    $querynames = "INSERT INTO $table (";
    $queryvalues = ") VALUES (";
    foreach ($columns as $column) {
      if (!isset($data[$column])) {
        $data[$column] = null;
      }
      $querynames .= "$column, ";
      $queryvalues .= ":$column, ";
      $filtereddata[":$column"] = $data[$column];
    }
    $querynames = substr($querynames, 0, -2); // fjern sidste ', '
    $queryvalues = substr($queryvalues, 0, -2); // fjern sidste ', '
    $query = "$querynames $queryvalues )";
    $smt = $this->dbh->prepare($query);
    $smt->execute($filtereddata);
    return $this->dbh->lastInsertId();
  }
  
  public function update($table, $where, $columns, $data) {
    $query = "UPDATE $table SET ";
    foreach ($columns as $column) {
      if (!isset($data[$column])) {
        $data[$column] = null;
      }
      $query .= "$column = :$column, ";
      $filtereddata[":$column"] = $data[$column];
    }
    $query = substr($query, 0, -2); // fjern sidste ', '
    if ($where) {
      $query .= " WHERE $where";
    }
    $smt = $this->dbh->prepare($query);
    $smt->execute($filtereddata);
  }
  
  public function executeScalar($sql) {
    $smt = $this->dbh->query($sql); //$this->printError();
    $array = $smt->fetch(PDO::FETCH_NUM);
    $smt->closeCursor();
    return $array[0];
  }
  
  public function executeScalarSmt($smt) {
    $smt->execute();
    $array = $smt->fetch(PDO::FETCH_NUM);
    $smt->closeCursor();
    return $array[0];
  }

}

?>
