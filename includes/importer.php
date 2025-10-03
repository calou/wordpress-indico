<?php

function wpi_import_single_event($url)
{
  $response = wp_remote_get($url);

  if (is_wp_error($response)) {
    return 'Failed to fetch JSON: ' . $response->get_error_message();
  }

  $json = wp_remote_retrieve_body($response);
  $data = json_decode($json, true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    return 'Invalid JSON format.';
  }

  $created = 0;

  foreach ($data as $item) {
    // Assumes each $item has 'title' and 'content'
    if (isset($item['title']) && isset($item['content'])) {
      $post_data = [
        'post_title'   => sanitize_text_field($item['title']),
        'post_content' => wp_kses_post($item['content']),
        'post_status'  => 'publish',
        'post_type'    => 'page',
      ];

      wp_insert_post($post_data);
      $created++;
    }
  }

  return "Successfully created $created pages.";
}
