<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ==========================================================
 * MESI-Cache — htaccess-manager.php
 * ----------------------------------------------------------
 * Auto-detects WordPress install path (/, /wp/, /blog/, etc.)
 * and generates the correct .htaccess rewrite block
 * for static HTML cache serving.
 * ==========================================================
 */

/**
 * Insert or update MESI-Cache block
 */
function mesi_cache_insert_htaccess_block() {
    $htaccess_file = ABSPATH . '.htaccess';

    // Detect subpath from home_url() (e.g. /wp, /blog, or empty)
    $subpath = trim( wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
    $base    = $subpath ? "/$subpath/" : '/';
    $cacheprefix = $subpath ? "$subpath/" : '';

    // Build the rewrite block (supports both permalink modes)
// Build the rewrite block (supports both permalink modes)
$block  = '# BEGIN MESI-Cache' . "\n";
$block .= '<IfModule mod_rewrite.c>' . "\n";
$block .= 'RewriteEngine On' . "\n";
$block .= 'RewriteBase ' . $base . "\n\n";
$block .= '# Front page' . "\n";
$block .= 'RewriteCond %{DOCUMENT_ROOT}' . $base . 'cache/' . $cacheprefix . 'index.html -f' . "\n";
$block .= 'RewriteRule ^$ cache/' . $cacheprefix . 'index.html [L]' . "\n\n";
$block .= '# Cached subpages (pretty permalinks)' . "\n";
$block .= 'RewriteCond %{REQUEST_URI} !/wp-admin' . "\n";
$block .= 'RewriteCond %{REQUEST_URI} !/wp-login\.php' . "\n";
$block .= 'RewriteCond %{QUERY_STRING} ^$' . "\n";
$block .= 'RewriteCond %{DOCUMENT_ROOT}' . $base . 'cache%{REQUEST_URI}/index.html -f' . "\n";
$block .= 'RewriteRule ^(.*)$ cache%{REQUEST_URI}/index.html [L]' . "\n\n";
$block .= '# Cached subpages (index.php permalinks)' . "\n";
$block .= 'RewriteCond %{REQUEST_URI} ^' . $base . 'index\.php/(.+)' . "\n";
$block .= 'RewriteCond %{DOCUMENT_ROOT}' . $base . 'cache/%1/index.html -f' . "\n";
$block .= 'RewriteRule ^index\.php/(.+)$ cache/%1/index.html [L]' . "\n";
$block .= '</IfModule>' . "\n";
$block .= '# END MESI-Cache' . "\n";


    // Load existing .htaccess (if any)
    $content = '';
    if ( file_exists( $htaccess_file ) ) {
	$content = file_get_contents( $htaccess_file );
	// Remove old block if present
	$content = preg_replace( '/# BEGIN MESI-Cache.*?# END MESI-Cache/s', '', $content );
    }

    // Append new block
    $new = trim( $content ) . "\n\n" . $block . "\n";

    if ( file_put_contents( $htaccess_file, $new ) === false ) {
	return new WP_Error( 'mesi_cache_htaccess', __( 'Could not write .htaccess file', 'mesi-cache' ) );
    }

    return true;
}

/**
 * Remove MESI-Cache block
 */
function mesi_cache_remove_htaccess_block() {
    $htaccess_file = ABSPATH . '.htaccess';
    if ( ! file_exists( $htaccess_file ) ) {
	return true;
    }

    $content = file_get_contents( $htaccess_file );
    $new     = preg_replace( '/# BEGIN MESI-Cache.*?# END MESI-Cache/s', '', $content );

    if ( file_put_contents( $htaccess_file, trim( $new ) . "\n" ) === false ) {
	return new WP_Error( 'mesi_cache_htaccess', __( 'Could not modify .htaccess', 'mesi-cache' ) );
    }

    return true;
}
