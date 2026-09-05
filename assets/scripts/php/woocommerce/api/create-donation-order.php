<?php

namespace Moveat\Woo\Api;

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	register_rest_route(
		'my-api/v1',
		'/create-donation-order',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\\moveat_create_donation_order',
			'permission_callback' => '__return_true',
		]
	);
} );

/*
	Создаёт заказ доната с позицией WC_Order_Item_Fee.
*/
function moveat_create_donation_order( \WP_REST_Request $request ) {
	if ( ! function_exists( 'wc_create_order' ) ) {
		return new \WP_REST_Response(
			[ 'error' => 'WooCommerce is not available' ],
			500
		);
	}

	$body            = $request->get_json_params() ?? [];
	$amount          = isset( $body['amount'] ) ? (float) $body['amount'] : 0;
	$email           = sanitize_email( (string) ( $body['email'] ?? '' ) );
	$first_name      = trim( sanitize_text_field( (string) ( $body['first_name'] ?? '' ) ) );
	$last_name_raw   = trim( sanitize_text_field( (string) ( $body['last_name'] ?? '' ) ) );
	// В скобках: WooCommerce склеивает имя и фамилию в одну строку,
	// иначе в админке получалось «Иван Фамилия не указана».
	$last_name       = $last_name_raw !== '' ? $last_name_raw : __( '(фамилия не указана)', 'moveat' );
	$phone           = trim( sanitize_text_field( (string) ( $body['phone'] ?? '' ) ) );
	$payment_method  = sanitize_text_field( (string) ( $body['payment_method'] ?? '' ) );
	$allowed_methods = [ 'ppcp-gateway', 'mono_gateway' ];

	$min_amount = moveat_get_min_donation_amount();

	if ( $amount < $min_amount ) {
		return new \WP_REST_Response(
			[
				'error'      => 'Minimum donation amount not met',
				'min_amount' => $min_amount,
			],
			400
		);
	}

	if ( ! is_email( $email ) ) {
		return new \WP_REST_Response(
			[ 'error' => 'Invalid email address' ],
			400
		);
	}

	if ( mb_strlen( $first_name ) < 2 ) {
		return new \WP_REST_Response(
			[ 'error' => 'Invalid first name' ],
			400
		);
	}

	if ( $phone === '' ) {
		return new \WP_REST_Response(
			[ 'error' => 'Invalid phone number' ],
			400
		);
	}

	if ( ! in_array( $payment_method, $allowed_methods, true ) ) {
		return new \WP_REST_Response(
			[ 'error' => 'Invalid payment method' ],
			400
		);
	}

	try {
		$order = wc_create_order();

		if ( ! $order ) {
			return new \WP_REST_Response(
				[ 'error' => 'Failed to create order' ],
				500
			);
		}

		$fee = new \WC_Order_Item_Fee();
		$fee->set_name( __( 'Донат', 'moveat' ) );
		$fee->set_amount( $amount );
		$fee->set_total( $amount );
		$fee->set_tax_status( 'none' );
		$order->add_item( $fee );

		$order->set_billing_email( $email );
		$order->set_billing_first_name( $first_name );
		$order->set_billing_last_name( $last_name );
		$order->set_billing_phone( $phone );
		$order->set_billing_address_1( __( 'Адрес не указан', 'moveat' ) );
		$order->set_billing_city( __( 'Город не указан', 'moveat' ) );
		$order->set_billing_postcode( '00000' );
		$order->set_billing_country( 'UA' );
		$order->set_billing_state( 'UA30' );

		$order->set_shipping_first_name( $first_name );
		$order->set_shipping_last_name( $last_name );
		$order->set_shipping_address_1( __( 'Адрес не указан', 'moveat' ) );
		$order->set_shipping_city( __( 'Город не указан', 'moveat' ) );
		$order->set_shipping_postcode( '00000' );
		$order->set_shipping_country( 'UA' );
		$order->set_shipping_state( 'UA30' );

		$order->set_payment_method( $payment_method );
		$order->set_status( 'pending' );
		$order->set_created_via( 'donation' );
		$order->update_meta_data( '_is_donation', 'yes' );
		$order->calculate_totals();
		$order->save();

		return new \WP_REST_Response(
			[
				'id'        => $order->get_id(),
				'order_key' => $order->get_order_key(),
			],
			200
		);
	} catch ( \Throwable $e ) {
		error_log( '[moveat create-donation-order] ' . $e->getMessage() );

		return new \WP_REST_Response(
			[ 'error' => 'Failed to create donation order' ],
			500
		);
	}
}
