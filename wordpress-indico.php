<?php

/**
 * Plugin Name: WordPress Indico
 * Description: Imports JSON data from a URL and creates pages from it.
 * Version: 0.0.1
 * Author: Sébastien Gruchet
 * Text Domain: wordpress-indico
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define constants
define('WPI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPI_PLUGIN_URL', plugin_dir_url(__FILE__));
require_once WPI_PLUGIN_DIR . 'includes/models/event.php';

// Include necessary files
require_once WPI_PLUGIN_DIR . 'includes/constants.php';
require_once WPI_PLUGIN_DIR . 'admin/settings-page.php';
require_once WPI_PLUGIN_DIR . 'includes/importer.php';

function create_blocks_init()
{
  if (function_exists('wp_register_block_types_from_metadata_collection')) {
    wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
    return;
  }

  if (function_exists('wp_register_block_metadata_collection')) {
    wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
  }

  $manifest_data = require __DIR__ . '/build/blocks-manifest.php';
  foreach (array_keys($manifest_data) as $block_type) {
    register_block_type(__DIR__ . "/build/{$block_type}");
  }
}
add_action('init', 'create_blocks_init');


require_once WPI_PLUGIN_DIR . 'includes/ical.php';


function wpi_enqueue_fontawesome()
{
  wp_enqueue_style(
    'font-awesome',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    array(), // Dependencies
    '6.5.0'   // Version
  );
}
add_action('wp_enqueue_scripts', 'wpi_enqueue_fontawesome'); // For frontend
