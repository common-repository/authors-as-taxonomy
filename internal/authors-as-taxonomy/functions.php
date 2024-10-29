<?php
/**
 * Internal implementations related to the authors as taxonomy feature.
 *
 * @package calmpress\internal\authors_as_taxonomy.
 * @version 1.0
 * @license GPLv2
 */

namespace calmpress\internal\authors_as_taxonomy;

/**
 * The current version of the internal implementation.
 *
 * @var string
 */
const VERSION = '1.0.0';

// The authors taxonomy name.
const TAXONOMY_NAME = 'calm_authors';

/**
 * Get the settings.
 *
 * If the settings are not in the DB, store them there if we are in an admin context.
 * The storage in the DB is needed to avoid pointless DB access caused by WordPress
 * not being able to find the option in the autoloaded options.
 *
 * @since 1.0.0
 *
 * @return array Array filled with the various settings.
 */
function get_settings(): array {
	$settings = get_option( __NAMESPACE__, false );
	if ( false === $settings ) {
		$settings = array(
			'version' => VERSION,
			'slug'    => 'authors',
		);
		update_option( __NAMESPACE__, $settings );
		\flush_rewrite_rules();
	}
	return $settings;
}

/**
 * Get the slug used for the taxonomy URLs.
 *
 * @since 1.0.0
 *
 * @return string The current slug being used.
 */
function get_taxonomy_slug() : string {
	$settings = get_settings();

	return $settings['slug'];
}

/**
 * Set the slug used for the taxonomy URLs.
 *
 * The slug will stored as part of the settings, and if it is different from
 * the previously used one, rewrite rules will be flushed.
 *
 * @since 1.0.0
 *
 * @param string $slug The new slug to be used.
 */
function set_taxonomy_slug( string $slug ) {
	$settings = get_settings();

	$current_slug = $settings['slug'];

	if ( $slug !== $current_slug ) {
		$settings['slug'] = $slug;
		update_option( __NAMESPACE__, $settings );
		\flush_rewrite_rules();
	}
}

/**
 * The post types which support having an author.
 *
 * @since 1.0.0
 *
 * @return string[] Array of strings containing the names of the post types.
 */
function post_types_supporting_authors(): array {
	$post_types = get_post_types();
	foreach ( $post_types as $key => $post_type ) {
		if ( ! post_type_supports( $post_type, 'author' ) ) {
			unset( $post_types[ $key ] );
		}
	}

	return $post_types;
}

/**
 * Register the authors taxonomy.
 *
 * @since 1.0.0
 */
function register_taxonomy() {

	$labels = array(
		'name'                       => __( 'Authors', 'authors_as_taxonomy' ),
		'singular_name'              => __( 'Author', 'authors_as_taxonomy' ),
		'separate_items_with_commas' => __( 'Separate authors with commas', 'authors_as_taxonomy' ),
		'choose_from_most_used'      => __( 'Choose from the most used authors', 'authors_as_taxonomy' ),
		'not_found'                  => __( 'No authors found.', 'authors_as_taxonomy' ),
		'add_new_item'               => __( 'Add New Author', 'authors_as_taxonomy' ),
		'edit_item'                  => __( 'Edit Author', 'authors_as_taxonomy' ),
		'search_items'               => __( 'Search Authors', 'authors_as_taxonomy' ),
		'update_item'                => __( 'Update Author', 'authors_as_taxonomy' ),
		'back_to_items'              => __( '&larr; Back to Authors', 'authors_as_taxonomy' ),
		'view_item'                  => __( 'View Author', 'authors_as_taxonomy' ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug' => get_taxonomy_slug(),
		),
		'show_admin_column' => true,
		'show_in_menu'      => false,
	);

	// Do not associate with any CPT right now as it will be done
	// on a later hook.
	\register_taxonomy( TAXONOMY_NAME, array(), $args );
}

// CPT and taxonomy registration should be done early on init.
add_action( 'init', __NAMESPACE__ . '\register_taxonomy', 0 );

/**
 * Attach the authors taxonomy to all post types which support authors
 *
 * @since 1.0.0
 */
function attach_to_relevant_post_types() {
	$post_types = post_types_supporting_authors();
	foreach ( $post_types as $post_type ) {
		register_taxonomy_for_object_type( TAXONOMY_NAME, $post_type );
	}
}

// Associate the taxonomy with public CPTs after all initialization is finished,
// Avoid depending on weird orders of CPT registrations.
add_action( 'wp_loaded', __NAMESPACE__ . '\attach_to_relevant_post_types', 0 );

// Save a pointless query on authors editing screens (and probably more) due
// to capability check that assumes there might be a default term.
add_filter( 'pre_option_default_' . TAXONOMY_NAME, '__return_null' );

/**
 * Get the author terms of the "current" post.
 *
 * @since 1.0.0
 *
 * @param int $post_id The ID of the post.
 *
 * @return \WP_Term[] An array of author terms for authors associated with the post.
 */
function post_authors( int $post_id ) : array {

	if ( 1 > $post_id ) {
		trigger_error( 'Invalid post ID value: ' . $post_id );
		return array();
	}

	// Get the author terms associated with the current post.
	$authors = get_the_terms( $post_id, TAXONOMY_NAME );

	// Make sure the call did not error.
	// The most likely error is failure in registering the taxonomy.
	if ( is_wp_error( $authors ) ) {
		trigger_error( 'Invalid post ID value: ' . $post_id );
		return array();
	}

	// If there are no aauhors we are likely to get a false value.
	if ( ! is_array( $authors ) ) {
		return array();
	}

	return $authors;
}
