<?php 
global $patch_events;
$meta = $patch_events->get_event_meta(get_the_ID());
?>
<div class="pe-event">
  <div class="pe-event-image"><?php echo the_post_thumbnail(); ?></div>
  <div class="pe-event-content">
    <h3 class="pe-event-title"><?php the_title(); ?></h3>
    <div class="pe-event-date">
      <?php $patch_events->meta(
      <?php echo $meta['pe_event_date']; ?>
    </div>
    <div class="pe-event-time">
      <?php echo $meta['pe_event_time']; ?>
    </div>
    <div class="pe-event-duration">
      <?php echo $meta['
    </div>
  </div>
</div>
