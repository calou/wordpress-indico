<?php
global $post;

$json = get_post_meta($post->ID, WPI_EVENT_JSON, true);
$event = new Event($json);
$event_details = $event->getCalendarExportData();

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
  <div class="wpi-event-header is-layout-constrained">
    <div class="wpi-event-header-container">

      <div class="wpi-event-header-start-date">
        <i class="fa-regular fa-clock"></i> <?php echo $event->startDate; ?>&nbsp;-&nbsp;<?php echo $event->endDate; ?>
      </div>

      <div class="wpi-event-header-location">
        <i class="fa-solid fa-location-dot"></i> <?php echo $event_details['location']; ?>
      </div>

      <div class="wpi-event-header-links">
        <div class="wpi-event-header-register-link">
          <a href="<?php echo esc_url($event_details['url']); ?>" target="_blank" rel="noopener noreferrer">Register now</a>
        </div>
        <div class="wpi-event-header-ellipsis">
          <i class="fa-solid fa-ellipsis-vertical"></i>

          <div class="wpi-event-header-ellipsis-menu-outer">
            <div class="wpi-event-header-ellipsis-menu">
              <a href="<?php echo wpi_ics_download_link($post->ID) ?>" download target="_blank" rel="noopener noreferrer">Add to my calendar (ICS, iCal)</a>
              <a href="<?php echo wpi_generate_google_calendar_url($post->ID) ?>" target="_blank" rel="noopener noreferrer">Add to my Google calendar</a>
              <a href="<?php echo wpi_generate_outlook_calendar_url($post->ID) ?>" target="_blank" rel="noopener noreferrer">Add to my Outlook calendar</a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
