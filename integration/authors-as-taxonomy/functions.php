<?php
/**
 * Integration functions for the authors as taxonomy feature.
 *
 * @package calmpress\integration\authors_as_taxonomy.
 * @version 1.0
 * @license GPLv2
 */

namespace calmpress\integration\authors_as_taxonomy;

use calmpress\internal\authors_as_taxonomy as internal;

/**
 * The current version of the integration functions.
 *
 * @var string
 */
const VERSION = '1.0.0';

// Owner metabox handle string.
const OWNER_METABOX_HANDLE = 'clam_ownerdiv';

/**
 * An handler for a manage_{$post_type}_posts_columns filter which changes
 * the heading of author column to "owner".
 *
 * @since 1.0.0
 *
 * @param array $posts_columns An array of strings with the index being the ID
 *                              of a column and the value is the heading text.
 *
 * @return string[] The $posts_columns array with the name of the author column changed
 *               to "owner".
 */
function change_author_to_owner( array $posts_columns ) : array {
	if ( isset( $posts_columns['author'] ) ) {
		$posts_columns['author'] = __( 'Owner', 'authors_as_taxonomy' );
	}

	return $posts_columns;
}

/**
 * Handler for the add_meta_box hook which removes the author metabox and adds instead
 * an owner one which is almost exactly the same except for strings.
 *
 * @since 1.0.0
 *
 * @param string   $post_type The post type of the current post.
 * @param \WP_Post $post The post.
 */
function change_author_meta_box_to_owner( string $post_type, \WP_Post $post ) {
	$post_type_object = get_post_type_object( $post_type );

	remove_meta_box( 'authordiv', $post_type, 'normal' );
	if ( post_type_supports( $post_type, 'author' ) && current_user_can( $post_type_object->cap->edit_others_posts ) ) {
		add_meta_box( OWNER_METABOX_HANDLE, __( 'Owner', 'authors_as_taxonomy' ), __NAMESPACE__ . '\post_owner_meta_box', null, 'normal', 'core' );
	}
}

/**
 * Output the owner meta box.
 *
 * Basically a direct copy of core's post_author_meta_box With only the strings being changed.
 *
 * @since 1.0.0
 *
 * @param \WP_Post $post The post for which the metabox is displayed..
 */
function post_owner_meta_box( \WP_Post $post ) {
	global $user_ID;
?>
<label class="screen-reader-text" for="post_author_override"><?php esc_html_e( 'Owner', 'authors_as_taxonomy' ); ?></label>
<?php
	wp_dropdown_users( array(
		'who'              => 'authors',
		'name'             => 'post_author_override',
		'selected'         => empty( $post->ID ) ? $user_ID : $post->post_author,
		'include_selected' => true,
		'show'             => 'display_name_with_login',
	) );
}

/**
 * Handler for the default_hidden_meta_boxes filter that ensures the owner metabox is
 * hidden by default.
 *
 * @since 1.0.0
 *
 * @param array      $hidden An array of meta boxes hidden by default.
 * @param \WP_Screen $screen WP_Screen object of the current screen.
 *
 * @return array Same as $hidden but includes also the handle of the owner metabox.
 */
function hide_owner_metabox_by_default( array $hidden, \WP_Screen $screen ) : array {
	$hidden[] = OWNER_METABOX_HANDLE;
	return $hidden;
}

/**
 * Register the authors menu as a top level admin menu.
 *
 * @since 1.0.0
 */
function set_authors_as_top_admin_menu() {

	$tax = get_taxonomy( internal\TAXONOMY_NAME );
	add_menu_page( __( 'Autors', 'authors_as_taxonomy' ), __( 'Authors', 'authors_as_taxonomy' ), $tax->cap->manage_terms, 'edit-tags.php?taxonomy=' . $tax->name, '', 'dashicons-admin-users', 69 );
}

/**
 * Filters the link to the author posts page, change it from the link to the user
 * which published the post to the link to the relevant author term page.
 *
 * If there are no authors associated with the post, return empty string.
 * If there are more than one, return an HTML which contains multiple relevant links.
 *
 * @since 1.0.0
 *
 * @param string $link HTML link.
 *
 * @return string HTML linking to the post author's term page.
 */
function the_author_posts_link( $link ) : string {

	$authors = post_authors( get_the_ID() );

	$html = '';
	foreach ( $authors as $author ) {

		if ( '' !== $html ) {
			$html .= ', ';
		}

		$html .= sprintf( '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			esc_url( get_term_link( $author, TAXONOMY_NAME ) ),
			/* translators: %s: author's name */
			esc_attr( sprintf( __( 'Content by %s', 'authors_as_taxonomy' ), $author->name ) ),
			$author->name
		);
	}

	return $html;
}

/**
 * Filters the URL to the author's page.
 *
 * If being in the context of a post, return url to an author.
 * If there are more than one, return the url for showing combined terms.
 *
 * @since 1.0.0
 *
 * @param string $link            The URL to the author's page.
 * @param int    $author_id       The author's id.
 * @param string $author_nicename The author's nice name.
 *
 * @return string the URL to the authors term(s) page.
 */
function author_link( $link, $author_id, $author_nicename ) : string {
	$authors = internal\post_authors( get_the_ID() );

	if ( empty( $authors ) ) {
		return '';
	}

	$url = get_term_link( $authors[0], TAXONOMY_NAME );
	if ( 1 === count( $authors ) ) {
		return $url;
	}

	// Hacky attempt to handle several authors by have some assumption on them
	// structure of the url, namely that the slug of the first author is a good
	// indication where the term slugs are supposed to be.
	if ( 1 === substr_count( $url, $authors[0]->slug ) ) {
		$slug = '';
		foreach ( $authors as $author ) {
			if ( '' !== $slug ) {
				$slug .= ',';
			}

			$slug .= $author->slug;
		}
		$url = str_replace( $authors[0]->slug, $slug, $url );
	}

	return $url;
}

/**
 * Filters the author's name. Return a string containing the names of all authors
 * associated with the current post, or empty if there are none.
 *
 * @since 1.0.0
 *
 * @param string $author The original name.
 *
 * @return string the URL to the authors term(s) page.
 */
function the_author( $author ) : string {
	$authors = internal\post_authors( get_the_ID() );

	$text = '';
	foreach ( $authors as $author ) {

		if ( '' !== $text ) {
			$text .= ', ';
		}

		$text .= $author->name;
	}

	return $text;
}
