<?php
/*
	Отправляет письмо донатору после успешной оплаты доната.
	Настройки письма — вкладка Email на странице донатов (donations.json).

	Язык письма — язык заказа (moveat_order_lang()): поля ACF и соцсети берутся
	с языковой версии страницы донатов, тексты по умолчанию — из словаря
	assets/dictionaries/emails/customer-donation-completed-order.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOVEAT_DONATION_EMAIL_SOCIALS_MAX', 10 );

/*
	ID страницы с шаблоном донатов на нужном языке ('ru' | 'uk').
	Без языка или без перевода — первая найденная страница с шаблоном.
*/
function moveat_get_donations_page_id( $lang = null ) {
	static $base_id = null;

	if ( $base_id === null ) {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template',
				'meta_value' => 'templates/donations-page.php',
				'number'     => 1,
			)
		);

		$base_id = ! empty( $pages[0]->ID ) ? (int) $pages[0]->ID : 0;
	}

	if ( $base_id && $lang && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $base_id, $lang );
		if ( $translated ) {
			return (int) $translated;
		}
	}

	return $base_id;
}

/*
	Текстовое поле Email со страницы донатов с запасным значением.
*/
function moveat_get_donations_email_field( $field_name, $default = '', $lang = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$page_id = moveat_get_donations_page_id( $lang );
	if ( ! $page_id ) {
		return $default;
	}

	$value = get_field( $field_name, $page_id );
	$value = is_string( $value ) ? trim( $value ) : $value;

	if ( $value === '' || $value === null ) {
		return $default;
	}

	return $value;
}

/*
	Нормализует значение поля ACF «изображение» в URL.
*/
function moveat_normalize_donation_email_icon_url( $icon ) {
	if ( is_array( $icon ) && ! empty( $icon['url'] ) ) {
		return (string) $icon['url'];
	}

	if ( is_numeric( $icon ) ) {
		$url = wp_get_attachment_image_url( (int) $icon, 'medium' );
		return $url ? $url : '';
	}

	return is_string( $icon ) ? trim( $icon ) : '';
}

/*
	Список соцсетей для письма (только заполненные: ссылка + иконка).
*/
function moveat_get_donation_email_social_items( $lang = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$t       = moveat_dictionary( 'emails/customer-donation-completed-order', $lang );
	$page_id = moveat_get_donations_page_id( $lang );
	if ( ! $page_id ) {
		return array();
	}

	$items = array();

	for ( $i = 1; $i <= MOVEAT_DONATION_EMAIL_SOCIALS_MAX; $i++ ) {
		$url = trim( (string) get_field( 'donations_email_social_' . $i . '_url', $page_id ) );
		$icon_url = moveat_normalize_donation_email_icon_url(
			get_field( 'donations_email_social_' . $i . '_icon', $page_id )
		);
		$bg_color = trim( (string) get_field( 'donations_email_social_' . $i . '_bg_color', $page_id ) );
		$label = trim( (string) get_field( 'donations_email_social_' . $i . '_label', $page_id ) );

		if ( $url === '' || $url === '#' || $icon_url === '' ) {
			continue;
		}

		$items[] = array(
			'url'      => $url,
			'icon_url' => $icon_url,
			'bg_color' => $bg_color !== '' ? $bg_color : '#ff7f13',
			'label'    => $label !== '' ? $label : sprintf( moveat_email_text( $t, 'social_label' ), $i ),
		);
	}

	return $items;
}

/*
	Тексты письма: ACF-поле со страницы донатов на языке письма,
	а если оно пустое — строка из словаря.
*/
function moveat_get_donation_email_content( $lang = null ) {
	$t       = moveat_dictionary( 'emails/customer-donation-completed-order', $lang );
	$content = array();

	$keys = array(
		'subject', 'greeting', 'thanks', 'intro', 'details_title', 'label_amount', 'label_date',
		'label_payment', 'outro_thanks', 'signoff_line', 'signoff_team', 'socials_title', 'footer',
	);

	foreach ( $keys as $key ) {
		$content[ $key ] = moveat_get_donations_email_field( 'donations_email_' . $key, moveat_email_text( $t, $key ), $lang );
	}

	return $content;
}

/*
	Человекочитаемое название способа оплаты доната.

	Обёртка над общим реестром способов оплаты (woocommerce/payment-method-display.php),
	чтобы подписи в письмах и в админке не разъезжались. Свой словарь здесь
	держать больше не нужно.
*/
function moveat_get_donation_payment_method_label( $order, $lang = null ) {
	$t    = moveat_dictionary( 'emails/customer-donation-completed-order', $lang );
	$slug = $order ? (string) $order->get_payment_method() : '';

	// Перевод подписи из словаря письма; нет — общая (русская) подпись реестра.
	if ( $slug !== '' && ! empty( $t['payment_methods'][ $slug ] ) ) {
		return (string) $t['payment_methods'][ $slug ];
	}

	return \Moveat\Woo\PaymentMethod\label( $order );
}

/*
	Формирует HTML письма по шаблону и отправляет подтверждение донатору.
*/
function moveat_send_customer_donation_completed_email( $order_id ) {
	if ( ! $order_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	if ( ! function_exists( 'moveat_is_donation_order' ) || ! moveat_is_donation_order( $order ) ) {
		return;
	}

	if ( $order->get_meta( '_moveat_donation_completed_email_sent' ) === 'yes' ) {
		return;
	}

	if ( ! $order->is_paid() && ! $order->has_status( array( 'processing', 'completed' ) ) ) {
		return;
	}

	$to = $order->get_billing_email();
	if ( ! is_email( $to ) ) {
		return;
	}

	$lang          = moveat_order_lang( $order );
	$email_content = moveat_get_donation_email_content( $lang );

	ob_start();
	wc_get_template(
		'emails/customer-donation-completed-order.php',
		array(
			'order'                => $order,
			'email_content'        => $email_content,
			'payment_method_label' => moveat_get_donation_payment_method_label( $order, $lang ),
			'social_items'         => moveat_get_donation_email_social_items( $lang ),
		),
		'',
		get_template_directory() . '/woocommerce/'
	);
	$html = ob_get_clean();

	$subject = $email_content['subject'];
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$sent = wp_mail( $to, $subject, $html, $headers );

	if ( $sent ) {
		$order->update_meta_data( '_moveat_donation_completed_email_sent', 'yes' );
		$order->save();
	}
}

add_action( 'woocommerce_payment_complete', 'moveat_send_customer_donation_completed_email', 10, 1 );
add_action( 'woocommerce_order_status_processing', 'moveat_send_customer_donation_completed_email', 10, 1 );
add_action( 'woocommerce_order_status_completed', 'moveat_send_customer_donation_completed_email', 10, 1 );

/*
	Не отправляем стандартное письмо WooCommerce «Заказ выполнен» для донатов —
	вместо него уходит кастомное письмо после доната.
*/
add_filter(
	'woocommerce_email_enabled_customer_completed_order',
	function ( $enabled, $order ) {
		if ( $order && function_exists( 'moveat_is_donation_order' ) && moveat_is_donation_order( $order ) ) {
			return false;
		}

		return $enabled;
	},
	10,
	2
);

?>
