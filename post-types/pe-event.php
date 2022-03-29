<?php

/**
 * Registers the `pe_event` post type.
 */
function pe_event_init() {
	register_post_type(
		'pe-event',
		[
			'labels'                => [
				'name'                  => __( 'Events', 'patchevents' ),
				'singular_name'         => __( 'Event', 'patchevents' ),
				'all_items'             => __( 'All Events', 'patchevents' ),
				'archives'              => __( 'Event Archives', 'patchevents' ),
				'attributes'            => __( 'Event Attributes', 'patchevents' ),
				'insert_into_item'      => __( 'Insert into Event', 'patchevents' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Event', 'patchevents' ),
				'featured_image'        => _x( 'Featured Image', 'pe-event', 'patchevents' ),
				'set_featured_image'    => _x( 'Set featured image', 'pe-event', 'patchevents' ),
				'remove_featured_image' => _x( 'Remove featured image', 'pe-event', 'patchevents' ),
				'use_featured_image'    => _x( 'Use as featured image', 'pe-event', 'patchevents' ),
				'filter_items_list'     => __( 'Filter Events list', 'patchevents' ),
				'items_list_navigation' => __( 'Events list navigation', 'patchevents' ),
				'items_list'            => __( 'Events list', 'patchevents' ),
				'new_item'              => __( 'New Event', 'patchevents' ),
				'add_new'               => __( 'Add New', 'patchevents' ),
				'add_new_item'          => __( 'Add New Event', 'patchevents' ),
				'edit_item'             => __( 'Edit Event', 'patchevents' ),
				'view_item'             => __( 'View Event', 'patchevents' ),
				'view_items'            => __( 'View Events', 'patchevents' ),
				'search_items'          => __( 'Search Events', 'patchevents' ),
				'not_found'             => __( 'No Events found', 'patchevents' ),
				'not_found_in_trash'    => __( 'No Events found in trash', 'patchevents' ),
				'parent_item_colon'     => __( 'Parent Event:', 'patchevents' ),
				'menu_name'             => __( 'Events', 'patchevents' ),
			],
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
      'supports'              => [ 
        'title', 
        'editor', 
        'thumbnail', 
      ],
			'has_archive'           => true,
			'rewrite'               => true,
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-admin-post',
			'show_in_rest'          => true,
			'rest_base'             => 'pe-event',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		]
	);

}

add_action( 'init', 'pe_event_init' );

/**
 * Sets the post updated messages for the `pe_event` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `pe_event` post type.
 */
function pe_event_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['pe-event'] = [
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Event updated. <a target="_blank" href="%s">View Event</a>', 'patchevents' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'patchevents' ),
		3  => __( 'Custom field deleted.', 'patchevents' ),
		4  => __( 'Event updated.', 'patchevents' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'patchevents' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Event published. <a href="%s">View Event</a>', 'patchevents' ), esc_url( $permalink ) ),
		7  => __( 'Event saved.', 'patchevents' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview Event</a>', 'patchevents' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Event</a>', 'patchevents' ), date_i18n( __( 'M j, Y @ G:i', 'patchevents' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview Event</a>', 'patchevents' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	];

	return $messages;
}

add_filter( 'post_updated_messages', 'pe_event_updated_messages' );

/**
 * Sets the bulk post updated messages for the `pe_event` post type.
 *
 * @param  array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
 *                              keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param  int[] $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 * @return array Bulk messages for the `pe_event` post type.
 */
function pe_event_bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	global $post;

	$bulk_messages['pe-event'] = [
		/* translators: %s: Number of Events. */
		'updated'   => _n( '%s Event updated.', '%s Events updated.', $bulk_counts['updated'], 'patchevents' ),
		'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 Event not updated, somebody is editing it.', 'patchevents' ) :
						/* translators: %s: Number of Events. */
						_n( '%s Event not updated, somebody is editing it.', '%s Events not updated, somebody is editing them.', $bulk_counts['locked'], 'patchevents' ),
		/* translators: %s: Number of Events. */
		'deleted'   => _n( '%s Event permanently deleted.', '%s Events permanently deleted.', $bulk_counts['deleted'], 'patchevents' ),
		/* translators: %s: Number of Events. */
		'trashed'   => _n( '%s Event moved to the Trash.', '%s Events moved to the Trash.', $bulk_counts['trashed'], 'patchevents' ),
		/* translators: %s: Number of Events. */
		'untrashed' => _n( '%s Event restored from the Trash.', '%s Events restored from the Trash.', $bulk_counts['untrashed'], 'patchevents' ),
	];

	return $bulk_messages;
}

add_filter( 'bulk_post_updated_messages', 'pe_event_bulk_updated_messages', 10, 2 );
