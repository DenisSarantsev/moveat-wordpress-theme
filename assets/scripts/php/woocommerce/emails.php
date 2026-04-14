<?php
// Отправка клиентского письма при создании нового заказа.
// Письмо использует шаблон templates/emails/new-order.php в теме.

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Логируем загрузку файла для отладки (пишется при включённом WP_DEBUG)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( '[moveat-email] emails.php loaded' );
}

// Отправлять письмо клиенту сразу после создания заказа (надежнее, срабатывает при создании заказа)
add_action( 'woocommerce_checkout_order_processed', function( $order_id, $posted_data = array(), $order_arg = null ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[moveat-email] hook checkout_order_processed fired for order_id=' . intval( $order_id ) );
    }

    if ( ! $order_id ) {
        return;
    }

    // Если объект заказа передали третьим аргументом, используем его, иначе грузим
    if ( $order_arg instanceof WC_Order ) {
        $order = $order_arg;
    } else {
        $order = wc_get_order( $order_id );
    }

    if ( ! $order ) {
        return;
    }

    // Получаем email покупателя
    $to = $order->get_billing_email();
    if ( ! $to ) {
        return;
    }

    // Составляем ссылку на оплату (пример указан пользователем)
    $payment_url = esc_url_raw( home_url( '/order-pay/' ) . '?order_id=' . $order->get_id() . '&order_key=' . $order->get_order_key() );

    // Тема письма
    $subject = sprintf( 'Ваш заказ #%s на %s', $order->get_order_number(), parse_url( home_url(), PHP_URL_HOST ) );

    // Получаем HTML контент из шаблона темы
    // шаблон лежит в templates/emails/new-order.php
    $template_base = get_template_directory() . '/templates/';
    $template_name = 'emails/new-order.php';

    // Собираем данные для шаблона
    $args = array(
        'order'       => $order,
        'payment_url' => $payment_url,
    );

    // Отладка: логируем попытку отправки
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[moveat-email] try send new-order for order_id=' . $order_id . ' to=' . $to . ' payment_url=' . $payment_url );
    }

    // Используем wc_get_template_html для корректной подстановки шаблона
    $message = '';
    try {
        if ( function_exists( 'wc_get_template_html' ) ) {
            $message = wc_get_template_html( $template_name, $args, '', $template_base );
        } else {
            // fallback: простой буфер
            if ( file_exists( $template_base . $template_name ) ) {
                ob_start();
                extract( $args );
                include $template_base . $template_name;
                $message = ob_get_clean();
            }
        }
    } catch ( Exception $e ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[moveat-email] template render error: ' . $e->getMessage() );
        }
        return;
    }

    if ( empty( $message ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[moveat-email] empty message for order ' . $order_id );
        }
        return;
    }

    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    $sent = wp_mail( $to, $subject, $message, $headers );

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[moveat-email] wp_mail result for order_id=' . $order_id . ' sent=' . ( $sent ? '1' : '0' ) );
    }

}, 10, 3 );
