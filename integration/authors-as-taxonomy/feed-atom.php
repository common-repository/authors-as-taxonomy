<?php
/**
 * Atom Feed Template for displaying Atom Posts feed.
 *
 * @package WordPress
 */

use calmpress\internal\authors_as_taxonomy as internal;

header( 'Content-Type: ' . feed_content_type( 'atom' ) . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>';

/** This action is documented in wp-includes/feed-rss2.php */
do_action( 'rss_tag_pre', 'atom' );
?>
<feed
	xmlns="http://www.w3.org/2005/Atom"
	xmlns:thr="http://purl.org/syndication/thread/1.0"
	xml:lang="<?php bloginfo_rss( 'language' ); ?>"
	xml:base="<?php bloginfo_rss( 'url' ); ?>/wp-atom.php"
	<?php
	/**
	 * Fires at end of the Atom feed root to add namespaces.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_ns' );
	?>
>
	<title type="text"><?php wp_title_rss(); ?></title>
	<subtitle type="text"><?php bloginfo_rss( 'description' ); ?></subtitle>

	<updated>
	<?php
		$date = get_lastpostmodified( 'GMT' );
		echo $date ? mysql2date( 'Y-m-d\TH:i:s\Z', $date, false ) : date( 'Y-m-d\TH:i:s\Z' );
	?>
	</updated>

	<link rel="alternate" type="<?php bloginfo_rss( 'html_type' ); ?>" href="<?php bloginfo_rss( 'url' ); ?>" />
	<id><?php bloginfo( 'atom_url' ); ?></id>
	<link rel="self" type="application/atom+xml" href="<?php self_link(); ?>" />

	<?php
	/**
	 * Fires just before the first Atom feed entry.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_head' );

	while ( have_posts() ) :
		the_post();
	?>
	<entry>
		<?php foreach ( internal\post_authors( get_the_ID() ) as $author ) { ?>
		<author>
			<name><?php echo htmlspecialchars( $author->name, ENT_XML1 ); ?></name>
		</author>
		<?php } ?>
		<title type="<?php html_type_rss(); ?>"><![CDATA[<?php the_title_rss(); ?>]]></title>
		<link rel="alternate" type="<?php bloginfo_rss( 'html_type' ); ?>" href="<?php the_permalink_rss(); ?>" />
		<id><?php the_guid(); ?></id>
		<updated><?php echo get_post_modified_time( 'Y-m-d\TH:i:s\Z', true ); ?></updated>
		<published><?php echo get_post_time( 'Y-m-d\TH:i:s\Z', true ); ?></published>
		<?php the_category_rss( 'atom' ); ?>
		<summary type="<?php html_type_rss(); ?>"><![CDATA[<?php the_excerpt_rss(); ?>]]></summary>
<?php if ( ! get_option( 'rss_use_excerpt' ) ) : ?>
		<content type="<?php html_type_rss(); ?>" xml:base="<?php the_permalink_rss(); ?>"><![CDATA[<?php the_content_feed( 'atom' ); ?>]]></content>
<?php endif; ?>
	<?php
	atom_enclosure();
	/**
	 * Fires at the end of each Atom feed item.
	 *
	 * @since 2.0.0
	 */
	do_action( 'atom_entry' );

	if ( get_comments_number() || comments_open() ) :
		?>
		<link rel="replies" type="<?php bloginfo_rss( 'html_type' ); ?>" href="<?php the_permalink_rss(); ?>#comments" thr:count="<?php echo get_comments_number(); ?>"/>
		<link rel="replies" type="application/atom+xml" href="<?php echo esc_url( get_post_comments_feed_link( 0, 'atom' ) ); ?>" thr:count="<?php echo get_comments_number(); ?>"/>
		<thr:total><?php echo get_comments_number(); ?></thr:total>
	<?php endif; ?>
	</entry>
	<?php endwhile; ?>
</feed>
