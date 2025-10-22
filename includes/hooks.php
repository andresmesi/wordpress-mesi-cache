<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'init',
    function() {
	if ( is_admin() ) {
	    return;
	}

	// Sanitize server method safely.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No form data is being processed here.
	$method = isset( $_SERVER['REQUEST_METHOD'] )
	    ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
	    : 'GET';

	if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
	    return;
	}

	// Sanitize request URI safely.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No form data is being processed here.
	$request_uri = isset( $_SERVER['REQUEST_URI'] )
	    ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
	    : '/';

	// Skip previews, feeds, and dynamic queries.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking presence of query vars, not processing user input.
	if ( strpos( $request_uri, 'preview=true' ) !== false
	    || strpos( $request_uri, '/feed/' ) !== false
	    || ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $_GET checked only for emptiness.
	    return;
	}

	$options = get_option( MESI_CACHE_OPTION, array() );
	if ( ! empty( $options['bypass_logged_in'] ) && is_user_logged_in() ) {
	    return;
	}

	$path = strtok( $request_uri, '?' );

	// === Front page ===.
	if ( $path === '' || $path === '/' || $path === '/index.php' ) {
	    $file = mesi_cache_file_for_home();

	    if ( file_exists( $file ) ) {
		header( 'X-MESI-Cache: HIT front' );
		mesi_cache_send_headers();

		// Use WP_Filesystem instead of readfile().
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( $wp_filesystem && $wp_filesystem->exists( $file ) ) {
		    echo $wp_filesystem->get_contents( $file ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		    exit;
		}
	    }
	    return;
	}

	// === Subpages ===.
	$home_path = rtrim( wp_parse_url( home_url(), PHP_URL_PATH ) ?? '', '/' );
	$relative  = ( $home_path && strpos( $path, $home_path . '/' ) === 0 )
	    ? substr( $path, strlen( $home_path ) + 1 )
	    : ltrim( $path, '/' );

	$file = mesi_cache_file_for_path( $relative );

	if ( file_exists( $file ) ) {
	    header( 'X-MESI-Cache: HIT' );
	    mesi_cache_send_headers();

	    require_once ABSPATH . 'wp-admin/includes/file.php';
	    WP_Filesystem();
	    global $wp_filesystem;

	    if ( $wp_filesystem && $wp_filesystem->exists( $file ) ) {
		echo $wp_filesystem->get_contents( $file ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	    }
	}
    },
    0
);
