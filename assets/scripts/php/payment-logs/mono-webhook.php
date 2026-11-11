<?php
/**
 * Webhook mono (/?wc-api=mono_gateway): журналируем каждое уведомление.
 *
 * Приоритет 20 — после обработчика плагина (10), чтобы статус заказа в журнале
 * был уже обновлённым. php://input можно читать повторно.
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_api_mono_gateway', __NAMESPACE__ . '\\log_mono_webhook', 20 );

function log_mono_webhook() {
	$raw    = (string) file_get_contents( 'php://input' );
	$x_sign = isset( $_SERVER['HTTP_X_SIGN'] ) ? (string) $_SERVER['HTTP_X_SIGN'] : '';

	$gateways = WC()->payment_gateways()->payment_gateways();
	$gateway  = $gateways['mono_gateway'] ?? null;

	// Подпись проверяем методом плагина (публичный ключ mono у него закэширован в файле).
	$verified = false;
	if ( '' !== $raw && '' !== $x_sign && $gateway && is_callable( array( $gateway, 'verify_webhook_signature' ) ) ) {
		try {
			$verified = $gateway->verify_webhook_signature( $raw, $x_sign );
		} catch ( \Throwable $e ) {
			$verified = false;
		}
	}

	if ( ! $verified ) {
		// Данные неподписанного запроса не пишем — только факт.
		log_event( 0, 'mono', 'webhook', array(
			'signature' => 'invalid',
			'ip'        => client_ip(),
		) );
		return;
	}

	$body = json_decode( $raw, true );
	if ( ! is_array( $body ) ) {
		return;
	}

	handle_mono_status( $body, 'webhook' );
}
