<?php

class Auth {

  private $database;
  private $urls;
  private $dateTools;

  function __construct() {
    $this->database = Factory::getDatabase();
    $this->urls = Factory::getUrls();
    $this->dateTools = Factory::getDateTools();
  }


  // aka authenticateOrRedirectAndDie
					// friendly: om man skal redirectes til hvor man kom fra
  public function authenticate($id=0, $friendly=true) {
    $authed = Auth::authed($id);
    if (!$authed) {
      if (USERID) { // man er logget ind, men ikke som den rigtige
        $this->urls->redirect(FOLDER);
      } else {
        $proceed = ($friendly ? $this->urls->getCurrent(true) : HOST);
        $passthrough = true;
        global $smarty;
        require "login.php";
      }
      die();
    }
  }

  // aka isAuthed
  public function authed($id=null) {
    return ($id ? ($id == USERID) : USERID);
  }


  public function checkLogin($user, $password) {
    $sql = "SELECT id, password, passwordsalt FROM user " .
           "WHERE name=?";
    $smt = $this->database->prepare($sql);
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
  
  public function makeToken($user) {
    $token = $this->database->executeScalar(
        "SELECT token FROM user WHERE id = $user AND tokenValid >= NOW() LIMIT 1");
    if (!$token) {
      $token = $this->makeRandomHash();
      $this->database->query(
        "UPDATE user SET token = '$token',
         tokenValid = DATE_ADD(NOW(), INTERVAL 31 DAY)
         WHERE id = $user");
    }
    setcookie('token', $token, time() + 60 * 60 * 24 * 31);
    setcookie('userId', $user, time() + 60 * 60 * 24 * 31);
  }
  
  public function clearToken($user) {
    if ($user) {
      $this->database->exec("UPDATE user SET token = NULL, tokenValid = NULL WHERE id = $user");
    }
  }
  
  public function checkToken($token, $userId) {
    $smt = $this->database->prepare(
      'SELECT id FROM user WHERE id = ? AND token = ? AND tokenValid > NOW() LIMIT 1');
    $smt->bindParam(1, $userId, PDO::PARAM_INT);
    $smt->bindParam(2, $token, PDO::PARAM_STR);
    $smt->execute();
    $rows = $smt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows)) {
      return $rows[0]['id'];
    } else {
      return null;
    }
  }
  
  public function logOut() {
    unset($_SESSION['userId']);
    if (isset($_COOKIE['token']) && isset($_COOKIE['userId'])) {
      if ($this->checkToken($_COOKIE['token'], $_COOKIE['userId'])) {
        setcookie('token', "", time() + 60 * 60 * 24 * 31); // clear
        $this->clearToken(USERID);
      }
    }
  }
  
  public function initLogin() {
    if (!isset($_SESSION['userId'])) {
      if (isset($_COOKIE['token']) && isset($_COOKIE['userId'])) {
        $userId = $this->checkToken($_COOKIE['token'], $_COOKIE['userId']);
        if ($userId) {
          $_SESSION['userId'] = $userId;
        }
      }
    }
    
    if (isset($_SESSION['userId']))
      define('USERID', $_SESSION['userId']);
    else
      define('USERID', null);
  }
  
  public function bypassLogin($userId) { // used for password reset mail thingy.
    $_SESSION['userId'] = $userId;
  }
  
  public function makeRandomHash($maxlen = 0) {
    $hash = hash('whirlpool', uniqid(rand(), true));
    if ($maxlen)
      $hash = substr($hash, 0, $maxlen);
      
    return $hash;    
  }
  
  public function makePassword(&$password, &$salt) {
    $salt = $this->makeRandomHash();
    $password = $this->hashPassword($password, $salt);
  }
  
  public function hashPassword($password, $salt) {
    return hash('whirlpool', $salt . $password);
  }

}


?>