<?php
/*
	Язык писем по заказу.

	Письма уходят из крона (напоминания) или из хуков смены статуса, когда
	Polylang уже не знает, на какой версии сайта был покупатель. Поэтому язык
	запоминаем в мету заказа при его создании (_moveat_lang), а для старых
	заказов без меты берём язык первого товара: на украинской версии в корзину
	попадают украинские переводы товаров.

	Тексты писем — в assets/dictionaries/emails/<письмо>/<язык>.php.
	Письма менеджерам остаются на русском.
*/

defined( 'ABSPATH' ) || exit;

define( 'MOVEAT_ORDER_LANG_META', '_moveat_lang' );

/*
	Язык запроса, из которого создают заказ.

	Заказ создаётся REST-запросом с фронта, где текущий язык Polylang не
	определён, поэтому смотрим на адрес страницы, с которой пришёл запрос:
	/uk/... — украинский, иначе — язык по умолчанию.
*/
function moveat_request_lang() {
	$referer = isset( $_SERVER['HTTP_REFERER'] ) ? (string) wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';
	$path    = $referer ? (string) wp_parse_url( $referer, PHP_URL_PATH ) : '';

	if ( '' !== $path ) {
		// Сайт может жить в подпапке (локально — /moveat/): отрезаем её.
		$home_path = (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH );
		$home_path = '/' . trim( $home_path, '/' );
		if ( '/' !== $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}

		$segment = strtolower( (string) strtok( trim( $path, '/' ), '/' ) );
		if ( in_array( $segment, array( 'uk', 'ua', 'ru' ), true ) ) {
			return moveat_normalize_lang( $segment );
		}

		return moveat_normalize_lang( '' );
	}

	return moveat_current_lang();
}

/*
	Запоминает язык заказа. Вызывается там, где заказ создаётся по запросу
	покупателя (create-order, create-donation-order). Сохранение — на вызывающем.
*/
function moveat_set_order_lang( $order, $lang = null ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	$order->update_meta_data( MOVEAT_ORDER_LANG_META, null === $lang ? moveat_request_lang() : moveat_normalize_lang( $lang ) );
}

/*
	Язык заказа: 'ru' | 'uk'.
*/
function moveat_order_lang( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return moveat_normalize_lang( '' );
	}

	$saved = (string) $order->get_meta( MOVEAT_ORDER_LANG_META );
	if ( '' !== $saved ) {
		return moveat_normalize_lang( $saved );
	}

	// Старые заказы: по языку товара.
	if ( function_exists( 'pll_get_post_language' ) ) {
		foreach ( $order->get_items() as $item ) {
			$product_id = is_callable( array( $item, 'get_product_id' ) ) ? (int) $item->get_product_id() : 0;
			$lang       = $product_id ? pll_get_post_language( $product_id ) : '';
			if ( $lang ) {
				return moveat_normalize_lang( $lang );
			}
		}
	}

	return moveat_normalize_lang( '' );
}

/*
	Словарь письма на языке заказа.
	moveat_order_email_dictionary( 'customer-completed-order', $order )
*/
function moveat_order_email_dictionary( $email_slug, $order ) {
	return moveat_dictionary( 'emails/' . $email_slug, moveat_order_lang( $order ) );
}

/*
	Строка словаря письма. Ключа нет — пустая строка.
*/
function moveat_email_text( $dictionary, $key ) {
	return moveat_dictionary_text( (array) $dictionary, $key, '' );
}

/*
	Ссылка на мессенджер из словаря письма (секция 'messengers').
*/
function moveat_email_messenger_url( $dictionary, $messenger ) {
	return isset( $dictionary['messengers'][ $messenger ] ) ? (string) $dictionary['messengers'][ $messenger ] : '';
}

/*
	Тема стандартного письма WooCommerce из словаря.

	Пустая 'subject' в словаре — оставляем тему из настроек WooCommerce
	(так у русской версии). Плейсхолдеры вида {order_number} подставляет сам
	WooCommerce через format_string().
*/
function moveat_filter_wc_email_subject( $email_slug ) {
	return static function ( $subject, $order, $email = null ) use ( $email_slug ) {
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return $subject;
		}

		$custom = moveat_email_text( moveat_order_email_dictionary( $email_slug, $order ), 'subject' );
		if ( '' === $custom ) {
			return $subject;
		}

		return ( $email && is_callable( array( $email, 'format_string' ) ) ) ? $email->format_string( $custom ) : $custom;
	};
}

add_filter( 'woocommerce_email_subject_customer_completed_order', moveat_filter_wc_email_subject( 'customer-completed-order' ), 20, 3 );
add_filter( 'woocommerce_email_subject_customer_failed_order', moveat_filter_wc_email_subject( 'customer-failed-order' ), 20, 3 );

/*
	Адрес страницы оплаты /order-pay/ на языке заказа.
	Нет украинского перевода страницы — русский адрес.
*/
function moveat_order_pay_page_url( $order ) {
	$url  = home_url( '/order-pay/' );
	$page = get_page_by_path( 'order-pay' );

	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID, moveat_order_lang( $order ) );
		if ( $translated ) {
			$link = get_permalink( $translated );
			if ( $link ) {
				$url = $link;
			}
		}
	}

	return $url;
}
