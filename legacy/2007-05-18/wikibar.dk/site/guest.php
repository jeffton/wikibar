<?

require_once "init.php";

Auth::authenticate();

$event = $_REQUEST['event'];
$action = $_REQUEST['action'];


if ($action == "add") {
  $database->addGuest($event, USERID);
} else if ($action == "remove") {
  $database->removeGuest($event, USERID);
}


Urls::redirect(HOST);



?>