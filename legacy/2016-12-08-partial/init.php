<?php


function rel($path) {
  return (dirname(__FILE__) . '/' . $path);
}

require_once rel('config.php');

$host = "//${_SERVER['HTTP_HOST']}";
define('HOST', $host);
define('FOLDER', $folder);
date_default_timezone_set($timezone);

// smarty setup
define('SMARTY_DIR', $smartylib);
require_once(SMARTY_DIR . 'Smarty.class.php');
$smarty = new Smarty();
$smarty->template_dir = $smartylocal . 'templates';
$smarty->compile_dir =  $smartylocal . 'templates_c';
$smarty->config_dir =   $smartylocal . 'config';
$smarty->cache_dir =    $smartylocal . 'cache';
define('PLUGINS_DIR',   $smartylocal . 'plugins'); // den bruger vi senere
$smarty->plugins_dir[] = PLUGINS_DIR;

$smarty->assign('scriptversion', 15);
$smarty->assign('host', $host);
$smarty->assign('folder', $folder);


$smarty->load_filter('output','trimwhitespace');

if ($sessionpath) {
  ini_set('session.save_path', $sessionpath);
}
session_start();

// småfunktioner
require_once rel("_utf8/utf8.php");
require_once rel("_utf8/ucfirst.php");
require_once rel("_utf8/strcasecmp.php");



class Factory {
  private static $database;
  private static $rowApi;
  private static $eventApi;
  private static $formatting;
  private static $urls;
  private static $dateTools;
  private static $stringTools;
  private static $valid;
  private static $auth;
  private static $statsApi;
  private static $historyApi;
  private static $mailApi;

  public function init() {
    require_once rel("classes/database.php");
    global $dbhost, $dbuser, $dbpass, $dbbase;
    Factory::$database = new Database($dbhost, $dbuser, $dbpass, $dbbase);
    $auth = Factory::getAuth();
    $auth->initLogin();
  }

  public function getDatabase() {
    return Factory::$database;
  }

  public function getRowApi() {
    if (is_null(Factory::$rowApi)) {
      require_once rel("classes/rowapi.php");
      Factory::$rowApi = new RowApi();
    }
    return Factory::$rowApi;
  }

  public function getEventApi() {
    if (is_null(Factory::$eventApi)) {
      require_once rel("classes/eventapi.php");
      Factory::$eventApi = new EventApi();
    }
    return Factory::$eventApi;
  }

  public function getFormatting() {
    if (is_null(Factory::$formatting)) {
      require_once rel("classes/formatting.php");
      Factory::$formatting = new Formatting();
    }
    return Factory::$formatting;
  }

  public function getUrls() {
    if (is_null(Factory::$urls)) {
      require_once rel("classes/urls.php");
      Factory::$urls = new Urls();
    }
    return Factory::$urls;
  }

  public function getDateTools() {
    if (is_null(Factory::$dateTools)) {
      require_once rel("classes/datetools.php");      
      Factory::$dateTools = new DateTools();
    }
    return Factory::$dateTools;
  }

  public function getStringTools() {
    if (is_null(Factory::$stringTools)) {
      require_once rel("classes/stringtools.php");      
      Factory::$stringTools = new StringTools();
    }
    return Factory::$stringTools;
  }

  public function getValid() {
    if (is_null(Factory::$valid)) {
      require_once rel("classes/valid.php");      
      Factory::$valid = new Valid();
    }
    return Factory::$valid;
  }

  public function getAuth() {
    if (is_null(Factory::$auth)) {
      require_once rel("classes/auth.php");
      Factory::$auth = new Auth();
    }
    return Factory::$auth;
  }

  public function getStatsApi() {
    if (is_null(Factory::$statsApi)) {
      require_once rel("classes/statsapi.php");
      Factory::$statsApi = new StatsApi(Factory::$database);
    }
    return Factory::$statsApi;
  }
  
  public function getHistoryApi() {
    if (is_null(Factory::$historyApi)) {
      require_once rel("classes/historyapi.php");
      Factory::$historyApi = new HistoryApi(Factory::$database);
    }
    return Factory::$historyApi;
  }
  
  public function getMailApi() {
    if (is_null(Factory::$mailApi)) {
      require_once rel("classes/mailapi.php");
      Factory::$mailApi = new MailApi();
    }
    return Factory::$mailApi;
  }
  

}
Factory::init();

unsetConfigVars();

?>
