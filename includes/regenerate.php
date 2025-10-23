<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate cache for home page.
 */
function mesi_cache_generate_home() {
    if ( ! mesi_cache_ensure_dir() ) {
	return false;
    }

    $response = wp_remote_get(
	home_url( '/' ),
	array(
	    'timeout' => 20,
	    'headers' => array( 'X-MESI-Bypass' => '1' ),
	)
    );

    if ( is_wp_error( $response ) ) {
	return false;
    }

    $body = wp_remote_retrieve_body( $response );
    if ( empty( $body ) ) {
	return false;
    }

    return mesi_cache_write_file( mesi_cache_file_for_home(), $body );
}

/**
 * Generate cache for a single post or page.
 */
function mesi_cache_generate_post( $id ) {
    if ( ! mesi_cache_ensure_dir() ) {
	return false;
    }

    $url = get_permalink( $id );
    if ( ! $url ) {
	return false;
    }

    $response = wp_remote_get(
	$url,
	array(
	    'timeout' => 20,
	    'headers' => array( 'X-MESI-Bypass' => '1' ),
	)
    );

    if ( is_wp_error( $response ) ) {
	return false;
    }

    $body = wp_remote_retrieve_body( $response );
    if ( empty( $body ) ) {
	return false;
    }

    $path = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );
    $file = mesi_cache_file_for_path( $path );

    return mesi_cache_write_file( $file, $body );
}

/**
 * Generate cache for all published posts and pages.
 */
function mesi_cache_generate_all() {
    $posts = get_posts(
	array(
	    'post_type'      => array( 'post', 'page' ),
	    'post_status'    => 'publish',
	    'posts_per_page' => -1,
	)
    );

    $generated = 0;
    $errors    = 0;

    if ( empty( $posts ) ) {
	return array( 'generated' => 0, 'errors' => 0 );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;

    foreach ( $posts as $post ) {
	$url      = get_permalink( $post->ID );
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
	    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Used only in debug mode for developer logging.
		trigger_error(
		    '[MESI-Cache] Failed fetching ' . esc_url( $url ) . ': ' . esc_html( $response->get_error_message() ),
		    E_USER_NOTICE
		);
	    }
	    $errors++;
	    continue;
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
	    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Used only in debug mode for developer logging.
		trigger_error(
		    '[MESI-Cache] Empty body for ' . esc_url( $url ),
		    E_USER_NOTICE
		);
	    }
	    $errors++;
	    continue;
	}

	// Clean /index.php/ prefix if present.
	$path = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( str_starts_with( $path, 'index.php/' ) ) {
	    $path = substr( $path, strlen( 'index.php/' ) );
	}

	// Convert to cache path.
	$file = mesi_cache_file_for_path( $path );

        if ( ! mesi_cache_write_file( $file, $body ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error -- Used only in debug mode for developer logging.
                trigger_error(
                    '[MESI-Cache] Failed writing ' . esc_html( $file ),
                    E_USER_NOTICE
		);
	    }
	    $errors++;
	} else {
	    $generated++;
	}
    }

    // --- Home page ---
    $home_url = home_url( '/' );
    $response = wp_remote_get( $home_url );
    if ( ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        $file = mesi_cache_file_for_home();
        if ( mesi_cache_write_file( $file, $body ) ) {
            $generated++;
        } else {
            $errors++;
        }
    }

    return array(
	'generated' => $generated,
	'errors'    => $errors,
    );
}
