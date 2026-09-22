<?php

require_once '../init.php';

$eventApi = Factory::getEventApi();

$id = $_GET['id'];

$event = $eventApi->getEvent($id, true);


if ($event['time']) {
  $event['time'] = str_replace(':', '', $event['time']);
} else {
  $event['time'] = '000000';
}

$event['date'] = str_replace('-', '', $event['date']);

$event['date'] .= 'T' . $event['time'];

if ($event['enddate']) {
  $event['enddate'] = str_replace('-', '', $event['enddate']) . 'T235900';
} else {
  $event['enddate'] = $event['date'];
}

$bandstring = "";
if (count($event['bands'])) {
  foreach ($event['bands'] as $band) {
    $bandstring .= $band['name'] . " - ";
  }
  if ($bandstring) {
    $bandstring = substr($bandstring, 0, -3);
  }
}

$sep = (($bandstring && $event['name']) ? ": " : "");

$summary = "{$event['name']}$sep$bandstring";


header("Content-type: text/calendar");


?>
BEGIN:VCALENDAR<? echo "\r\n"; ?>
VERSION:1.0<? echo "\r\n"; ?>
BEGIN:VEVENT<? echo "\r\n"; ?>
DTSTART:<? echo $event['date']. "\r\n" ?>
DTEND:<? echo $event['enddate']. "\r\n" ?>
SUMMARY:<? echo utf8_decode($summary) . "\r\n" ?>
LOCATION:<? echo utf8_decode($event['venue']['name']) . "\r\n" ?>
<? /* DESCRIPTION:<? echo $event['text'] . "\r\n" ?> */ ?>
CLASS:PRIVATE<? echo "\r\n"; ?>
END:VEVENT<? echo "\r\n"; ?>
END:VCALENDAR<? echo "\r\n"; ?>
