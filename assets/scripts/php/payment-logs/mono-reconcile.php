<?php
/**
 * Cron-сверка mono (woocommerce/mono-status-reconcile.php) отдаёт сюда полный
 * ответ /invoice/status — сама она использует только status.
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

add_action( 'moveat_mono_status_checked', __NAMESPACE__ . '\\log_mono_reconcile', 10, 2 );

function log_mono_reconcile( $invoice_id, $body ) {
	if ( ! is_array( $body ) ) {
		return;
	}

	if ( empty( $body['invoiceId'] ) ) {
		$body['invoiceId'] = $invoice_id;
	}

	handle_mono_status( $body, 'reconcile' );
}
