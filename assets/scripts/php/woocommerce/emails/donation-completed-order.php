<?php
/*
	Отправляет письмо донатору после успешной оплаты доната.
	Настройки письма — вкладка Email на странице донатов (donations.json).
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOVEAT_DONATION_EMAIL_SOCIALS_MAX', 10 );

/*
	ID страницы с шаблоном донатов.
*/
function moveat_get_donations_page_id() {
	static $page_id = null;

	if ( $page_id !== null ) {
		return $page_id;
	}

	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'templates/donations-page.php',
			'number'     => 1,
		)
	);

	$page_id = ! empty( $pages[0]->ID ) ? (int) $pages[0]->ID : 0;

	return $page_id;
}

/*
	Текстовое поле Email со страницы донатов с запасным значением.
*/
function moveat_get_donations_email_field( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$page_id = moveat_get_donations_page_id();
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
function moveat_get_donation_email_social_items() {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$page_id = moveat_get_donations_page_id();
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
			'label'    => $label !== '' ? $label : sprintf( 'Соцсеть %d', $i ),
		);
	}

	return $items;
}

/*
	Тексты письма со страницы донатов.
*/
function moveat_get_donation_email_content() {
	return array(
		'subject'       => moveat_get_donations_email_field(
			'donations_email_subject',
			'Спасибо! Вы помогаете проекту Moveat развиваться ❤️'
		),
		'greeting'      => moveat_get_donations_email_field( 'donations_email_greeting', 'Здравствуйте!' ),
		'thanks'        => moveat_get_donations_email_field( 'donations_email_thanks', 'Спасибо за вашу поддержку ❤️' ),
		'intro'         => moveat_get_donations_email_field(
			'donations_email_intro',
			'Такие переводы помогают нам продолжать делать то, что мы считаем важным: выпускать полезные материалы о здоровье, объяснять сложное простым языком и помогать людям лучше понимать свой организм.'
		),
		'details_title' => moveat_get_donations_email_field( 'donations_email_details_title', 'Детали поддержки:' ),
		'label_amount'  => moveat_get_donations_email_field( 'donations_email_label_amount', 'Сумма:' ),
		'label_date'    => moveat_get_donations_email_field( 'donations_email_label_date', 'Дата:' ),
		'label_payment' => moveat_get_donations_email_field( 'donations_email_label_payment', 'Способ оплаты:' ),
		'outro_thanks'  => moveat_get_donations_email_field( 'donations_email_outro_thanks', 'Спасибо, что вы с нами.' ),
		'signoff_line'  => moveat_get_donations_email_field( 'donations_email_signoff_line', 'С уважением,' ),
		'signoff_team'  => moveat_get_donations_email_field( 'donations_email_signoff_team', 'Команда Moveat' ),
		'socials_title' => moveat_get_donations_email_field( 'donations_email_socials_title', 'Следите за новыми материалами:' ),
		'footer'        => moveat_get_donations_email_field( 'donations_email_footer', 'Moveat' ),
	);
}

/*
	Человекочитаемое название способа оплаты доната.
*/
function moveat_get_donation_payment_method_label( $order ) {
	$labels = array(
		'ppcp-gateway' => 'PayPal',
		'mono_gateway' => __( 'Банковская карта', 'moveat' ),
		'paypal'       => 'PayPal',
	);

	$method = $order->get_payment_method();
	if ( isset( $labels[ $method ] ) ) {
		return $labels[ $method ];
	}

	$title = $order->get_payment_method_title();
	if ( $title ) {
		return $title;
	}

	return $method ? $method : '—';
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

	$email_content = moveat_get_donation_email_content();

	ob_start();
	wc_get_template(
		'emails/customer-donation-completed-order.php',
		array(
			'order'                => $order,
			'email_content'        => $email_content,
			'payment_method_label' => moveat_get_donation_payment_method_label( $order ),
			'social_items'         => moveat_get_donation_email_social_items(),
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
