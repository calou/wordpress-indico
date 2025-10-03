<?php

function wpi_register_menu_page()
{
  add_menu_page(
    'Indico',
    'Indico',
    'manage_options',
    'wpi-importer',
    'wpi_render_settings_page',
    'dashicons-download',
    80
  );
}
add_action('admin_menu', 'wpi_register_menu_page');

function wpi_render_settings_page()
{
?>
  <div class="wrap">
    <h1>Import an Indico event</h1>
    <form method="post" action="">
      <?php wp_nonce_field('wpi_import_nonce_action', 'wpi_import_nonce'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">URL of the event</th>
          <td><input type="url" name="wpi_event_url" size="150" required /></td>
        </tr>
      </table>
      <?php submit_button('Import'); ?>
    </form>
  </div>
  <div class="wrap">
    <h1>Import an Indico category</h1>
    <form method="post" action="">
      <?php wp_nonce_field('wpi_import_nonce_action', 'wpi_import_nonce'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">URL of the event</th>
          <td><input type="url" name="wpi_category_url" size="150" required /></td>
        </tr>
      </table>
      <?php submit_button('Import'); ?>
    </form>
  </div>

<?php

  // Handle form submission
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('wpi_import_nonce_action', 'wpi_import_nonce')) {
    if (!empty($_POST['wpi_event_url'])) {
      $url = esc_url_raw($_POST['wpi_event_url']);
      $result = wpi_import_single_event($url);
      echo '<div class="notice notice-success"><p>' . esc_html($result) . '</p></div>';
    }

    if (!empty($_POST['wpi_category_url'])) {
      $url = esc_url_raw($_POST['wpi_category_url']);
      $result = wpi_import_category($url);
      echo '<div class="notice notice-success"><p>' . esc_html($result) . '</p></div>';
    }
  }
}
