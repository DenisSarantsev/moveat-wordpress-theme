<?php
/*
	Отправляет уведомление администраторам, когда заказ переводится в статус "выполнен".
	Письмо формируется на основе шаблона: woocommerce/emails/admin-completed-order.php
*/

// Email для отправки уведомления администратору — задаётся в виде строки
$moveat_admin_notification_email = 'moveat.expert@gmail.com';

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/*
	Формирует HTML письма по шаблону и отправляет уведомление администраторам.
	Также сохраняет локальную HTML-копию письма в wp-content для проверки.
*/
function moveat_send_admin_completed_order_email( $order_id ) {
	if ( ! $order_id ) {
		return;
	}

	// Получаем объект заказа
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Рендерим шаблон письма из темы
	ob_start();
	wc_get_template( 'emails/admin-completed-order.php', array( 'order' => $order ), '', get_template_directory() . '/woocommerce/' );
	$html = ob_get_clean();

	// Сохраняем локальную копию HTML-письма для отладки/проверки
	$file = WP_CONTENT_DIR . "/admin-completed-order-{$order_id}.html";
	@file_put_contents( $file, $html );

	// Берём строковый email из глобальной переменной и формируем массив для wp_mail
	global $moveat_admin_notification_email;
	$recipients = array( $moveat_admin_notification_email );

	// Тема и заголовки письма
	$subject = sprintf( 'Заказ #%s выполнен', $order->get_order_number() );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Отправляем письмо
	wp_mail( $recipients, $subject, $html, $headers );
}

// Хук: при переводе заказа в статус completed вызываем отправку письма админам
add_action( 'woocommerce_order_status_completed', 'moveat_send_admin_completed_order_email', 10, 1 );

?>