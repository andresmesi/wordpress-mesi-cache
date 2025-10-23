<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ==========================================================
 * MESI-Cache — htaccess-manager.php
 * ----------------------------------------------------------
 * Inserta o actualiza el bloque de MESI-Cache y garantiza
 * que el bloque de WordPress exista y esté en el orden correcto.
 * ==========================================================
 */

function mesi_cache_insert_htaccess_block() {
    $htaccess_file = ABSPATH . '.htaccess';

    // Detectar subcarpeta (e.g. /wp/, /blog/, etc.)
    $subpath = trim( wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
    $base    = $subpath ? "/$subpath/" : '/';
    $cacheprefix = $subpath ? "$subpath/" : '';

    // ==============================
    // MESI-CACHE BLOCK
    // ==============================
    $mesi_block  = "# BEGIN MESI-Cache\n";
    $mesi_block .= "<IfModule mod_rewrite.c>\n";
    $mesi_block .= "RewriteEngine On\n";
    $mesi_block .= "RewriteBase {$base}\n\n";

    $mesi_block .= "# --- EXCLUSIONES ---\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} ^{$base}wp-json/ [OR]\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} ^{$base}wp-admin/admin-ajax\\.php$ [OR]\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} ^{$base}wp-admin [OR]\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} ^{$base}wp-login\\.php [OR]\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} /feed/ [OR]\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} preview=true [OR]\n";
    $mesi_block .= "RewriteCond %{QUERY_STRING} .+\n";
    $mesi_block .= "RewriteRule .* - [S=4]\n\n";

    $mesi_block .= "# --- Caché de portada ---\n";
    $mesi_block .= "RewriteCond %{DOCUMENT_ROOT}{$base}cache/{$cacheprefix}index.html -f\n";
    $mesi_block .= "RewriteRule ^$ cache/{$cacheprefix}index.html [L]\n\n";

    $mesi_block .= "# --- Caché de subpáginas (permalinks bonitos) ---\n";
    $mesi_block .= "RewriteCond %{DOCUMENT_ROOT}{$base}cache%{REQUEST_URI}/index.html -f\n";
    $mesi_block .= "RewriteRule ^(.*)$ cache%{REQUEST_URI}/index.html [L]\n\n";

    $mesi_block .= "# --- Caché de permalinks tipo index.php/slug ---\n";
    $mesi_block .= "RewriteCond %{REQUEST_URI} ^{$base}index\\.php/(.+)\n";
    $mesi_block .= "RewriteCond %{DOCUMENT_ROOT}{$base}cache/%1/index.html -f\n";
    $mesi_block .= "RewriteRule ^index\\.php/(.+)$ cache/%1/index.html [L]\n";
    $mesi_block .= "</IfModule>\n";
    $mesi_block .= "# END MESI-Cache\n";

    // ==============================
    // WORDPRESS FALLBACK BLOCK
    // ==============================
    $wp_block  = "# BEGIN WordPress\n";
    $wp_block .= "<IfModule mod_rewrite.c>\n";
    $wp_block .= "RewriteEngine On\n";
    $wp_block .= "RewriteBase {$base}\n";
    $wp_block .= "RewriteRule ^index\\.php$ - [L]\n";
    $wp_block .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $wp_block .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
    $wp_block .= "RewriteRule . {$base}index.php [L]\n";
    $wp_block .= "</IfModule>\n";
    $wp_block .= "# END WordPress\n";

    // ==============================
    // MERGE & WRITE
    // ==============================
    $content = '';
    if ( file_exists( $htaccess_file ) ) {
        $content = file_get_contents( $htaccess_file );
        // Limpiar bloques antiguos
        $content = preg_replace( '/# BEGIN MESI-Cache.*?# END MESI-Cache/s', '', $content );
        $content = preg_replace( '/# BEGIN WordPress.*?# END WordPress/s', '', $content );
    }

    // Insertar bloques en orden correcto
    $new_content = trim( $content ) . "\n\n" . $mesi_block . "\n\n" . $wp_block . "\n";

    if ( file_put_contents( $htaccess_file, $new_content ) === false ) {
        return new WP_Error(
            'mesi_cache_htaccess',
            __( 'Could not write .htaccess file', 'mesi-cache' )
        );
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
