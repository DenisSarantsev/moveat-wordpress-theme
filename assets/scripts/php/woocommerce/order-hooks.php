<?php

defined( 'ABSPATH' ) || exit;

/*
	Парсит cookie moveat_pending_order (JS пишет encodeURIComponent(JSON)).
*/
function moveat_parse_pending_order_cookie() {
	$result = array(
		'order_id'  => 0,
		'order_key' => '',
	);

	if ( empty( $_COOKIE['moveat_pending_order'] ) ) {
		return $result;
	}

	$raw = wp_unslash( $_COOKIE['moveat_pending_order'] );
	$data = json_decode( urldecode( $raw ), true );

	if ( ! is_array( $data ) ) {
		$data = json_decode( $raw, true );
	}

	if ( ! is_array( $data ) ) {
		return $result;
	}

	if ( ! empty( $data['order_id'] ) ) {
		$result['order_id'] = absint( $data['order_id'] );
	}

	if ( ! empty( $data['order_key'] ) ) {
		$result['order_key'] = sanitize_text_field( (string) $data['order_key'] );
	}

	return $result;
}

/*
	Возвращает заказ для страницы «Спасибо» по GET или cookie (до удаления cookie).
*/
function moveat_get_thankyou_order_from_request() {
	$order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( wp_unslash( $_GET['order_key'] ) ) : '';

	if ( ! $order_id || ! $order_key ) {
		$pending   = moveat_parse_pending_order_cookie();
		$order_id  = $pending['order_id'];
		$order_key = $pending['order_key'];
	}

	if ( ! $order_id || ! $order_key || ! function_exists( 'wc_get_order' ) ) {
		return false;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order || ! hash_equals( (string) $order->get_order_key(), (string) $order_key ) ) {
		return false;
	}

	return $order;
}

/*
	Проверяет, что заказ создан как донат.
*/
function moveat_is_donation_order( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return false;
	}

	return $order->get_meta( '_is_donation' ) === 'yes'
		|| $order->get_created_via() === 'donation';
}

/*
	URL страницы благодарности с параметрами заказа.
*/
function moveat_get_thankyou_page_url( $order ) {
	$url = home_url( '/stranicza-spasibo/' );

	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $url;
	}

	return add_query_arg(
		array(
			'order_id'  => $order->get_id(),
			'order_key' => $order->get_order_key(),
		),
		$url
	);
}

/*
	Заказы, созданные через cod, остаются в статусе "pending".
	cod по умолчанию переводит заказ сразу в "processing" — отменяем это поведение,
	чтобы пользователь мог выбрать реальный метод оплаты на странице /order-pay/.
*/
add_filter( 'woocommerce_cod_process_payment_order_status', function( $status ) {
	return 'pending';
} );

/*
	При заходе на страницу ошибки оплаты `/pay-problem/` удаляем флаговый cookie,
	чтобы пользователь мог повторно начать оплату — cookie восстановится при новом клике на "Оплатить".
*/
add_action( 'template_redirect', function() {
	if ( is_admin() ) {
		return;
	}

	if ( ! function_exists( 'is_page' ) || ! is_page( 'pay-problem' ) ) {
		return;
	}

	if ( empty( $_COOKIE['moveat_pending_order'] ) ) {
		return;
	}


	$cookie_name = 'moveat_pending_order';
	// Попробуем удалить куку несколькими способами: без domain (host-only) и с заголовком
	// 1) Удаление стандартным setcookie без domain — покрывает host-only куки, выставленные JS
	@setcookie( $cookie_name, '', time() - 3600, '/' );
	// 2) на случай, если кука ставилась с доменом — удалить и с domain
	$domain = parse_url( home_url(), PHP_URL_HOST );
	if ( $domain ) {
		@setcookie( $cookie_name, '', time() - 3600, '/', $domain );
	}
	// 3) очистка в текущем запросе
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		unset( $_COOKIE[ $cookie_name ] );
	}

	return;
} );

/*
	Если у пользователя есть флаговый cookie процесса оплаты, но он не на странице
	благодарности и не на странице ошибки, направим его сначала на страницу
	благодарности `/stranicza-spasibo/`. После перехода на эту страницу выполнится
	существующая проверка статуса заказа и будет решено — показать спасибо или
	перекинуть на /pay-problem/.
*/
add_action( 'template_redirect', function() {
	if ( is_admin() ) {
		return;
	}

	// Не вмешиваемся в REST/AJAX/CRON
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) ) {
		return;
	}

	if ( empty( $_COOKIE['moveat_pending_order'] ) ) {
		return;
	}

	// Если уже на странице благодарности или на странице ошибки — ничего не делаем
	if ( function_exists( 'is_page' ) && ( is_page( 'stranicza-spasibo' ) || is_page( 'pay-problem' ) ) ) {
		return;
	}

	// Иначе перенаправляем на страницу спасибо с order_id/order_key из cookie.
	$pending = moveat_parse_pending_order_cookie();
	$redirect_url = home_url( '/stranicza-spasibo/' );

	if ( $pending['order_id'] && $pending['order_key'] ) {
		$redirect_url = add_query_arg(
			array(
				'order_id'  => $pending['order_id'],
				'order_key' => $pending['order_key'],
			),
			$redirect_url
		);
	}

	wp_safe_redirect( $redirect_url );
	exit;
} );

/*
	Серверная проверка: если пользователь возвращается на страницу благодарности
	`/stranicza-spasibo/` и у него в cookie есть `moveat_pending_order`, проверим
	статус заказа и при `failed` перенаправим на `/pay-problem/` без показа страницы.
*/
add_action( 'template_redirect', function() {
	if ( is_admin() ) {
		return;
	}

	// Действуем только на странице со слагом 'stranicza-spasibo'
	if ( ! function_exists( 'is_page' ) || ! is_page( 'stranicza-spasibo' ) ) {
		return;
	}

	if ( empty( $_COOKIE['moveat_pending_order'] ) ) {
		return;
	}

	$pending   = moveat_parse_pending_order_cookie();
	$order_id  = $pending['order_id'];
	$order_key = $pending['order_key'];

	// Функция для безопасного удаления cookie-флага
	$clear_cookie = function() {
		if ( headers_sent() ) {
			// Попытка удалить через setcookie может не сработать, но unset локальной переменной — всё же сделаем
			unset( $_COOKIE['moveat_pending_order'] );
			return;
		}
		setcookie( 'moveat_pending_order', '', time() - 3600, '/' );
		unset( $_COOKIE['moveat_pending_order'] );
	};

	if ( ! $order_id || ! $order_key ) {
		$clear_cookie();
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		$clear_cookie();
		return;
	}

	// Проверяем соответствие order_key
	if ( ! hash_equals( (string) $order->get_order_key(), (string) $order_key ) ) {
		$clear_cookie();
		return;
	}

	$status = $order->get_status(); // например 'pending', 'failed', 'processing', 'completed'

	/*
		Если заказ в статусе 'pending' — помечаем как failed и показываем страницу проблемы.
		Это упрощённая логика: если пользователь вернулся с процесса оплаты, а заказ
		всё ещё в ожидании, считаем, что оплата не прошла.
	*/
	if ( 'pending' === $status ) {
		$order->add_order_note( 'Автообновление: заказ оставался в статусе pending после возврата с платёжного процесса — помечен как failed.' );
		$order->update_status( 'failed', 'Автообновление статуса: пользователь вернулся с платёжного шлюза без подтверждения оплаты.' );

		$redirect = add_query_arg(
			array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			),
			home_url( '/pay-problem/' )
		);

		$clear_cookie();
		wp_safe_redirect( $redirect );
		exit;
	}

	// Если заказ явно провален — редиректим на страницу ошибки оплаты
	if ( 'failed' === $status ) {
		$redirect = add_query_arg(
			array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			),
			home_url( '/pay-problem/' )
		);

		$clear_cookie();
		wp_safe_redirect( $redirect );
		exit;
	}

	// Если заказ оплачен — дописываем order_id/order_key в URL, затем удаляем cookie
	if ( $order->is_paid() || $order->has_status( array( 'processing', 'completed' ) ) ) {
		$has_query_args = ! empty( $_GET['order_id'] ) && ! empty( $_GET['order_key'] );

		if ( ! $has_query_args ) {
			wp_safe_redirect( moveat_get_thankyou_page_url( $order ) );
			exit;
		}

		$clear_cookie();
		return;
	}

	// Для прочих статусов (например 'pending') — не редиректим и оставляем cookie
	return;
} );

// REST-эндпоинт, используемый фронтэнд-проверкой, для проверки статуса заказа по order_id + order_key
add_action( 'rest_api_init', function() {
	register_rest_route( 'moveat/v1', '/order-status', array(
		'methods'  => 'GET',
		'callback' => function( WP_REST_Request $request ) {
			$order_id  = intval( $request->get_param( 'order_id' ) );
			$order_key = sanitize_text_field( (string) $request->get_param( 'order_key' ) );

			if ( ! $order_id || ! $order_key ) {
				return new WP_REST_Response( array( 'success' => false, 'code' => 'missing_params' ), 400 );
			}

			if ( ! function_exists( 'wc_get_order_id_by_order_key' ) ) {
				return new WP_REST_Response( array( 'success' => false, 'code' => 'wc_not_available' ), 500 );
			}

			$expected_id = wc_get_order_id_by_order_key( $order_key );
			if ( ! $expected_id || $expected_id !== $order_id ) {
				return new WP_REST_Response( array( 'success' => false, 'code' => 'invalid_key' ), 403 );
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return new WP_REST_Response( array( 'success' => false, 'code' => 'order_not_found' ), 404 );
			}

			$is_paid = $order->is_paid() || $order->has_status( array( 'processing', 'completed' ) );
			$payment_method = $order->get_payment_method();
			$status = $order->get_status();

			return array(
				'success' => true,
				'is_paid' => (bool) $is_paid,
				'payment_method' => $payment_method,
				'status' => $status,
			);
		},
		'permission_callback' => '__return_true',
	) );
} );

/*
	Редирект после успешной оплаты на кастомную страницу "Спасибо".
	Делаем поведение условным: перенаправляем на страницу спасибо только если
	заказ существует и имеет статус paid/processing/completed для целевых шлюзов.
*/
add_filter( 'woocommerce_get_return_url', function( $return_url, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $return_url;
	}

	$success_slugs = array( 'ppcp-gateway', 'paypal', 'mono_gateway' );

	// Если метод оплаты не в списке — оставляем исходный URL
	if ( ! in_array( $order->get_payment_method(), $success_slugs, true ) ) {
		return $return_url;
	}

	// Если заказ оплачен или в статусах, соответствующих успешной оплате — перенаправляем
	if ( $order->is_paid() || $order->has_status( array( 'processing', 'completed' ) ) ) {
		return moveat_get_thankyou_page_url( $order );
	}

	// Не менять URL для неподтверждённых/неоплаченных заказов
	return $return_url;
}, 10, 2 );

/*
	PayPal Sandbox иногда возвращает статус "on-hold" вместо "processing".
	Принудительно переводим в "processing" после успешной оплаты через PayPal или Monobank.
*/
add_action( 'woocommerce_order_status_on-hold', function( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && in_array( $order->get_payment_method(), [ 'ppcp-gateway', 'mono_gateway' ], true ) ) {
		$order->payment_complete();
	}
} );

// После успешной оплаты меняем статус для PayPal на "completed"
add_filter( 'woocommerce_payment_complete_order_status', function( $status, $order_id, $order ) {
	if ( $order && $order->get_payment_method() === 'ppcp-gateway' ) {
		return 'completed';
	}
	return $status;
}, 10, 3 );
