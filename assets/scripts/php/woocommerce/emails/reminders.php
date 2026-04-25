<?php
/*
	Отправляем пользователям напоминания о том, что нужно оплатить заказ: 
	через 10 мин и через 24 часа

	Отправляем письмо менеджерам, что польователю было отправлено напоминание
*/

// Если файл подключается несколько раз — не выполнять повторно.
if ( defined( 'MOVEAT_EMAILS_PHP_LOADED' ) ) {
    return;
}
define( 'MOVEAT_EMAILS_PHP_LOADED', true );

// Время задержки для напоминаний (в секундах). Можно менять при необходимости.
$moveat_reminder_delay_first  = 600;   // 10 минут
$moveat_reminder_delay_second = 86400; // 24 часа

/*
	Запланировать напоминание об оплате через 10 минут после оформления заказа.
	Простыми словами: как только клиент оформил заказ и он ждёт оплаты — мы создаём задачу,
	которая сработает через 10 минут и попытается напомнить клиенту оплатить.
*/
function moveat_schedule_payment_reminder( $order_id, $posted ) {
    // scheduling entry — logging removed
    // Берём объект заказа по его ID — дальше будем оперировать им.
    $order = wc_get_order( $order_id );

    // Если заказа нет — ничего не делаем.
    if ( ! $order ) {
        return;
    }

    // Проверяем сумму и статус заказа. Раньше мы пропускали заказ, если сумма 0,
    // но чтобы не терять заказы, созданные пустыми и затем заполненные, теперь
    // просто логируем total==0 и продолжаем. При этом не планируем напоминания
    // для окончательных статусов (processing/completed/cancelled/refunded).
    $order_total = method_exists( $order, 'get_total' ) ? (float) $order->get_total() : 0.0;
    $order_status = method_exists( $order, 'get_status' ) ? $order->get_status() : '';
    if ( $order_total <= 0 ) {
            // total is zero; still attempt to schedule — no debug logging
        // Также делаем подробный дамп для диагностики — полезно на проде при отладке.
        $items_dump = array();
        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $items_dump[] = array(
                'item_id' => $item_id,
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'qty' => $item->get_quantity(),
                'line_subtotal' => $item->get_subtotal(),
                'line_total' => $item->get_total(),
                'tax' => method_exists($item, 'get_subtotal_tax') ? $item->get_subtotal_tax() : null,
                'sku' => $product ? $product->get_sku() : null,
            );
        }
        $coupons = $order->get_used_coupons();
        $order_meta = print_r( get_post_meta( $order_id ), true );
        $order_data = method_exists( $order, 'get_data' ) ? $order->get_data() : null;
        $billing = array(
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
        );
        $dump = "ORDER DUMP for order={$order_id}:\n";
        $dump .= sprintf("status=%s total=%s subtotal=%s discount=%s payment_method=%s is_paid=%s\n", $order_status, $order_total, $order->get_subtotal(), $order->get_discount_total(), $order->get_payment_method(), ( method_exists( $order, 'is_paid' ) ? ( $order->is_paid() ? 'yes' : 'no' ) : 'unknown' ) );
        $dump .= "billing=" . print_r( $billing, true ) . "\n";
        $dump .= "items=" . print_r( $items_dump, true ) . "\n";
        $dump .= "coupons=" . print_r( $coupons, true ) . "\n";
        $dump .= "order_meta=" . $order_meta . "\n";
        $dump .= "order_data=" . print_r( $order_data, true ) . "\n";
            // detailed dump removed to avoid verbose logging
    }

    // Не планируем напоминание для окончательных статусов
    if ( in_array( $order_status, array( 'processing', 'completed', 'cancelled', 'refunded' ), true ) ) {
        return;
    }

    // Оставляем напоминание для заказов со статусом pending даже если needs_payment() вернул false
    $needs_payment_flag = true;
    if ( method_exists( $order, 'needs_payment' ) ) {
        $needs_payment_flag = (bool) $order->needs_payment();
    }
    if ( ! $needs_payment_flag && $order_status !== 'pending' ) {
        return;
    }

    // Используем переменные задержки, объявленные в файле (см. сверху).
    global $moveat_reminder_delay_first, $moveat_reminder_delay_second;

    // Рассчитываем метки времени, когда должны сработать напоминания.
    $send_at_1 = time() + (int) $moveat_reminder_delay_first;
    $send_at_2 = time() + (int) $moveat_reminder_delay_second;

    // Передаём в задачу минимальные данные: ID заказа и номер попытки (attempt).
    $args1 = array( 'order_id' => $order_id, 'attempt' => 1 ); // первое напоминание
    $args2 = array( 'order_id' => $order_id, 'attempt' => 2 ); // второе напоминание

    // Если есть Action Scheduler — используем его, это надёжнее WP-Cron.
    if ( function_exists( 'as_schedule_single_action' ) ) {
            // using Action Scheduler
        // Планируем первое напоминание в $send_at_1, если ещё не запланировано.
        if ( ! as_next_scheduled_action( 'moveat_send_payment_reminder', $args1 ) ) {
            as_schedule_single_action( $send_at_1, 'moveat_send_payment_reminder', $args1, 'moveat_reminders' );
                // action scheduled (attempt=1)
        }
        // Планируем второе напоминание в $send_at_2, если ещё не запланировано.
        if ( ! as_next_scheduled_action( 'moveat_send_payment_reminder', $args2 ) ) {
            as_schedule_single_action( $send_at_2, 'moveat_send_payment_reminder', $args2, 'moveat_reminders' );
                // action scheduled (attempt=2)
        }
    } else {
        // WP-Cron fallback: планируем по аналогии, избегая дубликатов.
            // using WP-Cron fallback
        if ( ! wp_next_scheduled( 'moveat_send_payment_reminder', $args1 ) ) {
            wp_schedule_single_event( $send_at_1, 'moveat_send_payment_reminder', $args1 );
                // wp cron scheduled (attempt=1)
        }
        if ( ! wp_next_scheduled( 'moveat_send_payment_reminder', $args2 ) ) {
            wp_schedule_single_event( $send_at_2, 'moveat_send_payment_reminder', $args2 );
                // wp cron scheduled (attempt=2)
        }
    }
}
// Говорим WP: вызывать эту функцию, когда заказ оформлен (хук WooCommerce после оформления заказа).
add_action( 'woocommerce_checkout_order_processed', 'moveat_schedule_payment_reminder', 10, 2 );

// Дополнительные временные хуки для отладки — чтобы поймать разные сценарии создания заказа.
add_action( 'woocommerce_new_order', 'moveat_schedule_payment_reminder_on_new_order', 10, 1 );
add_action( 'woocommerce_thankyou', 'moveat_schedule_payment_reminder_on_thankyou', 10, 1 );

function moveat_schedule_payment_reminder_on_new_order( $order_id ) {
    // new_order hook fired — no debug logging
    // Логирование тела запроса удалено
    $raw = file_get_contents( 'php://input' );
    $raw_snip = $raw ? ( strlen( $raw ) > 2000 ? substr( $raw, 0, 2000 ) . '...[truncated]' : $raw ) : '';
    // Вызов оригинальной функции (второй параметр может отсутствовать)
    moveat_schedule_payment_reminder( $order_id, array() );
}

function moveat_schedule_payment_reminder_on_thankyou( $order_id ) {
    // thankyou hook fired — no debug logging
    moveat_schedule_payment_reminder( $order_id, array() );
}

// Триггер: если заказ сначала создаётся пустым (REST/API), а потом обновляется —
// при сохранении заказа попробуем запланировать напоминание, если теперь есть total>0.
add_action( 'save_post_shop_order', 'moveat_maybe_schedule_on_order_save', 20, 3 );

function moveat_maybe_schedule_on_order_save( $post_id, $post, $update ) {
    // Игнорируем автосохранения и ревизии
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    // Берём заказ
    $order = wc_get_order( $post_id );
    if ( ! $order ) {
        return;
    }

    $order_total = method_exists( $order, 'get_total' ) ? (float) $order->get_total() : 0.0;
    $order_status = method_exists( $order, 'get_status' ) ? $order->get_status() : '';

    // save_post_shop_order fired — no debug logging
    if ( $order_total <= 0 ) {
        // total<=0 — still attempt scheduling
    }

    // Проверяем, не запланировано ли уже
    $args = array( 'order_id' => $post_id, 'attempt' => 1 );
    if ( function_exists( 'as_next_scheduled_action' ) ) {
        if ( as_next_scheduled_action( 'moveat_send_payment_reminder', $args ) ) {
            return;
        }
    } else {
        if ( wp_next_scheduled( 'moveat_send_payment_reminder', $args ) ) {
            return;
        }
    }
    // Если всё ок — вызываем планировщик напрямую (он сам проверит needs_payment/status/etc.)
    moveat_schedule_payment_reminder( $post_id, array() );
}


/*
	Удалить запланированное напоминание, если заказ изменил статус (например, оплачен).
	Простыми словами: если заказ оплатили или его статус стал таким, что напоминание не нужно — удаляем задачу.
*/
function moveat_unschedule_payment_reminder_for_order( $order_id ) {
    // unschedule called — no debug logging
    // Формируем наборы аргументов для обеих попыток, чтобы удалить их обе.
    $args1 = array( 'order_id' => $order_id, 'attempt' => 1 );
    $args2 = array( 'order_id' => $order_id, 'attempt' => 2 );

    // Если есть Action Scheduler — удаляем все действия с нашим hook и аргументами.
    if ( function_exists( 'as_unschedule_all_actions' ) ) {
        as_unschedule_all_actions( 'moveat_send_payment_reminder', $args1 );
        as_unschedule_all_actions( 'moveat_send_payment_reminder', $args2 );
    } else {
        // Иначе пробуем убрать события WP-Cron для обоих аргументов.
        wp_clear_scheduled_hook( 'moveat_send_payment_reminder', $args1 );
        wp_clear_scheduled_hook( 'moveat_send_payment_reminder', $args2 );
        // wp_clear_scheduled_hook called — no debug logging
    }
}

// Следим за изменением статуса заказа: если он перешёл в обработку/завершён/отменён — удаляем напоминание.
function moveat_handle_order_status_change( $order_id, $old_status, $new_status, $order ) {
    // Простыми словами: если новый статус означает, что напоминание больше не нужно — убираем его.
    if ( in_array( $new_status, array( 'processing', 'completed', 'cancelled', 'refunded' ), true ) ) {
        moveat_unschedule_payment_reminder_for_order( $order_id );
    }
}
add_action( 'woocommerce_order_status_changed', 'moveat_handle_order_status_change', 10, 4 );
// На всякий случай: если оплата завершилась, тоже удаляем напоминание.
add_action( 'woocommerce_payment_complete', 'moveat_unschedule_payment_reminder_for_order' );


/*
 	Хендлер: отправка напоминания об оплате.
	Простыми словами: когда запланированное действие срабатывает — эта функция формирует письмо
	на основе вашего шаблона `woocommerce/emails/customer-remind-about-payment.php` и отправляет его клиенту.
*/
function moveat_send_payment_reminder_handler( $args ) {
    // Action Scheduler передаёт аргументы как массив, WP-Cron — иногда как отдельные параметры.
    // Тут мы нормализуем вход и получаем order_id и номер попытки (attempt).
    $attempt = 1; // по умолчанию первая попытка
    if ( is_array( $args ) ) {
        $order_id = isset( $args['order_id'] ) ? absint( $args['order_id'] ) : 0;
        $attempt = isset( $args['attempt'] ) ? absint( $args['attempt'] ) : 1;
    } else {
        $order_id = absint( $args );
    }

    // handler called — no debug logging

    // Берём объект заказа — если нет, логируем и выходим.
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Если заказ уже оплачен — напоминать не нужно. Логируем состояние.
    $needs_payment_state = method_exists( $order, 'needs_payment' ) ? ( $order->needs_payment() ? 'yes' : 'no' ) : 'unknown';
    // Дополнительная проверка статуса заказа: если он перешёл в обработку/завершён/отменён/возвращён — отменяем отправку.
    $order_status = method_exists( $order, 'get_status' ) ? $order->get_status() : '';
    if ( in_array( $order_status, array( 'processing', 'completed', 'cancelled', 'refunded' ), true ) ) {
        if ( function_exists( 'moveat_unschedule_payment_reminder_for_order' ) ) {
            moveat_unschedule_payment_reminder_for_order( $order_id );
        }
        return;
    }

    if ( $needs_payment_state !== 'yes' ) {
        return;
    }

    // Формируем HTML контент письма, используя ваш шаблон в теме:
    // путь: wp-content/themes/ваша-тема/woocommerce/emails/customer-remind-about-payment.php
    ob_start();
    wc_get_template( 'emails/customer-remind-about-payment.php', array( 'order' => $order ), '', get_template_directory() . '/woocommerce/' );
    $html = ob_get_clean();

    // Получаем email покупателя и тему письма. В теме указываем номер попытки, если это повтор.
    $to = $order->get_billing_email();
    $subject = sprintf( 'Напоминание об оплате заказа #%s', $order->get_order_number() );
    if ( $attempt > 1 ) {
        $subject .= ' (повторное напоминание)';
    }

    // Заголовки — указать, что отправляем HTML.
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    // Сохраняем HTML письма локально для проверки (prod-safe копия)
    $customer_file = WP_CONTENT_DIR . "/reminder-order-{$order_id}-customer.html";
    file_put_contents( $customer_file, $html );
    $sent_customer = wp_mail( $to, $subject, $html, $headers );

    // Отправляем уведомление менеджерам на фиксированный адрес с отдельным шаблоном.
		$managers_email = array('moveat.expert@gmail.com', 'notifications.moveat@gmail.com');
    // Собираем HTML для менеджеров (используем свой шаблон в теме).
    ob_start();
    wc_get_template( 'emails/managers-remind-about-payment.php', array( 'order' => $order ), '', get_template_directory() . '/woocommerce/' );
    $managers_html = ob_get_clean();

    // Тема для менеджеров: укажем что это уведомление о напоминании клиенту.
    $managers_subject = sprintf( 'Пользователю отправлено напоминание об оплате заказа #%s', $order->get_order_number() );
    if ( $attempt > 1 ) {
        $managers_subject .= ' (повторное напоминание)';
    }

    // Сохраняем HTML менеджерского письма локально
    $managers_file = WP_CONTENT_DIR . "/reminder-order-{$order_id}-managers.html";
    file_put_contents( $managers_file, $managers_html );
    $sent_managers = wp_mail( $managers_email, $managers_subject, $managers_html, $headers );
}

// Привязываем наш хендлер к событию, которое мы использовали при планировании.
add_action( 'moveat_send_payment_reminder', 'moveat_send_payment_reminder_handler' );

?>
