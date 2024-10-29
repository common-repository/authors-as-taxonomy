<?php
/**
 * Implementattion of the author collection class.
 *
 * @package calmpress\api\authors_as_taxonomy.
 * @version 1.0
 * @license GPLv2
 */

namespace calmpress\api\authors_as_taxonomy;

/**
 * A collection of authors which is iteratable and countable.
 *
 * @api
 * @since 1.0.0
 */
class Authors_Collection implements iterator, Countable {

	/**
	 * Array of author taxonomy terms.
	 *
	 * @var \WP_Term[]
	 */
	private $terms;

	/**
	 * Construct a collection of authors from author taxonomy terms.
	 *
	 * @param \WP_Term ...$terms Array, iterator or variable list of the author terms.
	 */
	public function __construct( \WP_Term ...$terms ) {
		$this->terms = $terms;
		foreach ( $this->terms as $key => $term ) {
			if ( internal\TAXONOMY_NAME !== $term->taxonomy ) {
				unset( $this->terms[ $key ] );
				trigger_error( "A term do not belong to the author taxonomy. id: $term->term_id" );
			}
		}
	}

	/**
	 * The number of authors in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return int The number of authors in the collection.
	 */
	public function count() : int {
		return count( $this->terms );
	}

	/**
	 * The current author.
	 *
	 * @since 1.0.0
	 *
	 * @return Author The current author.
	 */
	public function current() : author {
		return new Author( current( $this->terms ) );
	}

	/**
	 * The key identifying the current element of the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed The key identifying the current element of the collection.
	 */
	public function key() {
		return key( $this->terms );
	}

	/**
	 * Advance the internal position to the next item.
	 *
	 * @since 1.0.0
	 */
	public function next() {
		next( $this->terms );
	}

	/**
	 * Identify if there are any authors in the collection.
	 *
	 * @since 1.0.0
	 *
	 * @return bool false if the collection is empty, otherwise true.
	 */
	public function valid() : bool {
		return 0 !== count( $this->terms );
	}

	/**
	 * Set position to the first item.
	 *
	 * @since 1.0.0
	 */
	public function rewind() {
		reset( $this->terms );
	}
}
