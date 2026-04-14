<?php
/*
	Шаблон письма: новое создание заказа (пользовательское уведомление)
	Переменные, которые ожидаются:
		- $order (WC_Order)
		- $payment_url (string)
*/
defined( 'ABSPATH' ) || exit;

if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
	return;
}

$first_name = $order->get_billing_first_name();
$order_number = $order->get_order_number();
$payment_url = isset( $payment_url ) ? $payment_url : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Кастомное письмо - Ваш заказ #<?php echo esc_html( $order_number ); ?></title>
</head>
<body>
	<h2>Здравствуйте<?php echo $first_name ? ' ' . esc_html( $first_name ) : ''; ?>,</h2>

	<p>Спасибо! Ваш заказ <strong>#<?php echo esc_html( $order_number ); ?></strong> создан.</p>

	<?php if ( $payment_url ) : ?>
		<p>Чтобы завершить оплату, пожалуйста, перейдите по ссылке:</p>
		<p><a href="<?php echo esc_url( $payment_url ); ?>"><?php echo esc_html( $payment_url ); ?></a></p>
	<?php else : ?>
		<p>Ссылка на оплату временно недоступна. Пожалуйста, войдите в свой аккаунт или свяжитесь с поддержкой.</p>
	<?php endif; ?>

	<p>Детали заказа:</p>
	<ul>
		<li>Номер заказа: <?php echo esc_html( $order_number ); ?></li>
		<li>Сумма: <?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></li>
		<li>Статус: <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></li>
	</ul>

	<p>Если у вас возникли вопросы, ответьте на это письмо или свяжитесь с поддержкой сайта.</p>

	<p>С уважением,<br>Команда Moveat</p>
</body>
</html>
