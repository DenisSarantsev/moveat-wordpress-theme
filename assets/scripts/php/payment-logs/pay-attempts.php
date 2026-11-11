<?php
/**
 * Попытки оплаты из нашего API pay-order (каждое нажатие «Оплатить»).
 *
 * pay-order.php вызывает do_action( 'moveat_payment_attempt', $order, $payment_method, $context ),
 * где $context['event']:
 * - already_paid — заказ уже оплачен, шлюз не вызывался;
 * - reused       — отдали сохранённую ссылку на живой инвойс;
 * - gateway      — шлюз создал платёж, $context['result'] — ответ process_payment();
 * - error        — исключение шлюза, $context['error'] — текст.
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

add_action( 'moveat_payment_attempt', __NAMESPACE__ . '\\log_payment_attempt', 10, 3 );

function log_payment_attempt( $order, $payment_method, $context ) {
	if ( ! $order instanceof \WC_Order ) {
		return;
	}

	$event = (string) ( $context['event'] ?? '' );

	$data = array(
		'step'    => $event,
		'attempt' => 'already_paid' === $event ? get_attempts( $order ) : increment_attempts( $order ),
		'status'  => $order->get_status(),
		'total'   => $order->get_total() . ' ' . $order->get_currency(),
	);

	if ( 'gateway' === $event ) {
		$result = $context['result'] ?? null;

		$data['result'] = is_array( $result ) ? ( $result['result'] ?? '' ) : ( false === $result ? 'false' : 'empty' );
		// Ссылку на оплату не пишем целиком — достаточно, что она есть.
		$data['redirect'] = is_array( $result ) && ! empty( $result['redirect'] );
		$data['invoice']  = $order->get_transaction_id();
		$data['error']    = is_array( $result ) ? ( $result['errorMessage'] ?? '' ) : '';

		// Причину отказа шлюзы кладут в notices WooCommerce (mono: «Request error (…)»).
		if ( '' === $data['error'] ) {
			$data['error'] = first_error_notice();
		}
	}

	if ( 'error' === $event ) {
		$data['error'] = $context['error'] ?? '';
	}

	$data['ip'] = client_ip();

	log_event( $order->get_id(), gateway_label( $payment_method ), 'attempt', $data );
}

/**
 * Первый текст ошибки из notices WooCommerce (без очистки — их читает pay-order).
 */
function first_error_notice() {
	if ( ! function_exists( 'wc_get_notices' ) || ! function_exists( 'WC' ) || empty( WC()->session ) ) {
		return '';
	}

	$notices = wc_get_notices( 'error' );
	if ( empty( $notices ) ) {
		return '';
	}

	$first = reset( $notices );

	return is_array( $first ) ? (string) ( $first['notice'] ?? '' ) : (string) $first;
}
