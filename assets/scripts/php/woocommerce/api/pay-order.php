<?php

namespace Moveat\Woo\Api;

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
	register_rest_route(
		'my-api/v1',
		'/pay-order/(?P<id>\d+)',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\\pay_order',
			'permission_callback' => '__return_true',
		]
	);
});

/**
 * Pay existing WooCommerce order (NO duplicate order creation)
 */
function pay_order(\WP_REST_Request $request)
{
	$order_id = (int) $request->get_param('id');
	$body     = $request->get_json_params() ?? [];

	if (!$order_id) {
		return new \WP_REST_Response([
			'error' => 'Order ID missing',
		], 400);
	}

	$order = wc_get_order($order_id);

	if (!$order) {
		return new \WP_REST_Response([
			'error' => 'Order not found',
		], 404);
	}

	// -----------------------------
	// 1. Billing / Shipping update
	// -----------------------------
	if (!empty($body['billing'])) {
		try {
			$order->set_address((array)$body['billing'], 'billing');
		} catch (\Throwable $e) {
			error_log('[moveat pay-order] billing error: ' . $e->getMessage());
		}
	}

	if (!empty($body['shipping'])) {
		try {
			$order->set_address((array)$body['shipping'], 'shipping');
		} catch (\Throwable $e) {
			error_log('[moveat pay-order] shipping error: ' . $e->getMessage());
		}
	}

	// -----------------------------
	// 2. Payment method
	// -----------------------------
	$payment_method = $body['payment_method'] ?? $order->get_payment_method();

	if ($payment_method) {
		try {
			$order->set_payment_method($payment_method);
			$order->set_payment_method_title($payment_method);
		} catch (\Throwable $e) {
			error_log('[moveat pay-order] payment method error: ' . $e->getMessage());
		}
	}

	$order->save();

	// -----------------------------
	// 3. Init Woo payment system safely
	// -----------------------------
	if (function_exists('WC')) {
		if (empty(WC()->session) && class_exists('\WC_Session_Handler')) {
			try {
				WC()->session = new \WC_Session_Handler();
				WC()->session->init();
			} catch (\Throwable $e) {
				error_log('[moveat pay-order] session init error: ' . $e->getMessage());
			}
		}
	}

	// -----------------------------
	// 4. Gateway payment redirect
	// -----------------------------
	$payment_url = null;

	if ($payment_method && class_exists('\WC_Payment_Gateways')) {
		try {
			$gateways = WC()->payment_gateways()->payment_gateways();

			if (isset($gateways[$payment_method])) {
				$gateway = $gateways[$payment_method];

				if (is_callable([$gateway, 'process_payment'])) {
					$result = $gateway->process_payment($order_id);

					error_log('[moveat pay-order] gateway result: ' . wp_json_encode($result));

					if (is_array($result)) {
						$payment_url =
							$result['redirect']
							?? $result['payment_url']
							?? ($result['payment_result']['redirect_url'] ?? null);
					}
				}
			}
		} catch (\Throwable $e) {
			error_log('[moveat pay-order] gateway error: ' . $e->getMessage());
		}
	}

	// -----------------------------
	// 5. Fallback URL
	// -----------------------------
	if (empty($payment_url)) {
		try {
			$payment_url = $order->get_checkout_payment_url();
		} catch (\Throwable $e) {
			$payment_url = '';
		}
	}

	// -----------------------------
	// 6. Response
	// -----------------------------
	return new \WP_REST_Response([
		'order_id'    => $order_id,
		'payment_url' => $payment_url,
	], 200);
}