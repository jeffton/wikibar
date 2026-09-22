<?php
function smarty_function_calendar($params, &$smarty) {
  $eventApi = Factory::getEventApi();
  $valid = Factory::getValid();

  $type = $params['type'];
  $id = $params['id'];
  $template = $params['template'];
  $eventIds = $params['events'];
  $perpage = $params['perpage'];
  
  $highlight = $_GET['highlight'];
  $page = $params['page'];
  if (!$page) {
    $page = $_GET['page'];
  }

  if (!$valid->posInt($highlight)) {
    unset($highlight);
  }
  if ($type && $id) {
    $$type = $id;
  }

  if (!$eventIds) {
    $events = $eventApi->getCalendar($venue, $band, $user, $page, $highlight,
        $archivePages, $pages, $perpage);
  } else {
    $events = $eventApi->getEvents($eventIds);
  }
  for ($i = 0; $i < count($events); $i++) {
    $events[$i]['text'] = nl2br($events[$i]['text']);
    if (($i > 0) &&
        ($events[$i]['date'] == $events[$i-1]['date']) &&
        (!$events[$i]['enddate'] && !$events[$i-1]['enddate'])) {
      $events[$i]['samedate'] = true;
    }
  }
  
  $twoPagesBack = $page - 2;
  if ($twoPagesBack == 0 || ($twoPagesBack < 0 && $page > 0)) {
    $twoPagesBack--;
  }
  $twoPagesForward = $page + 2;
  if ($twoPagesForward == 0 || ($twoPagesForward > 0 && $page < 0)) {
    $twoPagesForward++;
  }
  
  unset($archivePageNumbers); unset($pageNumbers);
  for ($p = $twoPagesBack; $p <= $twoPagesForward; $p++) {
    if ($p == 0 || $p > $pages || ($p < 0 && -$p > $archivePages)) {
      continue;
    }
    if ($p < 0) {
      $archivePageNumbers[] = $p;
    } else {
      $pageNumbers[] = $p;
    }
  }
  
  $smarty->assign('highlight', $highlight);
  $smarty->assign('events', $events);
  $smarty->assign('archivePageNumbers', $archivePageNumbers);
  $smarty->assign('pageNumbers', $pageNumbers);
  $smarty->assign('page', $page);
  $smarty->display("calendar/$template.tpl");
}
?>
