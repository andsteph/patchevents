<?php
/**
 * Plugin Name:     PatchEvents
 * Plugin URI:      https://patchpress.ca/plugins/patchevents/
 * Description:     No frills event management plugin
 * Author:          Andrew Stephens
 * Author URI:      https://patchpress.ca
 * Text Domain:     patchevents
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Patchevents
 */

if ( !defined('ABSPATH') ) {
  exit;
}

if (!function_exists('write_log')) {
  function write_log($log)
  {
    if (true === WP_DEBUG) {
      if (is_array($log) || is_object($log)) {
        error_log(print_r($log, true));
      } else {
        error_log($log);
      }
    }
  }
}

if ( !class_exists('PatchEvents') ) { 

  class PatchEvents {

    // static vars -----------------------------------------------------------
    public static $version = '0.1';

    // instance vars ---------------------------------------------------------
    public $posts_per_page = 3;

    // constructor -----------------------------------------------------------
    public function __construct() {
      require plugin_dir_path(__FILE__) . 'post-types/pe-event.php'; 
      register_activation_hook( __FILE__, [$this, 'activation'] );
      add_action( 'add_meta_boxes', [$this, 'add_meta_boxes'] );
      add_action( 'save_post', [$this, 'save_postdata'] );
      add_shortcode( 'pe_events', [$this, 'events_shortcode'] );
      add_shortcode( 'pe_expired_events', [$this, 'expired_events_shortcode'] );
      add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
      write_log( "PatchEvents initialized" );
    }

    // activation hook -------------------------------------------------------
    public function activation() {
    }

    // hook for enqueing scripts/styles --------------------------------------
    public function enqueue_scripts() {
      wp_enqueue_style( 'patchevents', plugins_url('css/style.css', __FILE__), [], self::$version );
    }

    // hook for adding metaboxes ---------------------------------------------
    public function add_meta_boxes() {
      add_meta_box(
        'pe_event_details',
        'Event Details',
        [$this, 'metabox_event_details'],
        'pe-event',
        'side',
      );
    }

    // add metaboxes for event details on 'new event' screen -----------------
    public function metabox_event_details( $post ) {
      $pe_event_date = get_post_meta( $post->ID, 'pe_event_date', true );
      $pe_event_time = get_post_meta( $post->ID, 'pe_event_time', true );
      $pe_event_duration = get_post_meta( $post->ID, 'pe_event_duration', true );
      $pe_event_location = get_post_meta( $post->ID, 'pe_event_location', true );
      echo "
        <p><label>Date: <input name='pe_event_date' type='date' value='$pe_event_date'></label></p>
        <p>Time: <input name='pe_event_time' type='time' value='$pe_event_time'></label></p>
        <p>Duration: <input name='pe_event_duration' type='text' value='$pe_event_duration'></label></p>
        <p>Location: <input name='pe_event_location' type='text' value='$pe_event_location'></label></p>
      ";
    }

    // get all metadata for single event -------------------------------------
    public function get_event_meta( $post_id ) {
      $meta = [
        'date' => get_post_meta( $post_id, 'pe_event_date', true ),
        'time' => get_post_meta( $post_id, 'pe_event_time', true ),
        'duration' => get_post_meta( $post_id, 'pe_event_duration', true ),
        'location' => get_post_meta( $post_id, 'pe_event_location', true ),
      ];
      return $meta;
    }

    // update single meta in $_POST before save ------------------------------
    public function update_meta( $key, $post_id ) {
      if ( array_key_exists($key, $_POST) ) {
        update_post_meta( $post_id, $key, $_POST[$key] );
      }
    }

    // add meta data when event is saved -------------------------------------
    public function save_postdata( $post_id ) {
      $this->update_meta( 'pe_event_date', $post_id );
      $this->update_meta( 'pe_event_time', $post_id );
      $this->update_meta( 'pe_event_duration', $post_id );
      $this->update_meta( 'pe_event_location', $post_id );
    }

    // build and return events query -----------------------------------------
    public function get_events_query($expired=false) {
      $today = date( 'Y-m-d' );
      $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
      if ( $expired ) {
        $order = 'desc';
        $compare = '<';
      } else {
        $order = 'asc';
        $compare = '>=';
      }
      $meta_query = [
        [
          'key' => 'pe_event_date',
          'compare' => $compare,
          'value' => $today,
          'type' => 'DATE'
        ]
      ];
      $query = new WP_Query(
        [
          'meta_key' => 'pe_event_date',
          'orderby' => 'meta_value',
          'order' => $order,
          'paged' => $paged,
          'posts_per_page' => $this->posts_per_page,
          'post_status' => 'publish',
          'post_type' => 'pe-event',
          'meta_query' => $meta_query,
        ]
      );
      return $query;
    }

    // get html of single event ----------------------------------------------
    public function show_event($event_id) {
      $img = get_the_post_thumbnail( $event_id );
      $meta = $this->get_event_meta( $event_id );
      $html = '';
      $html .= '<div class="pe-event">';
      $html .= "<div class='pe-event-image'>$img</div>";
      $html .= '<div class="pe-event-content">';
      $html .= '<h3 class="pe-event-content-title">' . get_the_title() . '</h3>';
      foreach ( $meta as $key=>$value ) {
        $fmt = '
            <div class="pe-event-content-row">
              <div>%s:</div><div>%s</div>
            </div>
          ';
        $html .= sprintf( $fmt, $key, $value );
      }
      $html .= '<div class="pe-event-content-description">' . get_the_content( get_the_ID() ) . "</div>";
      $html .= '</div> <!-- .pe-event-content -->';
      $html .= '</div> <!-- .pe-event -->';
      return $html;
    }

    // get html of all events based on query ---------------------------------
    public function show_events($query) {
      $html = '';
      while ( $query->have_posts() ) {
        $query->the_post();
        $img = get_the_post_thumbnail();
        $meta = $this->get_event_meta( get_the_ID() );
        $html .= $this->show_event( get_the_ID() );
      }
      $html .= paginate_links (
        [
          'base' => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
          'total' => $query->max_num_pages,
          'current' => max( 1, get_query_var('paged') ),
        ]
      );
      wp_reset_postdata();
      return $html;
    }

    // shortcode to show upcoming events -------------------------------------
    public function events_shortcode() {
      $query = $this->get_events_query();
      return $this->show_events( $query );
    }

    // shortcode to show previous events -------------------------------------
    public function expired_events_shortcode() {
      $query = $this->get_events_query( $expired = true );
      return $this->show_events( $query );
    }

  }

  // create instance ---------------------------------------------------------
  $patch_events = new PatchEvents();

}

