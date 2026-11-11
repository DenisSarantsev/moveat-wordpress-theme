<?php
/**
 * Общая обработка статуса инвойса mono (из webhook и из cron-сверки).
 *
 * Ответ mono содержит failureReason и errCode — плагин код не сохраняет, а
 * причину пишет, только если заказ ещё pending (тема к этому моменту уже ставит
 * failed). Поэтому журналируем сами и добавляем заметку с кодом в заказ.
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

// Уже записанные события инвойсов — чтобы webhook и cron не дублировали друг друга.
const META_MONO_SEEN = '_moveat_mono_seen';
const MONO_SEEN_KEEP = 30;

/**
 * @param array  $body   Тело webhook или ответа /invoice/status.
 * @param string $source webhook | reconcile.
 */
function handle_mono_status( array $body, $source ) {
	$status     = (string) ( $body['status'] ?? '' );
	$invoice_id = (string) ( $body['invoiceId'] ?? '' );
	$order      = find_mono_order( $body );
	$order_id   = $order ? $order->get_id() : (int) ( $body['reference'] ?? 0 );

	// created/processing из cron не пишем — сверка дёргает их по расписанию.
	// Из webhook пишем: processing показывает, что клиент начал попытку.
	$is_final = in_array( $status, array( 'success', 'failure', 'expired', 'reversed', 'hold' ), true );
	if ( 'reconcile' === $source && ! $is_final ) {
		return;
	}

	if ( $order && $is_final && ! mark_seen( $order, implode( '|', array( $invoice_id, $status, $body['errCode'] ?? '', $body['modifiedDate'] ?? '' ) ) ) ) {
		return; // это событие уже записано из другого источника
	}

	$payment_info = isset( $body['paymentInfo'] ) && is_array( $body['paymentInfo'] ) ? $body['paymentInfo'] : array();

	log_event( $order_id, 'mono', $source, array(
		'status'        => $status,
		'errCode'       => $body['errCode'] ?? '',
		'reason'        => $body['failureReason'] ?? '',
		'invoice'       => $invoice_id,
		'amount'        => isset( $body['amount'] ) ? $body['amount'] / 100 : '',
		'method'        => $payment_info['paymentMethod'] ?? '',
		'system'        => $payment_info['paymentSystem'] ?? '',
		'bank'          => $payment_info['bank'] ?? '',
		'country'       => $payment_info['country'] ?? '',
		'modified'      => $body['modifiedDate'] ?? '',
		'attempt'       => $order ? get_attempts( $order ) : '',
		'order_status'  => $order ? $order->get_status() : '',
	) );

	if ( $order && in_array( $status, array( 'failure', 'expired' ), true ) ) {
		$code   = isset( $body['errCode'] ) && '' !== (string) $body['errCode'] ? ', код ' . $body['errCode'] : '';
		$reason = (string) ( $body['failureReason'] ?? '' );

		$order->add_order_note( sprintf(
			'%s mono — %s (инвойс %s%s)%s',
			NOTE_PREFIX,
			'expired' === $status ? 'инвойс истёк' : 'оплата не прошла',
			$invoice_id,
			$code,
			'' !== $reason ? ': ' . $reason : ''
		) );
	}
}

/**
 * Заказ по reference (= номер заказа), запасной путь — по transaction_id (= invoiceId).
 */
function find_mono_order( array $body ) {
	$order_id = (int) ( $body['reference'] ?? 0 );
	$order    = $order_id ? wc_get_order( $order_id ) : false;

	if ( ! $order && ! empty( $body['invoiceId'] ) ) {
		$orders = wc_get_orders( array(
			'transaction_id' => (string) $body['invoiceId'],
			'limit'          => 1,
		) );
		$order  = $orders ? reset( $orders ) : false;
	}

	return $order instanceof \WC_Order ? $order : null;
}

/**
 * Отмечает событие как записанное. false — если уже было.
 */
function mark_seen( \WC_Order $order, $key ) {
	$seen = $order->get_meta( META_MONO_SEEN );
	$seen = is_array( $seen ) ? $seen : array();

	if ( in_array( $key, $seen, true ) ) {
		return false;
	}

	$seen[] = $key;
	$order->update_meta_data( META_MONO_SEEN, array_slice( $seen, -MONO_SEEN_KEEP ) );
	$order->save_meta_data();

	return true;
}
