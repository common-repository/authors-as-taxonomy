<?php
/**
 * Main file of the plugin
 *
 * @package authors_as_taxonomy.
 * @version 1.0.0
 * @license GPLv2
 */

/*
Plugin Name: Authors as taxonomy
Plugin URI: https://wordpress.org/plugins/authors-as-taxonomy/
Description: Treat authors as taxonomy terms instead of as users.
Author: calmPress
Version: 1.0.0
Author URI: http://calmpress.ptg
Text Domain: authors-as-taxonomy
Domain Path: /languages
*/

namespace calmpress\plugins\authors_as_taxonomy;

use calmpress\integration\authors_as_taxonomy as integration;
use calmpress\internal\authors_as_taxonomy as internal;

// register translation.
load_plugin_textdomain( 'authors_as_taxonomy' );

/**
 * The current version of the plugin.
 *
 * @var string
 */
const VERSION = '1.0.0';

// Shortcut for using the integration namsspace in strings.
const INTEGRATION_NAMESPACE = 'calmpress\integration\authors_as_taxonomy';

require_once plugin_dir_path( __FILE__ ) . 'internal/authors-as-taxonomy/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'integration/authors-as-taxonomy/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'api/authors-as-taxonomy/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'wordpress-plugins/autoloader.php';

/**
 * Set actions and filters which make sense only at admin context.
 *
 * @since 1.0
 */
function admin_init() {

	// Change the name of the author column to "owner".
	$post_types = internal\post_types_supporting_authors();
	foreach ( $post_types as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", INTEGRATION_NAMESPACE . '\change_author_to_owner' );
	}

	// Media uses different hook format :(.
	add_filter( 'manage_media_columns', INTEGRATION_NAMESPACE . '\change_author_to_owner' );

	// Replace the author meta box with an "owner" one.
	add_filter( 'add_meta_boxes', INTEGRATION_NAMESPACE . '\change_author_meta_box_to_owner', 10, 2 );

	// By default the author meta box is hidden, owner should be treated the same.
	add_filter( 'default_hidden_meta_boxes', INTEGRATION_NAMESPACE . '\hide_owner_metabox_by_default', 10, 2 );
}

add_action( 'admin_init', __NAMESPACE__ . '\admin_init' );

add_action( 'admin_menu', INTEGRATION_NAMESPACE . '\set_authors_as_top_admin_menu' );

add_filter( 'the_author_posts_link', INTEGRATION_NAMESPACE . '\the_author_posts_link' );

/**
 * Set actions and filters which make sense only at front end context.
 *
 * @since 1.0
 */
function front_end() {

	// Replace a link to the user with a link to an achieve of possibly multiple
	// authors.
	add_filter( 'author_link', INTEGRATION_NAMESPACE . '\author_link', 10, 3 );

	// Replace a user name with list of authors names.
	add_filter( 'the_author', INTEGRATION_NAMESPACE . '\the_author' );
}

add_action( 'template_redirect', __NAMESPACE__ . '\front_end' );

/**
 * Load either Atom comment feed or Atom posts feed. For the post feed
 * Use the multi author template of the plugin, for the comments use the core one.
 *
 * @since 1.0
 *
 * @param bool $for_comments True for the comment feed, false for posts feed.
 */
function do_feed_atom( bool $for_comments ) {

	if ( $for_comments ) {
		load_template( ABSPATH . WPINC . '/feed-atom-comments.php' );
	} else {
		load_template( plugin_dir_path( __FILE__ ) . 'integration/authors-as-taxonomy/feed-atom.php' );
	}
}

// Replace the atom posts feed with one that support multiple authors.
remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
add_action( 'do_feed_atom', __NAMESPACE__ . '\do_feed_atom', 10, 1 );

/**
 * Load either RSS2 comment feed or RSS2 posts feed. For the post feed
 * Use the multi author template of the plugin, for the comments use the core one.
 *
 * @since 1.0
 *
 * @param bool $for_comments True for the comment feed, false for posts feed.
 */
function do_feed_rss2( bool $for_comments ) {

	if ( $for_comments ) {
		load_template( ABSPATH . WPINC . '/feed-rss2-comments.php' );
	} else {
		load_template( plugin_dir_path( __FILE__ ) . 'integration/authors-as-taxonomy/feed-rss2.php' );
	}
}

// Replace the RSS2 posts feed with one that support multiple authors.
remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
add_action( 'do_feed_rss2', __NAMESPACE__ . '\do_feed_rss2', 10, 1 );
