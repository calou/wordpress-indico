<?php

function wpi_generate_ics($event)
{
  $event_details = $event->getCalendarExportData();

  // Generate ICS content
  return  generate_ics_file(
    $event_details['title'],
    $event_details['start'],
    $event_details['end'],
    $event_details['location'],
    get_permalink($event->id)
  );
}

// 3. Generate the ICS file content
function generate_ics_file($title, $start, $end, $location, $url)
{
  // Format dates for ICS (YYYYMMDDTHHmmss)
  $start_date = date('Ymd\THis', strtotime($start));
  $end_date = date('Ymd\THis', strtotime($end));
  $timestamp = date('Ymd\THis');

  // Create unique ID
  $uid = md5($title . $start) . '@' . $_SERVER['HTTP_HOST'];

  // Escape special characters in ICS format
  $title = ics_escape($title);
  $location = ics_escape($location);
  $url = ics_escape($url);

  // Build ICS content
  $ics = "BEGIN:VCALENDAR\r\n";
  $ics .= "VERSION:2.0\r\n";
  $ics .= "PRODID:-//Your Site Name//Event Calendar//EN\r\n";
  $ics .= "CALSCALE:GREGORIAN\r\n";
  $ics .= "METHOD:PUBLISH\r\n";
  $ics .= "BEGIN:VEVENT\r\n";
  $ics .= "UID:" . $uid . "\r\n";
  $ics .= "DTSTAMP:" . $timestamp . "\r\n";
  $ics .= "DTSTART:" . $start_date . "\r\n";
  $ics .= "DTEND:" . $end_date . "\r\n";
  $ics .= "SUMMARY:" . $title . "\r\n";

  if (!empty($location)) {
    $ics .= "LOCATION:" . $location . "\r\n";
  }

  if (!empty($url)) {
    $ics .= "URL:" . $url . "\r\n";
  }

  $ics .= "STATUS:CONFIRMED\r\n";
  $ics .= "SEQUENCE:0\r\n";
  $ics .= "END:VEVENT\r\n";
  $ics .= "END:VCALENDAR\r\n";

  return $ics;
}

// 4. Helper function to escape ICS special characters
function ics_escape($text)
{
  $text = str_replace('\\', '\\\\', $text);
  $text = str_replace(',', '\\,', $text);
  $text = str_replace(';', '\\;', $text);
  $text = str_replace("\n", '\\n', $text);
  $text = str_replace("\r", '', $text);
  return $text;
}

function wpi_generate_google_calendar_url($event_id)
{
  $json = get_post_meta($event_id, WPI_EVENT_JSON, true);
  $event = new Event($json);
  $event_details = $event->getCalendarExportData();
  $params = [
    'action' => 'TEMPLATE',
    'text' => $event_details['title'],
    'dates' => date('Ymd\THis', strtotime($event_details['start'])) . '/' . date('Ymd\THis', strtotime($event_details['end'])),
    'details' => get_permalink($event_id),
    'location' => $event_details['location'],
    'sprop' => 'website:' . home_url()
  ];

  return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
}

// 3. Outlook.com Calendar URL generator
function wpi_generate_outlook_calendar_url($event_id)
{
  $json = get_post_meta($event_id, WPI_EVENT_JSON, true);
  $event = new Event($json);
  $event_details = $event->getCalendarExportData();
  $params = [
    'path' => '/calendar/action/compose',
    'rru' => 'addevent',
    'subject' => $event_details['title'],
    'startdt' => date('Y-m-d\TH:i:s', strtotime($event_details['start'])),
    'enddt' => date('Y-m-d\TH:i:s', strtotime($event_details['end'])),
    'location' => $event_details['location'],
    'body' => get_permalink($event_id)
  ];

  return 'https://outlook.live.com/calendar/0/deeplink/compose?' . http_build_query($params);
}
