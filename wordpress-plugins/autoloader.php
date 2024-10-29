<?php
/**
 * Common autoloader implementation functions.
 *
 * @package calmpress\wordpress_plugins.
 * @version 1.0
 * @license GPLv2
 */

namespace calmpress\wordpress_plugins;

// Prevent errors relating to having several plugins with this autoloader.
if ( function_exists( __NAMESPACE__ . '\autoload' ) ) {
	return;
}

/**
 * The current version of the autoloader implementation.
 *
 * @since 1.0.0
 *
 * @var string
 */
const VERSION = '1.0.0';

spl_autoload_register( __NAMESPACE__ . '\autoload' );

/**
 * Handle autoloading of the classes used in the plugin.
 *
 * @since 1.0.0
 *
 * @param string $class_name The class name needs loading.
 */
function autoload( string $class_name ) {

	// If the specified $class_name does not include our namespace, duck out.
	if ( false === strpos( $class_name, 'calmpress' ) ) {
		return;
	}

	// The names space part after the clampress one should be the relative path to the file.
	$relative_path = substr( $class_name, 10 );

	// Need to replace underscores with dashes on the actual class name, add
	// a "class-" prefix to get the possible file name and ensure it is lower case.
	$parts = explode( '\\', $relative_path );
	$parts[ count( $parts ) - 1 ] = 'class-' . strtolower( str_replace( '_', '-', $parts[ count( $parts ) - 1 ] ) );
	$relative_path = join( '\\', $parts );

	// Combine the path of the plugin root directory with the relative path
	// "reinventing" plugin_dir_path as it does not support levels.
	$path = trailingslashit( dirname( __FILE__, 2 ) ) . $relative_path;

	// Use include instead of require as if we have other calmpress autoloaders
	// we might be handling a class which is not part of the plugin, and it may
	// be normal for the file not to be in this plugin.
	include $path;
}
