<?php
/**
 * Таймаут «Покупки частинами» monobank: неподтверждённая заявка → заказ «Отменён».
 *
 * На подтверждение в приложении у клиента 15 минут. Отказ банка плагин CatCode
 * сам переводит в «Отменён», но узнаёт о нём только из колбэка банка или пока
 * экран ожидания опрашивает статус. Если клиент закрыл страницу, а колбэк не
 * дошёл, заказ так и висел бы «В ожидании оплаты». Поэтому при отправке заявки
 * ставим отложенную проверку: через 16 минут (15 у банка + запас) один раз
 * сверяем заявку с банком и, если она так и не одобрена, отменяем заказ.
 *
 * Проверка привязана к конкретной заявке: если клиент после таймаута отправил
 * новую («Попробовать снова»), старая задача её не тронет.
 */

defined( 'ABSPATH' ) || exit;

/** Хук Action Scheduler с проверкой. */
const MOVEAT_INSTALMENTS_TIMEOUT_HOOK = 'moveat_instalments_timeout';

/** Через сколько после заявки проверяем: 15 минут банка + запас на его ответ. */
const MOVEAT_INSTALMENTS_TIMEOUT_DELAY = 16 * MINUTE_IN_SECONDS;

/**
 * Ставит проверку таймаута для только что отправленной заявки.
 */
function moveat_instalments_schedule_timeout( WC_Order $order ) {
	$mono_id = (string) $order->get_meta( '_cciw_mono_order_id' );
	if ( '' === $mono_id || ! function_exists( 'as_schedule_single_action' ) ) {
		return;
	}

	$args = array( (int) $order->get_id(), $mono_id );

	if ( ! as_has_scheduled_action( MOVEAT_INSTALMENTS_TIMEOUT_HOOK, $args ) ) {
		as_schedule_single_action( time() + MOVEAT_INSTALMENTS_TIMEOUT_DELAY, MOVEAT_INSTALMENTS_TIMEOUT_HOOK, $args );
	}
}

/**
 * Последняя сверка заявки с банком; не одобрена — отменяем заказ.
 *
 * @param int    $order_id ID заказа.
 * @param string $mono_id  ID заявки в monobank, для которой ставилась проверка.
 */
function moveat_instalments_timeout( $order_id, $mono_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || MOVEAT_INSTALMENTS_GATEWAY !== $order->get_payment_method() || 'mono' !== $order->get_meta( '_cciw_bank' ) ) {
		return;
	}

	// Клиент уже отправил новую заявку — её срок отсчитывает своя задача.
	if ( (string) $order->get_meta( '_cciw_mono_order_id' ) !== (string) $mono_id ) {
		return;
	}

	// Одобрение или отказ, о которых мы не узнали, применит сам плагин.
	$gateway = moveat_instalments_gateway();
	if ( $gateway && method_exists( $gateway, 'sync_order' ) ) {
		try {
			$gateway->sync_order( $order );
		} catch ( \Throwable $e ) {
			error_log( '[moveat instalments-timeout] sync error: ' . $e->getMessage() );
		}
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || $order->is_paid() || ! $order->has_status( array( 'pending', 'failed' ) ) ) {
		return;
	}

	// Этот текст отдаёт клиенту эндпоинт опроса плагина, если экран ожидания ещё открыт.
	$order->update_meta_data( '_cciw_fail_text', 'Вы не подтвердили заявку в течение 15 минут.' );
	$order->update_status( 'cancelled', 'monobank: заявку не подтвердили за 15 минут — заказ отменён.' );
}
add_action( MOVEAT_INSTALMENTS_TIMEOUT_HOOK, 'moveat_instalments_timeout', 10, 2 );
