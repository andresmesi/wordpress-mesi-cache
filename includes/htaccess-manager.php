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

    // Detect subpath from home_url() (e.g. /wp, /blog/, or empty).
    $home_path = wp_parse_url( home_url(), PHP_URL_PATH );
    $subpath   = trim( $home_path ?? '', '/' );

    $rewrite_base = $subpath ? '/' . $subpath . '/' : '/';
    $docroot_base = $subpath ? '/' . $subpath : '';
    $request_root = $subpath ? '/' . $subpath : '';

    // Build the rewrite block (supports all permalink modes).
    $block  = '# BEGIN MESI-Cache' . "\n";
    $block .= '<IfModule mod_rewrite.c>' . "\n";
    $block .= 'RewriteEngine On' . "\n";
    $block .= 'RewriteBase ' . $rewrite_base . "\n\n";
    $block .= '# Skip existing files and directories' . "\n";
    $block .= 'RewriteCond %{REQUEST_FILENAME} -f [OR]' . "\n";
    $block .= 'RewriteCond %{REQUEST_FILENAME} -d' . "\n";
    $block .= 'RewriteRule ^ - [L]' . "\n\n";
    $block .= '# Front page (/, /index.php, with or without subdirectory)' . "\n";
    $block .= 'RewriteCond %{DOCUMENT_ROOT}' . $docroot_base . '/cache/index.html -f' . "\n";
    $block .= 'RewriteRule ^(?:index\\.php)?$ cache/index.html [L]' . "\n\n";
    $block .= '# Cached subpages (pretty permalinks, supports custom structures)' . "\n";
    $block .= 'RewriteCond %{REQUEST_URI} !/wp-admin' . "\n";
    $block .= 'RewriteCond %{REQUEST_URI} !/wp-login\\.php' . "\n";
    $block .= 'RewriteCond %{REQUEST_URI} ^' . $request_root . '/(.+?)/?$' . "\n";
    $block .= 'RewriteCond %{QUERY_STRING} ^$' . "\n";
    $block .= 'RewriteCond %{DOCUMENT_ROOT}' . $docroot_base . '/cache/%1/index.html -f' . "\n";
    $block .= 'RewriteRule ^(.+?)/?$ cache/$1/index.html [L]' . "\n\n";
    $block .= '# Cached subpages (index.php prefix permalinks)' . "\n";
    $block .= 'RewriteCond %{REQUEST_URI} ^' . $request_root . '/index\\.php/(.+?)/?$' . "\n";
    $block .= 'RewriteCond %{QUERY_STRING} ^$' . "\n";
    $block .= 'RewriteCond %{DOCUMENT_ROOT}' . $docroot_base . '/cache/%1/index.html -f' . "\n";
    $block .= 'RewriteRule ^index\\.php/(.+?)/?$ cache/$1/index.html [L]' . "\n";
    $block .= '</IfModule>' . "\n";
    $block .= '# END MESI-Cache' . "\n";

    // Load existing .htaccess (if any).
    $content = '';
    if ( file_exists( $htaccess_file ) ) {
        $content = file_get_contents( $htaccess_file );
        // Remove old block if present.
        $content = preg_replace( '/# BEGIN MESI-Cache.*?# END MESI-Cache/s', '', $content );
    }

    // Append new block.
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
