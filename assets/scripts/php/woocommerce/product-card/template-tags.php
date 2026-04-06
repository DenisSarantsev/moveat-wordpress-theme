<?php
defined( 'ABSPATH' ) || exit;

/**
 * Breadcrumbs block respecting theme classes or using Woo default as fallback.
 */
function moveat_wc_render_breadcrumbs() {
	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb( [
			'wrap_before' => '<nav aria-label="breadcrumb"><ol class="breadcrumb product-breadcrumbs__list">',
			'wrap_after'  => '</ol></nav>',
			'before'      => '<li class="breadcrumb-item product-breadcrumbs__item">',
			'after'       => '</li>',
			'home'        => _x( 'Главная', 'breadcrumb', 'moveat' ),
		] );
	}
}

/**
 * Custom gallery: main image + thumbnails using Woo template parts.
 */
function moveat_wc_render_gallery() {
	wc_get_template( 'single-product/product-image.php' );
	wc_get_template( 'single-product/product-thumbnails.php' );
	// Lightbox markup could be added here if using custom JS. Keeping minimal for now.
}

/**
 * Summary block: title, short description, audio, formats, price USD/UAH, buttons.
 */
function moveat_wc_render_summary() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	echo '<h2 class="product-page__title">' . esc_html( get_the_title() ) . '</h2>';

	$short_description = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
	if ( $short_description ) {
		echo '<div class="product-page__description">' . wp_kses_post( wpautop( $short_description ) ) . '</div>';
	}

	moveat_wc_render_audio_block();
	moveat_wc_render_formats_block();
	moveat_wc_render_price_and_buttons();
}

/**
 * Formats block using ACF checkbox and formats-map.
 */
function moveat_wc_render_formats_block() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
	$formats = (array) get_field( 'product_formats' );
	if ( empty( $formats ) ) {
		return;
	}
	$map = function_exists( 'moveat_wc_get_formats_map' ) ? moveat_wc_get_formats_map() : [];
	echo '<div class="product-page__formats" aria-label="Форматы получения товара">';
	echo '<div class="product-page__formats-title">' . esc_html__( 'Вы получаете:', 'moveat' ) . '</div>';
	echo '<div class="product-page__formats-wrapper">';
	foreach ( $formats as $format_key ) {
		if ( empty( $map[ $format_key ] ) ) {
			continue;
		}
		$icon   = $map[ $format_key ]['icon'];
		$label  = $map[ $format_key ]['label'];
		echo '<div class="product-page__format-item">';
		echo '<img src="' . esc_url( $icon ) . '" alt="' . esc_attr( $label ) . '">';
		echo '<div>' . esc_html( $label ) . '</div>';
		echo '</div>';
	}
	echo '</div></div>';
}

/**
 * Audio block from ACF file field.
 */
function moveat_wc_render_audio_block() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
	$audio = get_field( 'product_audio' );
	$src   = is_array( $audio ) && ! empty( $audio['url'] ) ? $audio['url'] : ( is_string( $audio ) ? $audio : '' );
	if ( ! $src ) {
		return;
	}
	echo '<div class="product-page__audio-player">';
	echo '<div class="product-page__audio-player-title">' . esc_html__( 'Аудио фрагмент:', 'moveat' ) . '</div>';
	echo '<audio src="' . esc_url( $src ) . '" controls></audio>';
	echo '</div>';
}

/**
 * Price (USD + UAH) and buttons block.
 */
function moveat_wc_render_price_and_buttons() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$usd = wc_get_price_to_display( $product );
	$uah = function_exists( 'moveat_get_uah_price_for_product' ) ? moveat_get_uah_price_for_product( $product ) : '';

	echo '<div class="product-page__price">';
	echo '<span class="product-page__price-currency">$</span>';
	echo '<span class="product-page__price-value">' . esc_html( wc_format_decimal( $usd, wc_get_price_decimals() ) ) . '</span>';
	if ( $uah !== '' ) {
		echo '<span class="product-page__price-secondary">₴' . esc_html( wc_format_decimal( $uah, 0 ) ) . '</span>';
	}
	echo '</div>';

	echo '<div class="product-page__buttons">';
	woocommerce_template_single_add_to_cart();
	echo '</div>';
}

/**
 * Full description block.
 */
function moveat_wc_render_full_description() {
	the_content();
}

/**
 * Related wrapper delegating to our override template.
 */
function moveat_wc_render_related() {
	woocommerce_output_related_products();
}

