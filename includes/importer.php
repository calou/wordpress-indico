<?php

function wpi_import_category($url)
{
  $json_url = wpi_get_category_json_url($url);
  $response = wp_remote_get($json_url);

  if (is_wp_error($response)) {
    return 'Failed to fetch JSON: ' . $response->get_error_message();
  }

  $json = wp_remote_retrieve_body($response);
  $data = json_decode($json, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    return 'Invalid JSON format.';
  }

  $created = '';
  foreach ($data['results'] as $item) {
    $result = wpi_import_single_event($item['url']);
    $created = $created . '<br/>' . $result;
  }
  return $created;
}


function wpi_import_single_event($url)
{

  $json_url = wpi_get_event_json_url($url);
  $response = wp_remote_get($json_url);

  if (is_wp_error($response)) {
    return 'Failed to fetch JSON: ' . $response->get_error_message();
  }

  $json = wp_remote_retrieve_body($response);
  $data = json_decode($json, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    return 'Invalid JSON format.';
  }

  $existing = get_posts([
    'post_type'  => 'page',
    'meta_key'   => WPI_EVENT_URL,
    'meta_value' => $data['url'],
    'numberposts' => 1,
    'post_status' => 'any',
  ]);

  if (empty($existing)) {
    $created = '';
    foreach ($data['results'] as $item) {
      // Assumes each $item has 'title' and 'content'
      if (isset($item['title']) && isset($item['description'])) {
        $title = sanitize_text_field($item['title']);
        $post_data = [
          'post_title'   => $title,
          'post_content' => wpi_create_content($item),
          'post_status'  => 'draft',
          'post_type'    => 'page',
        ];

        $post_id = wp_insert_post($post_data);
        if ($post_id && !is_wp_error($post_id)) {
          wpi_update_metadata($post_id, $json);
          $created = $created . "\nSuccessfully created page: " . $title;
        }
      }
    }
    return $created;
  } else {
    // Update only the metadata
    $post_id = $existing[0]->ID;
    wpi_update_metadata($post_id, $json);
    $title = $existing[0]->post_title;
    return "Successfully updated page: $title";
  }
}

function wpi_create_content($item)
{
  $raw = wp_kses_post($item['description']);

  return $raw;
}

function wpi_update_metadata($post_id, $json)
{
  $data = json_decode($json, true);

  update_post_meta($post_id, WPI_EVENT_FLAG, WPI_EVENT_FLAG_VALUE);
  update_post_meta($post_id, WPI_EVENT_URL, $data['url']);
  update_post_meta($post_id, WPI_EVENT_JSON, $json);
}

function wpi_get_event_json_url($url)
{
  $parsed = parse_url($url);

  if (!isset($parsed['host'], $parsed['path'])) {
    return false; // Invalid URL
  }

  // Ensure the path ends with a slash
  $path = rtrim($parsed['path'], '/') . '/';

  // Match URLs like /event/215/ and extract ID
  if (preg_match('#/event/(\d+)/$#', $path, $matches)) {
    $event_id = $matches[1];
    $rewritten_path = "/export/event/{$event_id}.json";

    // Build new URL
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'https';
    $host = $parsed['host'];

    $query = http_build_query([
      'detail' => 'sessions',
      'pretty' => 'yes',
    ]);

    return "{$scheme}://{$host}{$rewritten_path}?{$query}";
  }

  return false; // Not a recognized Indico event URL

}

function wpi_get_category_json_url($url)
{
  $parsed = parse_url($url);

  if (!isset($parsed['host'], $parsed['path'])) {
    return false; // Invalid URL
  }

  // Ensure the path ends with a slash
  $path = rtrim($parsed['path'], '/') . '/';

  // Match URLs like /event/215/ and extract ID
  if (preg_match('#/category/(\d+)/$#', $path, $matches)) {
    $event_id = $matches[1];
    $rewritten_path = "/export/categ/{$event_id}.json";

    // Build new URL
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'https';
    $host = $parsed['host'];

    $query = http_build_query([
      'pretty' => 'yes',
    ]);

    return "{$scheme}://{$host}{$rewritten_path}?{$query}";
  }

  return false; // Not a recognized Indico event URL

}
