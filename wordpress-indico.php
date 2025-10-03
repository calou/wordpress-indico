<?php

/**
 * Plugin Name: WordPress Indico
 * Description: Imports JSON data from a URL and creates pages from it.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wordpress-indico
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define constants
define('WPI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once WPI_PLUGIN_DIR . 'includes/constants.php';
require_once WPI_PLUGIN_DIR . 'admin/settings-page.php';
require_once WPI_PLUGIN_DIR . 'includes/importer.php';
