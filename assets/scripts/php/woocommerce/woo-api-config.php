<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_localize_script(
		'moveat-main',
		'MOVEAT_WOO_API_CONFIG',
		[
			'baseUrl'       => untrailingslashit( home_url( '/' ) ),
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'storeApiNonce' => wp_create_nonce( 'wc_store_api' ),
		]
	);
}, 30 );

