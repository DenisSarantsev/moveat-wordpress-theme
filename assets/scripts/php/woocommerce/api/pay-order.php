<?php

namespace Moveat\Woo\Api;

defined('ABSPATH') || exit;

/**
 * Сколько секунд переиспользуем уже созданный инвойс mono вместо создания нового.
 * Должно укладываться в срок жизни инвойса (validity, по умолчанию у mono ~24 ч).
 * Берём консервативно — перекос в сторону «не списать дважды».
 */
const REUSE_TTL = 20 * HOUR_IN_SECONDS;

// Мета-ключи для идемпотентности оплаты.
const META_PAY_URL     = '_moveat_pay_url';
const META_PAY_URL_AT  = '_moveat_pay_url_at';
const META_PAY_INVOICE = '_moveat_pay_invoice';

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
 * Возвращает сохранённую ссылку на оплату, если она ещё «свежая»
 * (инвойс с большой вероятностью жив). Иначе null.
 */
function get_reusable_pay_url($order)
{
	$url = $order->get_meta(META_PAY_URL);
	$at  = (int) $order->get_meta(META_PAY_URL_AT);

	if (empty($url) || $at <= 0) {
		return null;
	}

	if ((time() - $at) > REUSE_TTL) {
		return null; // инвойс, скорее всего, протух — создадим новый
	}

	return $url;
}

/**
 * Сохраняет данные созданного инвойса на заказ для последующего переиспользования.
 */
function store_pay_url($order_id, $payment_url)
{
	$order = wc_get_order($order_id);
	if (!$order) {
		return;
	}
	$order->update_meta_data(META_PAY_URL, $payment_url);
	$order->update_meta_data(META_PAY_URL_AT, time());
	$order->update_meta_data(META_PAY_INVOICE, $order->get_transaction_id());
	$order->save();
}

/**
 * Pay existing WooCommerce order (NO duplicate order creation, NO duplicate invoice)
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
	// 1.5. Нулевая сумма → шлюз не нужен
	// -----------------------------
	// Проверяем до выбора метода оплаты: у бесплатного заказа свой метод (moveat_free),
	// перезаписывать его пришедшим с фронта mono/paypal незачем.
	if (function_exists('moveat_order_is_free') && moveat_order_is_free($order)) {
		moveat_complete_free_order($order);

		return new \WP_REST_Response([
			'order_id'    => $order_id,
			'payment_url' => moveat_get_thankyou_page_url($order),
			'is_free'     => true,
		], 200);
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
	// 3. Идемпотентность: уже оплачен → не создаём инвойс
	// -----------------------------
	if ($order->is_paid() || $order->has_status(['processing', 'completed'])) {
		return new \WP_REST_Response([
			'order_id'     => $order_id,
			'payment_url'  => $order->get_checkout_order_received_url(),
			'already_paid' => true,
		], 200);
	}

	// -----------------------------
	// 4. Идемпотентность: есть свежий инвойс → переиспользуем ссылку
	// -----------------------------
	$reuse_url = get_reusable_pay_url($order);
	if ($reuse_url) {
		return new \WP_REST_Response([
			'order_id'    => $order_id,
			'payment_url' => $reuse_url,
			'reused'      => true,
		], 200);
	}

	// -----------------------------
	// 5. Лок против параллельных вызовов (double-submit / ретрай сети)
	// -----------------------------
	$lock_key = 'moveat_pay_lock_' . $order_id;

	if (false !== get_transient($lock_key)) {
		// Параллельный запрос уже создаёт инвойс — подождём его результат
		// (до ~3 секунд) и переиспользуем ту же ссылку.
		for ($i = 0; $i < 6; $i++) {
			usleep(500000); // 0.5 c
			$fresh = wc_get_order($order_id);
			if ($fresh) {
				$reuse_url = get_reusable_pay_url($fresh);
				if ($reuse_url) {
					return new \WP_REST_Response([
						'order_id'    => $order_id,
						'payment_url' => $reuse_url,
						'reused'      => true,
					], 200);
				}
			}
		}
		// Не дождались — безопасный фолбэк на стандартную страницу оплаты Woo.
		return new \WP_REST_Response([
			'order_id'    => $order_id,
			'payment_url' => $order->get_checkout_payment_url(),
			'locked'      => true,
		], 200);
	}

	set_transient($lock_key, time(), 30);

	try {
		// -----------------------------
		// 6. Init Woo payment system safely
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
		// 7. Gateway payment redirect (создаёт новый инвойс)
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
		// 8. Fallback URL
		// -----------------------------
		if (empty($payment_url)) {
			try {
				$payment_url = $order->get_checkout_payment_url();
			} catch (\Throwable $e) {
				$payment_url = '';
			}
		}

		// Сохраняем ссылку/инвойс для идемпотентности будущих вызовов.
		if (!empty($payment_url)) {
			store_pay_url($order_id, $payment_url);
		}
	} finally {
		delete_transient($lock_key);
	}

	// -----------------------------
	// 9. Response
	// -----------------------------
	return new \WP_REST_Response([
		'order_id'    => $order_id,
		'payment_url' => $payment_url,
	], 200);
}
