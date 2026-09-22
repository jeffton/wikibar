<?php
function smarty_function_calendar($params, &$smarty) {
  global $database;
  $type = $params['type'];
  $id = $params['id'];

  if ($type && $id) {
    $$type = $id;
    $smarty->assign('calendar_style', 'small');
  }

  $events = $database->getCalendar($venue, $band, $user);

  for ($i = 0; $i < count($events); $i++) {
    $events[$i]['text'] = nl2br($events[$i]['text']);
    if (($i > 0) &&
        ($events[$i]['date'] == $events[$i-1]['date']) &&
        (!$events[$i]['enddate'] && !$events[$i-1]['endddate'])) {
      $events[$i]['samedate'] = true;
    }
  }
  $smarty->assign('events', $events);
  $smarty->display('components/calendar.tpl');
}
?>