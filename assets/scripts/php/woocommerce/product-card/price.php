<?php
defined( 'ABSPATH' ) || exit;

/**
 * Get UAH price for product using plugin rate or fallback option.
 */
function moveat_get_uah_price_for_product( WC_Product $product ) {
	$usd = (float) wc_get_price_to_display( $product );
	$rate = moveat_get_uah_rate();
	if ( ! $rate ) {
		return '';
	}
	return round( $usd * (float) $rate );
}

/**
 * Rate resolver:
 * - Try currency plugins via filters if available
 * - Fallback to theme option `uah_rate` (Options API)
 */
function moveat_get_uah_rate() {
	$rate = 0;

	// Try WOOCS (WooCommerce Currency Switcher)
	if ( has_filter( 'woocs_exchange_value' ) ) {
		// Convert 1 USD to UAH
		$rate = apply_filters( 'woocs_exchange_value', 1, 'UAH', 'USD' );
		if ( $rate ) {
			return (float) $rate;
		}
	}

	// Try CURCY (VillaTheme)
	if ( function_exists( 'wmc_get_price' ) ) {
		$rate = (float) wmc_get_price( 1, 'UAH' );
		if ( $rate ) {
			return $rate;
		}
	}

	// Fallback: options
	$option_rate = get_option( 'moveat_uah_rate' );
	if ( $option_rate ) {
		return (float) $option_rate;
	}
	return 0;
}

