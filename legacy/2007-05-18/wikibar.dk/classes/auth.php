<?php

class Auth {


  // aka authenticateOrRedirectAndDie
					// friendly: om man skal redirectes til hvor man kom fra
  public function authenticate($id=0, $friendly=true) {
    $authed = Auth::authed($id);
    if (!$authed) {
      if (USERID) { // man er logget ind, men ikke som den rigtige
        Urls::redirect(HOST);
      } else {
        $proceed = ($friendly ? Urls::getCurrent(true) : HOST);
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

}


?>