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

	// Передаём данные заказа на страницу /order-pay/ для использования в JS
	$order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( $_GET['order_key'] ) : '';

	if ( $order_id && $order_key ) {
		$order = wc_get_order( $order_id );
		if ( $order && $order->key_is_valid( $order_key ) ) {
			wp_localize_script(
				'moveat-main',
				'MOVEAT_ORDER_DATA',
				[
					'billing' => [
						'first_name' => $order->get_billing_first_name(),
						'last_name'  => $order->get_billing_last_name(),
						'email'      => $order->get_billing_email(),
						'phone'      => $order->get_billing_phone(),
						'address_1'  => $order->get_billing_address_1() ?: '—',
						'city'       => $order->get_billing_city() ?: '—',
						'postcode'   => $order->get_billing_postcode() ?: '00000',
						'country'    => $order->get_billing_country() ?: 'UA',
					],
				]
			);
		}
	}
}, 30 );

