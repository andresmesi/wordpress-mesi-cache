<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Safely create a directory using WP_Filesystem.
 */
function mesi_cache_safe_mkdir( $dir ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;

    if ( $wp_filesystem && ! $wp_filesystem->is_dir( $dir ) ) {
        $wp_filesystem->mkdir( $dir, FS_CHMOD_DIR );
    }

    return $wp_filesystem && $wp_filesystem->is_dir( $dir ) && $wp_filesystem->is_writable( $dir );
}

/**
 * Ensure the main cache directory exists.
 */
function mesi_cache_ensure_dir() {
    return mesi_cache_safe_mkdir( MESI_CACHE_DIR );
}

/**
 * Get the cached file path for a given URL path.
 */
function mesi_cache_file_for_path( $path ) {
    $path = trim( $path, '/' );

    if ( $path === '' ) {
        return MESI_CACHE_DIR . 'index.html';
    }

    $dir = trailingslashit( MESI_CACHE_DIR . $path );
    mesi_cache_safe_mkdir( $dir );

    return $dir . 'index.html';
}

/**
 * Get the file path for the home (front page) cache.
 */
function mesi_cache_file_for_home() {
    return MESI_CACHE_DIR . 'index.html';
}

/**
 * Write a cache file safely using WP_Filesystem.
 */
function mesi_cache_write_file( $file, $html ) {
    if ( ! mesi_cache_ensure_dir() ) {
        return false;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;

    if ( ! $wp_filesystem ) {
        return false;
    }

    $dir = dirname( $file );
    if ( ! $wp_filesystem->is_dir( $dir ) ) {
        $wp_filesystem->mkdir( $dir, FS_CHMOD_DIR );
    }

    // Escribir a archivo temporal
    $tmp = $file . '.tmp';
    if ( ! $wp_filesystem->put_contents( $tmp, $html, FS_CHMOD_FILE ) ) {
        return false;
    }

    // Reemplazo seguro
    if ( $wp_filesystem->exists( $file ) ) {
        $wp_filesystem->delete( $file );
    }

    if ( ! $wp_filesystem->move( $tmp, $file, true ) ) {
        // Fallback si move falla
        $wp_filesystem->copy( $tmp, $file, true, FS_CHMOD_FILE );
        $wp_filesystem->delete( $tmp );
    }

    $wp_filesystem->chmod( $file, FS_CHMOD_FILE );
    clearstatcache( true, $file );

    return $wp_filesystem->exists( $file );
}

/**
 * Delete a cache file safely.
 */
function mesi_cache_delete_file( $file ) {
    if ( file_exists( $file ) ) {
        wp_delete_file( $file );
    }
}

/**
 * Clear all cached files recursively.
 */
function mesi_cache_clear_all() {
    if ( ! is_dir( MESI_CACHE_DIR ) ) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;

    if ( $wp_filesystem && $wp_filesystem->is_dir( MESI_CACHE_DIR ) ) {
        $dirlist = $wp_filesystem->dirlist( MESI_CACHE_DIR, true );
        if ( is_array( $dirlist ) ) {
            foreach ( array_keys( $dirlist ) as $file ) {
                $wp_filesystem->delete( MESI_CACHE_DIR . $file, true );
            }
        }
    }
}

/**
 * Send cache headers for cached pages.
 */
function mesi_cache_send_headers() {
    $options = get_option( MESI_CACHE_OPTION, array() );

    if ( ! empty( $options['add_cache_headers'] ) ) {
        header( 'Cache-Control: public, max-age=86400' );
    }

    header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
}

/**
 * Devuelve una URL directa (funciona con o sin permalinks)
 * para obtener el contenido de un post/página sin 404.
 */
function mesi_cache_resolve_id_url( $post_id ) {
    $type = get_post_type( $post_id );
    $base = home_url( '/' );

    if ( $type === 'page' ) {
        return add_query_arg( 'page_id', $post_id, $base );
    }

    if ( $type === 'post' ) {
        return add_query_arg( 'p', $post_id, $base );
    }

    // custom post type
    return add_query_arg(
        array(
            'p'         => $post_id,
            'post_type' => $type,
        ),
        $base
    );
}
