<?php
// require_once __DIR__ . '/acf/options-page.php';
require_once __DIR__ . '/acf/global-fields.php';
require_once __DIR__ . '/articles/post-type.php';
require_once __DIR__ . '/main/menu.php';
require_once __DIR__ . '/main/logo.php';
// WooCommerce modules
if ( class_exists( 'WooCommerce' ) ) {
	require_once __DIR__ . '/woocommerce/product-card/setup.php';
	require_once __DIR__ . '/woocommerce/api/setup.php';
}