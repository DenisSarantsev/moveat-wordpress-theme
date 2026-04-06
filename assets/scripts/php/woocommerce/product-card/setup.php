<?php
defined( 'ABSPATH' ) || exit;

// Ensure WooCommerce support.
add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
} );

// Enqueue product-only assets
add_action( 'wp_enqueue_scripts', function () {
	if ( is_product() ) {
		wp_enqueue_script(
			'moveat-product-page',
			get_template_directory_uri() . '/assets/scripts/js/modules/product-page.js',
			[ 'jquery' ],
			null,
			true
		);
		// Pass theme URI to JS for asset paths (e.g., close icon in lightbox)
		wp_localize_script(
			'moveat-product-page',
			'MOVEAT_THEME',
			[
				'themeUri' => get_template_directory_uri(),
			]
		);
	}
}, 20 );

// Adjust WooCommerce hooks to use our template-tags rendering
add_action( 'init', function () {
	if ( ! function_exists( 'is_product' ) || ! is_woocommerce() ) {
		return;
	}
	// We render gallery/summary ourselves in content-single-product.php via helpers.
	// Optionally remove default single product summary parts if they appear duplicated.
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}, 15 );

require_once __DIR__ . '/template-tags.php';
require_once __DIR__ . '/price.php';
require_once __DIR__ . '/formats-map.php';

// Route single Product to templates/product.php without extra template file
add_filter( 'template_include', function ( $template ) {
	if ( function_exists('is_singular') && is_singular( 'product' ) ) {
		$custom = get_template_directory() . '/templates/product.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}, 50 );
