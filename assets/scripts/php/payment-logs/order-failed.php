<?php
/**
 * Неудачные оплаты по статусу заказа и заметкам плагинов.
 *
 * - Любой шлюз: переход в failed + последняя заметка (там причина от плагина,
 *   у PayPal — «Processor response: …» с кодом процессора).
 * - PayPal: заметки до оплаты. Первые отказы карты (INSTRUMENT_DECLINED)
 *   плагин пишет только заметкой, не меняя статус — иначе их не увидеть.
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_order_status_failed', __NAMESPACE__ . '\\log_order_failed', 20, 2 );
add_action( 'woocommerce_order_note_added', __NAMESPACE__ . '\\log_paypal_note', 10, 2 );

function log_order_failed( $order_id, $order = null ) {
	$order = $order instanceof \WC_Order ? $order : wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	log_event( $order->get_id(), gateway_label( $order->get_payment_method() ), 'failed', array(
		'note'    => last_order_note( $order->get_id() ),
		'attempt' => get_attempts( $order ),
		'invoice' => $order->get_transaction_id(),
	) );
}

function log_paypal_note( $comment_id, $order ) {
	if ( ! $order instanceof \WC_Order || 'paypal' !== gateway_label( $order->get_payment_method() ) ) {
		return;
	}

	// После оплаты заметки (письма, доставка) к попыткам отношения не имеют.
	if ( $order->is_paid() ) {
		return;
	}

	$comment = get_comment( $comment_id );
	$text    = $comment ? (string) $comment->comment_content : '';

	if ( '' === $text || 0 === strpos( $text, NOTE_PREFIX ) ) {
		return;
	}

	log_event( $order->get_id(), 'paypal', 'note', array(
		'text'    => $text,
		'status'  => $order->get_status(),
		'attempt' => get_attempts( $order ),
	) );
}

/**
 * Текст самой свежей заметки заказа (по ID — у заметок одной секунды дата совпадает).
 */
function last_order_note( $order_id ) {
	$notes = wc_get_order_notes( array(
		'order_id' => $order_id,
		'limit'    => 5,
	) );

	if ( empty( $notes ) ) {
		return '';
	}

	usort( $notes, function ( $a, $b ) {
		return $b->id - $a->id;
	} );

	return (string) $notes[0]->content;
}
