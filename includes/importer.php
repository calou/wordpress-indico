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

  foreach ($data['results'] as $item) {
    // Assumes each $item has 'title' and 'content'
    if (isset($item['title']) && isset($item['description'])) {
      $post_data = [
        'post_title'   => sanitize_text_field($item['title']),
        'post_content' => wp_kses_post($item['description']),
        'post_status'  => 'draft',
        'post_type'    => 'page',
      ];

      $post_id = wp_insert_post($post_data);
      if ($post_id && !is_wp_error($post_id)) {
        // Save original item as post meta (encoded JSON)
        update_post_meta($post_id, '_wpi_original_json', wp_json_encode($item));
        $created++;
      }
    }
  }

  return "Successfully created $created pages.";
}
