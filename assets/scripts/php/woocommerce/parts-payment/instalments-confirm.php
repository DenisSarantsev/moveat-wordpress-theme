<?php
/**
 * Подтверждение выдачи для «Покупки частинами» monobank.
 *
 * После одобрения заявки плагин CatCode проводит заказ (processing), но в
 * monobank заявка висит в WAITING_FOR_STORE_CONFIRM: рассрочка у клиента
 * запускается только после запроса магазина /api/order/confirm. Во Free-версии
 * плагин этот запрос не шлёт. Товары у нас цифровые и выдаются сразу, поэтому
 * подтверждаем выдачу автоматически, как только заказ стал оплаченным.
 *
 * Запрос идёт через публичный клиент плагина: он сам знает среду
 * (sandbox/stage/live), store-id, расшифровывает секрет и подписывает запрос.
 */

defined( 'ABSPATH' ) || exit;

/** Хук Action Scheduler, в котором уходит запрос в банк. */
const MOVEAT_INSTALMENTS_CONFIRM_HOOK = 'moveat_instalments_confirm';

/** Сколько раз пробуем подтвердить автоматически и с каким интервалом. */
const MOVEAT_INSTALMENTS_CONFIRM_MAX_ATTEMPTS = 5;
const MOVEAT_INSTALMENTS_CONFIRM_RETRY_DELAY  = 15 * MINUTE_IN_SECONDS;

// Мета-ключи: свои и плагина (_cciw_*), чтобы данные плагина оставались согласованными.
const MOVEAT_META_CCIW_CONFIRMED = '_moveat_cciw_confirmed';
const MOVEAT_META_CCIW_ATTEMPTS  = '_moveat_cciw_confirm_attempts';

/** Состояния заявки, в которых рассрочка уже активна — подтверждать нечего. */
const MOVEAT_CCIW_ACTIVE_STATES = array( 'ACTIVE', 'DONE' );

/**
 * Заказ оплачен частями через monobank, и его выдачу ещё не подтверждали.
 */
function moveat_instalments_needs_confirm( WC_Order $order ) {
	if ( MOVEAT_INSTALMENTS_GATEWAY !== $order->get_payment_method() ) {
		return false;
	}
	if ( 'mono' !== $order->get_meta( '_cciw_bank' ) || '' === (string) $order->get_meta( '_cciw_mono_order_id' ) ) {
		return false;
	}
	if ( $order->get_meta( MOVEAT_META_CCIW_CONFIRMED ) ) {
		return false;
	}
	return ! in_array( (string) $order->get_meta( '_cciw_txn' ), MOVEAT_CCIW_ACTIVE_STATES, true );
}

/*
	Заказ стал оплаченным → ставим подтверждение в очередь. Синхронно не шлём:
	хук срабатывает внутри вебхука/поллинга плагина, и клиент с банком не должны
	ждать ещё один HTTP-запрос.
*/
function moveat_instalments_queue_confirm( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! moveat_instalments_needs_confirm( $order ) ) {
		return;
	}

	$args = array( (int) $order_id );

	if ( function_exists( 'as_enqueue_async_action' ) ) {
		if ( ! as_has_scheduled_action( MOVEAT_INSTALMENTS_CONFIRM_HOOK, $args ) ) {
			as_enqueue_async_action( MOVEAT_INSTALMENTS_CONFIRM_HOOK, $args );
		}
		return;
	}

	// Action Scheduler нет (не должно случаться при WooCommerce) — шлём сразу.
	moveat_instalments_confirm( $order_id );
}
add_action( 'woocommerce_order_status_processing', 'moveat_instalments_queue_confirm' );
add_action( 'woocommerce_order_status_completed', 'moveat_instalments_queue_confirm' );

/**
 * Отправляет в monobank подтверждение выдачи. Идемпотентно.
 *
 * @param int  $order_id ID заказа.
 * @param bool $manual   Вызов кнопкой из админки: без автоповторов.
 * @return bool Подтверждено ли.
 */
function moveat_instalments_confirm( $order_id, $manual = false ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! moveat_instalments_needs_confirm( $order ) ) {
		return false;
	}

	$gateway = moveat_instalments_gateway();
	$client  = ( $gateway && method_exists( $gateway, 'mono_client' ) ) ? $gateway->mono_client() : null;

	if ( ! $client || ! method_exists( $client, 'confirm' ) ) {
		$order->add_order_note( 'monobank: не удалось подтвердить выдачу — плагин оплаты частями недоступен или изменился.' );
		return false;
	}

	try {
		$resp = $client->confirm( (string) $order->get_meta( '_cciw_mono_order_id' ) );
	} catch ( \Throwable $e ) {
		$resp = array( 'ok' => false, 'message' => $e->getMessage() );
	}

	$state = (string) ( $resp['state'] ?? '' );

	if ( ! empty( $resp['ok'] ) && 'FAIL' !== $state ) {
		$sub_state = (string) ( $resp['sub_state'] ?? '' );
		$order->update_meta_data( '_cciw_txn', '' !== $sub_state ? $sub_state : $state );
		$order->update_meta_data( MOVEAT_META_CCIW_CONFIRMED, time() );
		$order->delete_meta_data( MOVEAT_META_CCIW_ATTEMPTS );
		$order->add_order_note( 'monobank: выдача подтверждена, рассрочка у клиента активирована.' );
		$order->save();
		return true;
	}

	$error = trim( (string) ( $resp['message'] ?? '' ) ) ?: 'неизвестная ошибка';

	if ( $manual ) {
		$order->add_order_note( 'monobank: не удалось подтвердить выдачу — ' . $error );
		return false;
	}

	$attempt = (int) $order->get_meta( MOVEAT_META_CCIW_ATTEMPTS ) + 1;
	$order->update_meta_data( MOVEAT_META_CCIW_ATTEMPTS, $attempt );

	if ( $attempt < MOVEAT_INSTALMENTS_CONFIRM_MAX_ATTEMPTS && function_exists( 'as_schedule_single_action' ) ) {
		as_schedule_single_action( time() + MOVEAT_INSTALMENTS_CONFIRM_RETRY_DELAY, MOVEAT_INSTALMENTS_CONFIRM_HOOK, array( (int) $order_id ) );
		$order->add_order_note( sprintf( 'monobank: не удалось подтвердить выдачу (попытка %d из %d) — %s. Повторим через 15 минут.', $attempt, MOVEAT_INSTALMENTS_CONFIRM_MAX_ATTEMPTS, $error ) );
	} else {
		$order->add_order_note( 'monobank: не удалось подтвердить выдачу — ' . $error . '. Подтвердите вручную: «Действия с заказом» → «Подтвердить выдачу в monobank».' );
	}

	$order->save();
	return false;
}
add_action( MOVEAT_INSTALMENTS_CONFIRM_HOOK, 'moveat_instalments_confirm' );

// Ручная кнопка в «Действиях с заказом» — на случай, если автоповторы не помогли.
add_filter( 'woocommerce_order_actions', function ( $actions, $order = null ) {
	// Старые версии WooCommerce не передают заказ вторым аргументом — берём с экрана редактирования.
	if ( ! $order instanceof WC_Order ) {
		$order = $GLOBALS['theorder'] ?? null;
	}
	if ( $order instanceof WC_Order && moveat_instalments_needs_confirm( $order ) ) {
		$actions['moveat_instalments_confirm'] = 'Подтвердить выдачу в monobank';
	}
	return $actions;
}, 10, 2 );

add_action( 'woocommerce_order_action_moveat_instalments_confirm', function ( $order ) {
	moveat_instalments_confirm( $order->get_id(), true );
} );
