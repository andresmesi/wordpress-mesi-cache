<?php
/**
 * Plugin Name: Mesi Cache
 * Plugin URI: https://github.com/andresmesi/wordpress-mesi-cache
 * Description: Ultra-light static HTML caching system for WordPress. Generates static files served directly by Apache for maximum performance.
 * Version: 1.2.2
 * Author: Mesi
 * Author URI: https://mesi.com.ar
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mesi-cache
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MESI_CACHE_VERSION', '1.2.2' );
define( 'MESI_CACHE_DIR', trailingslashit( ABSPATH ) . 'cache/' );
define( 'MESI_CACHE_OPTION', 'mesi_cache_options' );

// Core includes.
require_once __DIR__ . '/includes/functions-cache.php';
require_once __DIR__ . '/includes/hooks.php';
require_once __DIR__ . '/includes/regenerate.php';
require_once __DIR__ . '/includes/htaccess-manager.php';
require_once __DIR__ . '/includes/verifier.php';

// Admin interface.
if ( is_admin() ) {
    require_once __DIR__ . '/admin/settings-page.php';
}

// Activation hook: create cache directory and default options.
register_activation_hook(
    __FILE__,
    function() {

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	if ( $wp_filesystem && ! $wp_filesystem->is_dir( MESI_CACHE_DIR ) ) {
	    $wp_filesystem->mkdir( MESI_CACHE_DIR, FS_CHMOD_DIR );
	}

	if ( ! get_option( MESI_CACHE_OPTION ) ) {
	    update_option(
		MESI_CACHE_OPTION,
		array(
		    'regen_on_comment'  => false,
		    'bypass_logged_in'  => true,
		    'add_cache_headers' => true,
		)
	    );
	}
    }
);
