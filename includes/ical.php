<?php

// 1. Add the download link to your event page template
function wpi_ics_download_link($event_id)
{
  $download_url = add_query_arg([
    'action' => 'download_event_ics',
    'event_id' => $event_id,
    'nonce' => wp_create_nonce('download_ics_' . $event_id)
  ], admin_url('admin-ajax.php'));

  return $download_url;
}

// 2. Handle the ICS file generation and download
add_action('wp_ajax_download_event_ics', 'handle_ics_download');
add_action('wp_ajax_nopriv_download_event_ics', 'handle_ics_download');

function handle_ics_download()
{
  $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
  $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

  // Verify nonce
  if (!wp_verify_nonce($nonce, 'download_ics_' . $event_id)) {
    wp_die('Security check failed');
  }

  $json = get_post_meta($event_id, WPI_EVENT_JSON, true);

  $event_title = '';
  $event_start = get_post_meta($event_id, 'event_start_datetime', true);
  $event_end = get_post_meta($event_id, 'event_end_datetime', true);
  $event_location = get_post_meta($event_id, 'event_location', true);
  $event_description = get_post_meta($event_id, 'event_description', true);

  // Generate ICS content
  $ics_content = generate_ics_file(
    $event_title,
    $event_start,
    $event_end,
    $event_location,
    $event_description,
    get_permalink($event_id)
  );

  // Send headers for file download
  header('Content-Type: text/calendar; charset=utf-8');
  header('Content-Disposition: attachment; filename="event-' . $event_id . '.ics"');
  header('Content-Length: ' . strlen($ics_content));
  header('Cache-Control: no-cache');

  echo $ics_content;
  exit;
}

// 3. Generate the ICS file content
function generate_ics_file($title, $start, $end, $location, $description, $url)
{
  // Format dates for ICS (YYYYMMDDTHHmmss)
  $start_date = date('Ymd\THis', strtotime($start));
  $end_date = date('Ymd\THis', strtotime($end));
  $timestamp = date('Ymd\THis');

  // Create unique ID
  $uid = md5($title . $start) . '@' . $_SERVER['HTTP_HOST'];

  // Escape special characters in ICS format
  $title = ics_escape($title);
  $description = ics_escape($description);
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

  if (!empty($description)) {
    $ics .= "DESCRIPTION:" . $description . "\r\n";
  }

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

// 5. Example usage in your event template
/*
// In your single-event.php or wherever you display event details:
if (have_posts()) {
    while (have_posts()) {
        the_post();
        
        // Your event details display
        echo '<h1>' . get_the_title() . '</h1>';
        // ... other event info ...
        
        // Add the download link
        add_ics_download_link(get_the_ID());
    }
}
*/
