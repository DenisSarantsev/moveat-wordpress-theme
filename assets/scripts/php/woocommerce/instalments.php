<?php
/**
 * Оплата частями monobank через плагин CatCode (шлюз cc_payparts).
 *
 * Плагин работает только с гривной и берёт сумму заявки из заказа, а заказы
 * у нас в долларах. Сам заказ не переводим: на время создания заявки
 * подменяем суммы, которые плагин читает через геттеры WooCommerce. Так заказ
 * остаётся в USD, и после неудачной рассрочки карта/PayPal работают как раньше.
 */

defined( 'ABSPATH' ) || exit;

/** ID шлюза плагина CatCode. */
const MOVEAT_INSTALMENTS_GATEWAY = 'cc_payparts';

/**
 * Курс USD → UAH. Тот же источник, что и для сумм на странице оплаты,
 * чтобы в заявку ушла ровно та сумма в гривнах, которую видел клиент.
 */
function moveat_order_pay_get_uah_rate() {
	if ( class_exists( '\Yay_Currency\Helpers\YayCurrencyHelper' ) ) {
		$currencies   = \Yay_Currency\Helpers\YayCurrencyHelper::converted_currency();
		$uah_currency = \Yay_Currency\Helpers\YayCurrencyHelper::get_currency_by_currency_code( 'UAH', $currencies );
		if ( $uah_currency ) {
			$rate = \Yay_Currency\Helpers\YayCurrencyHelper::get_rate_fee( $uah_currency );
			if ( $rate ) {
				return (float) $rate;
			}
		}
	}
	return (float) get_option( 'moveat_uah_rate', 0 );
}

/**
 * Оплата частями пока на тестировании — доступна только администраторам магазина.
 * Открыть для всех: вернуть true.
 */
function moveat_instalments_allowed_for_current_user() {
	return current_user_can( 'manage_woocommerce' );
}

/** Экземпляр шлюза CatCode или null, если плагин не активен. */
function moveat_instalments_gateway() {
	if ( ! function_exists( 'WC' ) ) {
		return null;
	}
	$gateways = WC()->payment_gateways()->payment_gateways();
	return $gateways[ MOVEAT_INSTALMENTS_GATEWAY ] ?? null;
}

/*
	Варианты количества платежей берём из настроек плагина (поле «Кількість платежів»).
	parts_list() уже отбрасывает значения вне лимитов банка; первое значение —
	вариант по умолчанию. Пустой массив — способ не показываем.
*/
function moveat_order_pay_get_installment_terms() {
	$gateway = moveat_instalments_gateway();

	if ( ! $gateway || 'yes' !== $gateway->enabled || ! method_exists( $gateway, 'parts_list' ) ) {
		return array();
	}

	if ( method_exists( $gateway, 'mono_enabled' ) && ! $gateway->mono_enabled() ) {
		return array();
	}

	return array_map( 'intval', (array) $gateway->parts_list( 'mono' ) );
}

/**
 * Выполняет $callback, пока суммы заказа отдаются плагину в гривнах.
 *
 * Плагин читает get_total() заказа и get_total()/get_total_tax() позиций
 * в контексте 'view' — эти значения проходят через фильтры ниже. В базу
 * сохраняются данные контекста 'edit', поэтому заказ не меняется.
 */
function moveat_instalments_with_uah_amounts( $rate, callable $callback ) {
	$to_uah = static function ( $value ) use ( $rate ) {
		return round( (float) $value * $rate, 2 );
	};

	$hooks = array(
		'woocommerce_order_get_total',
		'woocommerce_order_item_get_total',
		'woocommerce_order_item_get_total_tax',
	);

	foreach ( $hooks as $hook ) {
		add_filter( $hook, $to_uah, PHP_INT_MAX );
	}

	/*
		Плагин шлёт суммы в JSON как float. При serialize_precision ≠ -1 (на хостинге
		бывает 17) json_encode пишет 5850.37 как 5850.3699999999999, и monobank
		отклоняет заявку: «максимум 2 знаки після точки». -1 — кратчайшая точная запись.
	*/
	$precision = ini_get( 'serialize_precision' );
	ini_set( 'serialize_precision', '-1' );

	try {
		return $callback();
	} finally {
		foreach ( $hooks as $hook ) {
			remove_filter( $hook, $to_uah, PHP_INT_MAX );
		}
		if ( false !== $precision ) {
			ini_set( 'serialize_precision', $precision );
		}
	}
}

/**
 * URL, который опрашивает экран ожидания: эндпоинт плагина сверяет статус
 * заявки с банком и отвечает { done, ok, redirect | message }.
 */
function moveat_instalments_poll_url( WC_Order $order ) {
	return add_query_arg(
		array(
			'order_id' => $order->get_id(),
			'key'      => $order->get_order_key(),
		),
		WC()->api_request_url( MOVEAT_INSTALMENTS_GATEWAY . '_poll' )
	);
}
