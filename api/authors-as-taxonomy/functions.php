<?php
/**
 * Functions and globals related to the authors as taxonomy API
 *
 * @package calmpress\api\authors_as_taxonomy.
 * @version 1.0
 * @license GPLv2
 */

namespace calmpress\api\authors_as_taxonomy;

use calmpress\internal\authors_as_taxonomy as internal;

/**
 * The current version of the API.
 *
 * @api
 * @var string
 */
const VERSION = '1.0.0';

/**
 * Get the slug used for the base of authors URLs.
 *
 * @api
 * @since 1.0.0
 *
 * @return string The current slug being used.
 */
function authors_base_slug() : string {
	return internal\get_taxonomy_slug();
}

/**
 * Set the slug used for the base of authors URLs.
 *
 * If the slug is different from the previously used one, rewrite rules will be flushed.
 *
 * @api
 * @since 1.0.0
 *
 * @param string $slug The new slug to be used.
 */
function set_authors_base_slug( string $slug ) {
	internal\set_taxonomy_slug( $slug );
}

/**
 * An helper function to get a html which contains links to post author's
 * archive pages for a specific post.
 *
 * @api
 * @since 1.0.0
 *
 * @param int    $post_id   The ID of the post.
 * @param string $prefix    The string which will start the html.
 *                          Can include HTML tags.
 * @param string $separator The string to be used as separator between authors when there are multiple.
 *                          Can include HTML tags.
 * @param string $postfix   The string which will end the html.
 *                          Can include HTML tags.
 * @param string $link_class The CSS class(es) which will be assigned to the links.
 *                           If empty, no class will be added.
 * @param string $title_format Sprintf like formatting for the title attribute of the links.
 *                             %s should be used as the place holder to where the author
 *                             name might be inserted.
 *                             If inserted, the author name will be escaped, the rest of the
 *                             format can include html tags.
 * @param string $rel_type The rel type(s) which will be assigned to the links.
 *                         If empty, no rel will be added.
 * @param string $anchor_format Sprintf like formatting for the anchor of the links.
 *                              %s should be used as the place holder to where the author
 *                              name might be inserted.
 *                              If inserted, the author name will be escaped, the rest of the
 *                              format can include html tags.
 *
 * @return string In case there are no authors associated with the post, an empty
 *                string is returned, otherwise the string returned will be based
 *                on the actual author information handled with the formatting
 *                instructions passed as parameters.
 */
function formatted_authors_links( int $post_id,
								string $prefix,
								string $separator,
								string $postfix,
								string $link_class,
								string $title_format,
								string $rel_type,
								string $anchor_format ) : string {
	if ( 1 > $post_id ) {
		trigger_error( 'Invalid post ID: ' . $post_id );
		return '';
	}

	$authors = internal\post_authors( $post_id );
	if ( empty( $authors ) ) {
		return '';
	}

	$rel   = ( '' === $rel_type ) ? '' : ' rel="' . esc_attr( $rel_type ) . '"';
	$class = ( '' === $class ) ? '' : ' class="' . esc_attr( $class ) . '"';

	$html = $prefix;
	foreach ( $authors as $author ) {
		if ( $html !== $prefix ) {
			$html .= $separator;
		}
		$href   = ' href="' . esc_url( get_term_link( $author, internal\TAXONOMY_NAME ) ) . '"';
		$name   = $author->name;
		$title  = ( '' === $title_format ) ? '' : ' ' . sprintf( $title_format, esc_attr( $name ) );
		$anchor = ( '' === $anchor_format ) ? '' : sprintf( $anchor_format, esc_html( $name ) );
		$html  .= '<a' . $class . $href . $title . $rel . '>' . $anchor . '</a>';
	}
	$html .= $postfix;

	return $html;
}

/**
 * An helper function to get a html which contains a post author's
 * names.
 *
 * In case there are no authors associated with the post, an empty string is returned
 * Otherwise the string returned will be based on the actual author information handled
 * with the formatting instructions passed as parameters.
 *
 * @api
 * @since 1.0.0
 *
 * @param int    $post_id   The ID of the post.
 * @param string $prefix    The string which will start the html.
 *                          Can include HTML tags.
 * @param string $separator The string to be used as separator between authors when there are multiple.
 *                          Can include HTML tags.
 * @param string $postfix   The string which will end the html.
 *                          Can include HTML tags.
 * @param string $name_format Sprintf like formatting for the anchor of the links.
 *                            %s should be used as the place holder to where the author
 *                            name might be inserted.
 *                            If inserted, the author name will be escaped, the rest of the
 *                            format can include html tags.
 *
 * @return string In case there are no authors associated with the post, an empty
 *                string is returned, otherwise the string returned will be based
 *                on the actual author information handled with the formatting
 *                instructions passed as parameters.
 */
function formatted_authors_text( int $post_id,
								string $prefix,
								string $separator,
								string $postfix,
								string $name_format ) : string {
	if ( 1 > $post_id ) {
		trigger_error( "Invalid post ID: $post_id" );
		return '';
	}

	$authors = internal\post_authors( $post_id );
	if ( empty( $authors ) ) {
		return '';
	}

	$html = $prefix;
	foreach ( $authors as $author ) {
		if ( $html !== $prefix ) {
			$html .= $separator;
		}
		$name      = $author->name;
		$name_text = ( '' === $name_format ) ? '' : sprintf( $name_format, esc_html( $name ) );
		$html     .= $name_text;
	}
	$html .= $postfix;

	return $html;
}

/**
 * An helper function to get a html which contains a post author's
 * names.
 *
 * In case there are no authors associated with the post, an empty string is returned
 * Otherwise the string returned will be based on the actual author information handled
 * with the formatting instructions passed as parameters.
 *
 * @api
 * @since 1.0.0
 *
 * @param int $post_id   The ID of the post.
 *
 * @return Authors_Collection A collection of the authors of the post.
 */
function post_authors( int $post_id ) : Authors_Collection {
	if ( 1 > $post_id ) {
		trigger_error( "Invalid post ID: $post_id" );
		return new Authors_Collection();
	}

	return new Authors_Collection( internal\post_authors( $post_id ) );
}
