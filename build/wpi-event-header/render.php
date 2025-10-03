<?php
global $post;

$json = get_post_meta($post->ID, WPI_EVENT_JSON, true);

$data = json_decode($json);
//$event = $data['results'][0];
//$start_date = $event['startDate']['date'];
$start_date = '2024-10-11';
//$url = $event['url'];
$url = 'https://indico.esrf.fr/event/157/';
$location = 'Grenoble, France';

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <div class="wpi-event-header is-layout-constrained">
    <div class="wpi-event-header-container">

      <div class="wpi-event-header-start-date">
        &#x1F551;&nbsp;<?php echo esc_html($start_date); ?>
      </div>

      <div class="wpi-event-header-location">
        &#x26FA;&nbsp;<?php echo esc_html($location); ?>
      </div>

      <div class="wpi-event-header-links">
        <div class="wpi-event-header-register-link">
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">Register now</a>
        </div>
        <div class="wpi-event-header-ical-link">
          <a href="<?php echo wpi_ics_download_link($post->ID) ?>" download target="_blank" rel="noopener noreferrer">Add to my calendar</a>
        </div>
      </div>

    </div>
  </div>
</div>
