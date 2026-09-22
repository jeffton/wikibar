<?php

class MailApi {
  private $database;
  private $auth;
  private $rowApi;
  private $urls;

  public function __construct() {
    $this->database = Factory::getDatabase();
    $this->auth = Factory::getAuth();
    $this->urls = Factory::getUrls();
  }

  public function sendConfirmationMail($userId) {
    $this->rowApi = Factory::getRowApi();
  
    $user = $this->rowApi->getRow('user', $userId);
  
    $token = $this->auth->makeRandomHash(24);
    $columns[] = 'mailToken';
    $user['mailToken'] = $token;
    $this->database->update('user', "id=" . $user['id'], $columns, $user);
    
    $host = HOST;
    $body = 
<<<body
Hej {$user['name']},

Klik på nedenstående link for at bekræfte, at dette er din mail-adresse:
$host/mail.php?action=confirm&userId={$user['id']}&token=$token

wikibar.dk
body;

    $this->sendEmail($user['mail'], "Bekræft din mail-adresse på wikibar.dk", $body);
  }
  
  public function sendPasswordResetMail($name, $mail) {
    $sql = "SELECT id FROM user WHERE name = ? AND mail = ? AND mailConfirmed = 1";
    $smt = $this->database->prepare($sql);
    $smt->bindParam(1, $name, PDO::PARAM_STR);
    $smt->bindParam(2, $mail, PDO::PARAM_STR);
    $id = $this->database->executeScalarSmt($smt);
    if (!$id)
      return false;
  
    $this->rowApi = Factory::getRowApi();
    $user = $this->rowApi->getRow('user', $id);
    $token = $this->auth->makeRandomHash(24);
    $sql = "UPDATE user SET mailToken = ? WHERE id = ?";
    $smt = $this->database->prepare($sql);
    $smt->bindParam(1, $token, PDO::PARAM_STR);
    $smt->bindParam(2, $id, PDO::PARAM_INT);
    $smt->execute();
    
    $host = HOST;
    $body = 
<<<body
Hej {$user['name']},

Du har anmodet om et nyt password på wikibar.dk. Linket herunder lader dig logge ind og ændre dit password:
$host/mail.php?action=reset&userId={$user['id']}&token=$token

Bemærk, at linket kun virker én gang.

wikibar.dk
body;

    $this->sendEmail($user['mail'], "Nyt password på wikibar.dk", $body);
    return true;
  }
  
  
  public function confirmMail($userId, $token) {
    $sql = "UPDATE user SET mailConfirmed = 1, mailToken = null WHERE id = ? AND mailToken = ?";
    $smt = $this->database->prepare($sql);
    $smt->bindParam(1, $userId, PDO::PARAM_INT);
    $smt->bindParam(2, $token, PDO::PARAM_STR);
    $smt->execute();
    return $smt->rowCount();
  }
  
  public function resetPassword($userId, $token) {
    $sql = "UPDATE user SET mailToken = null, token = null, tokenValid = null WHERE id = ? AND mailToken = ?";
    $smt = $this->database->prepare($sql);
    $smt->bindParam(1, $userId, PDO::PARAM_INT);
    $smt->bindParam(2, $token, PDO::PARAM_STR);
    $smt->execute();
    if ($smt->rowCount()) {
      $this->auth->bypassLogin($userId);
      $this->urls->redirect($this->urls->getUrl('user', $userId, 'edit', true, false));
      die();
    }
    
    return false;
  }

  private function sendEmail($receiver, $subject, $body) {
    mail($receiver, $subject, $body, 
    "From: robot@wikibar.dk\r\nReply-to: info@wikibar.dk");
  }


}


?>