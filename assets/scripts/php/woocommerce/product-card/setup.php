<?php
defined( 'ABSPATH' ) || exit;

// Поддержка WooCommerce для темы.
add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce' );
} );

// Подключение скриптов только на странице товара.
add_action( 'wp_enqueue_scripts', function () {
	if ( is_product() ) {
		wp_enqueue_script(
			'moveat-product-page',
			get_template_directory_uri() . '/assets/scripts/js/modules/product-page.js',
			[ 'jquery' ],
			null,
			true
		);
		// Передаём URI темы в JS для путей к ресурсам (напр. иконка закрытия в лайтбоксе).
		wp_localize_script(
			'moveat-product-page',
			'MOVEAT_THEME',
			[
				'themeUri' => get_template_directory_uri(),
			]
		);
	}
}, 20 );

// Настройка хуков WooCommerce под наш вывод через хелперы template-tags.
add_action( 'init', function () {
	if ( ! function_exists( 'is_product' ) || ! is_woocommerce() ) {
		return;
	}
	// Галерею и зону сведений о товаре выводим сами в content-single-product.php через хелперы.
	// Убираем стандартные части карточки товара, если они дублируют нашу вёрстку.
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
}, 15 );

require_once __DIR__ . '/template-tags.php';
require_once __DIR__ . '/price.php';
require_once __DIR__ . '/formats-map.php';

// Страница одиночного товара → templates/product.php (без отдельного корневого файла шаблона).
add_filter( 'template_include', function ( $template ) {
	if ( function_exists( 'is_singular' ) && is_singular( 'product' ) ) {
		$custom = get_template_directory() . '/templates/product.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}, 50 );

// Архив категории товаров (ранее taxonomy-product_cat.php в корне темы).
add_filter( 'template_include', function ( $template ) {
	if ( function_exists( 'is_tax' ) && is_tax( 'product_cat' ) ) {
		$custom = dirname( __DIR__ ) . '/taxonomy-product-cat.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}, 99 );
