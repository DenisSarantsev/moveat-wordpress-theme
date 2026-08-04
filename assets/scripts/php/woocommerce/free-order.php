<?php
/*
	Заказы с нулевой суммой (бесплатный товар или купон на 100%).

	Оплачивать в них нечего, поэтому платёжный шлюз не подключаем:
	заказ создаётся обычным путём, сразу проводится через payment_complete()
	и пользователь отправляется на страницу благодарности минуя `/order-pay/`.

	payment_complete() выбран намеренно — он повторяет путь платного заказа:
	  - переводит заказ в «Выполнен» (статус форсится фильтром ниже);
	  - снимает запланированные напоминания об оплате (см. emails/reminders.php);
	  - триггерит письма клиенту и админу и синхронизацию с Tallanto.
*/

defined( 'ABSPATH' ) || exit;

// Слаг и название метода оплаты для нулевых заказов (виден в админке заказа).
const MOVEAT_FREE_PAYMENT_METHOD = 'moveat_free';
const MOVEAT_FREE_PAYMENT_TITLE  = 'Без оплаты (0)';

/*
	Заказ бесплатный — итоговая сумма равна нулю (или ушла в минус на скидке).
*/
function moveat_order_is_free( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return false;
	}

	return (float) $order->get_total() <= 0;
}

/*
	Проводит бесплатный заказ без оплаты. Идемпотентно: повторный вызов
	на уже проведённом заказе ничего не меняет (двойной сабмит, возврат по истории).
*/
function moveat_complete_free_order( $order ) {
	if ( ! moveat_order_is_free( $order ) ) {
		return false;
	}

	// Уже проведён — второй раз статус не трогаем.
	if ( $order->is_paid() || $order->has_status( array( 'processing', 'completed' ) ) ) {
		return true;
	}

	try {
		$order->set_payment_method( MOVEAT_FREE_PAYMENT_METHOD );
		$order->set_payment_method_title( MOVEAT_FREE_PAYMENT_TITLE );
		$order->add_order_note( 'Сумма заказа равна нулю — заказ проведён без оплаты.' );
		$order->save();

		// Заказ проводится из REST-запроса (/my-api/v1/create-order), где обработчики
		// транзакционных писем могут быть ещё не подключены. Дёргаем mailer явно,
		// чтобы письмо «Заказ выполнен» ушло так же, как после обычной оплаты.
		if ( function_exists( 'WC' ) ) {
			WC()->mailer();
		}

		$order->payment_complete();
	} catch ( \Throwable $e ) {
		error_log( '[moveat free-order] complete error: ' . $e->getMessage() );
		return false;
	}

	return true;
}

/*
	Бесплатный заказ сразу «Выполнен».

	payment_complete() по умолчанию ставит `completed` только когда все товары
	виртуальные/скачиваемые, иначе `processing`. В `processing` WooCommerce не отправляет
	письмо «Заказ выполнен», и клиент остаётся без подтверждения. Оплачивать и отгружать
	в бесплатном заказе нечего, поэтому доводим его до конца сразу.

	Заодно на статусе completed срабатывают письмо админу (emails/completed-order.php)
	и дозапись заказа в Tallanto (tallanto/woocommerce-sync.php) — как у оплаченного заказа.
*/
add_filter( 'woocommerce_payment_complete_order_status', function( $status, $order_id, $order ) {
	if ( ! $order ) {
		return $status;
	}

	if ( MOVEAT_FREE_PAYMENT_METHOD === $order->get_payment_method() || moveat_order_is_free( $order ) ) {
		return 'completed';
	}

	return $status;
}, 10, 3 );

/*
	Очищает корзину после проведения бесплатного заказа.
	В REST-контексте корзина не инициализируется сама — поднимаем её из сессии
	клиента (куки приходят вместе с запросом) через wc_load_cart().
*/
function moveat_empty_cart_after_free_order() {
	try {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}

		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
	} catch ( \Throwable $e ) {
		error_log( '[moveat free-order] empty cart error: ' . $e->getMessage() );
	}
}

/*
	Страховка на прямой заход: если пользователь открыл `/order-pay/` со ссылкой
	на бесплатный заказ (из письма, истории браузера, закладки) — платить нечего,
	проводим заказ и уводим на страницу благодарности.
*/
add_action( 'template_redirect', function() {
	if ( is_admin() || ! function_exists( 'is_page' ) || ! is_page( 'order-pay' ) ) {
		return;
	}

	$order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( wp_unslash( $_GET['order_key'] ) ) : '';

	if ( ! $order_id || ! $order_key || ! function_exists( 'wc_get_order' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order || ! $order->key_is_valid( $order_key ) || ! moveat_order_is_free( $order ) ) {
		return;
	}

	moveat_complete_free_order( $order );

	wp_safe_redirect( moveat_get_thankyou_page_url( $order ) );
	exit;
} );
