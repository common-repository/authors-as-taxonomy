<?php
/**
 * Implementation of the author class.
 *
 * @package calmpress\api\authors_as_taxonomy.
 * @version 1.0.0
 * @license GPLv2
 */

namespace calmpress\api\authors_as_taxonomy;

use calmpress\internal\authors_as_taxonomy as internal;

/**
 * A post author.
 *
 * @api
 * @since 1.0.0
 */
class Author {

	/**
	 * The term representation of the author.
	 *
	 * @since 1.0.0
	 *
	 * @var \WP_Term.
	 */
	private $term;

	/**
	 * Create an author based on the term.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Term $term The term holding the information about the author.
	 */
	public function __construct( \WP_Term $term ) {
		$this->term = $term;
		if ( internal\TAXONOMY_NAME !== $term->taxonomy ) {
			trigger_error( "A term do not belong to the author taxonomy. id: $term->term_id" );
		}
	}

	/**
	 * The name of the author.
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the author.
	 */
	public function name() : string {
		return $this->term->name;
	}

	/**
	 * The URL of the author archivers.
	 *
	 * @since 1.0.0
	 *
	 * @return string The URL of the first page of the author archivers.
	 */
	public function archive_url() : string {
		return get_term_link( $this->term, internal\TAXONOMY_NAME );
	}
}
