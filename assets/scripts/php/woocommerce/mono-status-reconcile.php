<?php
/**
 * Mono (Plata by mono) — страховочная сверка статусов заказов.
 *
 * Зачем нужно:
 * Вебхук от Монобанка (PUSH) может не дойти / не отработать. В этом случае
 * оплаченный заказ остаётся в статусе pending/failed. Этот модуль независимо
 * от вебхука периодически САМ опрашивает API Монобанка (PULL,
 * GET /api/merchant/invoice/status) по «зависшим» заказам и приводит их
 * статус в соответствие с реальным статусом инвойса.
 *
 * Это именно страховочная сетка: основной путь (вебхук) остаётся как был,
 * cron лишь подбирает то, что вебхук пропустил.
 */

namespace Moveat\Woo\Mono;

defined( 'ABSPATH' ) || exit;

/**
 * Конфиг. ⚠ Значения, помеченные «подтвердить», нужно сверить с реальным
 * плагином Plata by mono на проде (см. инструкцию в конце разговора).
 */
const CONFIG = array(
	// ID платёжного шлюза monopay (Plata by mono) в WooCommerce.
	// Подтверждено: section=mono_gateway в настройках + $this->id плагина.
	// Используется и для фильтрации заказов, и для чтения merchant-токена
	// из настроек шлюза (woocommerce_mono_gateway_settings).
	'gateway_id'        => 'mono_gateway',

	// Запасные meta-ключи invoiceId. Основной источник — transaction_id
	// заказа (плагин сохраняет invoiceId через set_transaction_id()),
	// см. get_invoice_id(). Эти ключи — на случай нестандартного хранения.
	'invoice_meta_keys' => array( '_mono_invoice_id', 'mono_invoice_id', 'invoiceId', '_invoiceId' ),

	// За сколько часов назад проверять заказы (чтобы не сканировать всю базу).
	'lookback_hours'    => 48,

	// Какие статусы считаем «зависшими» и проверяем.
	'check_statuses'    => array( 'pending', 'failed', 'on-hold' ),

	// Сколько заказов обрабатывать за один прогон cron.
	'batch_size'        => 30,
);

const API_STATUS_URL = 'https://api.monobank.ua/api/merchant/invoice/status';
const CRON_HOOK       = 'moveat_mono_reconcile';

/**
 * Расписание проверок одного заказа (экспоненциальный backoff).
 * Значение по индексу = пауза в секундах ПЕРЕД проверкой №(индекс+1),
 * отсчёт от предыдущей проверки (для первой — от создания заказа).
 *
 * Схема: 1-я — сразу; 2-3 — каждые 3 мин; 4-6 — каждые 10 мин;
 * 7-9 — каждый час; 10-я — через сутки. Всего MAX_CHECKS проверок.
 */
const CHECK_SCHEDULE = array(
	0,            // проверка 1 — сразу
	3 * 60,       // проверка 2 — через 3 мин
	3 * 60,       // проверка 3 — через 3 мин
	10 * 60,      // проверка 4 — через 10 мин
	10 * 60,      // проверка 5 — через 10 мин
	10 * 60,      // проверка 6 — через 10 мин
	60 * 60,      // проверка 7 — через 1 час
	60 * 60,      // проверка 8 — через 1 час
	60 * 60,      // проверка 9 — через 1 час
	24 * 60 * 60, // проверка 10 — через 24 часа
);
const MAX_CHECKS = 10; // = count( CHECK_SCHEDULE )

// Мета-ключи служебных счётчиков троттлинга.
const META_ATTEMPTS = '_mono_check_attempts';
const META_LAST     = '_mono_last_check';

// Логирование.
const LOG_ENABLED   = true;            // главный тумблер записи в файл лога
const LOG_VERBOSE   = false;           // подробный режим (пропуски по троттлингу и т.п.)
const LOG_MAX_BYTES = 5 * 1024 * 1024; // лимит размера файла (5 МБ); при превышении — ротация

/**
 * Путь к отдельному файлу лога скрипта:
 * wp-content/themes/<тема>/assets/logs/mono-reconcile.log
 *
 * При первом обращении создаёт папку и защищает её от доступа извне
 * (.htaccess + index.php), т.к. папка лежит внутри веб-доступной темы.
 *
 * @return string|null Полный путь к файлу или null, если папку создать не удалось.
 */
function get_log_file() {
	$dir = get_template_directory() . '/assets/logs';

	if ( ! is_dir( $dir ) ) {
		if ( ! wp_mkdir_p( $dir ) ) {
			return null;
		}
		// Защита от прямого скачивания логов по URL (Apache 2.4 и 2.2).
		@file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
		@file_put_contents( $dir . '/index.php', '<?php // Silence is golden.' );
	}

	return $dir . '/mono-reconcile.log';
}

/**
 * Пишет строку в отдельный файл лога скрипта (не зависит от WP_DEBUG).
 *
 * @param string $message Текст.
 * @param bool   $verbose Если true — пишется только при включённом LOG_VERBOSE.
 */
function log_line( $message, $verbose = false ) {
	if ( ! LOG_ENABLED ) {
		return;
	}
	if ( $verbose && ! LOG_VERBOSE ) {
		return;
	}

	$file = get_log_file();
	if ( null === $file ) {
		return;
	}

	// Простая ротация: при превышении лимита переносим текущий лог в .old.
	if ( is_file( $file ) && filesize( $file ) > LOG_MAX_BYTES ) {
		@rename( $file, $file . '.old' );
	}

	error_log(
		'[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL,
		3,
		$file
	);
}

/* -------------------------------------------------------------------------
 * 1. Планировщик
 * ---------------------------------------------------------------------- */

// Свой интервал — каждые 3 минуты. Это «разрешающая способность» планировщика:
// сам тик частый, а реальную частоту проверки каждого заказа задаёт throttle
// (CHECK_SCHEDULE). 3 мин = самый мелкий шаг расписания проверок.
add_filter( 'cron_schedules', function ( $schedules ) {
	if ( ! isset( $schedules['moveat_three_minutes'] ) ) {
		$schedules['moveat_three_minutes'] = array(
			'interval' => 3 * MINUTE_IN_SECONDS,
			'display'  => 'Каждые 3 минуты (Mono reconcile)',
		);
	}
	return $schedules;
} );

add_action( 'after_setup_theme', function () {
	if ( ! wp_next_scheduled( CRON_HOOK ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'moveat_three_minutes', CRON_HOOK );
	}
} );

// На случай переключения темы — снимаем задачу.
add_action( 'switch_theme', function () {
	$timestamp = wp_next_scheduled( CRON_HOOK );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, CRON_HOOK );
	}
} );

add_action( CRON_HOOK, __NAMESPACE__ . '\\reconcile_pending_orders' );

/* -------------------------------------------------------------------------
 * 2. Основная сверка
 * ---------------------------------------------------------------------- */

/**
 * Находит зависшие mono-заказы и сверяет их статус с API Монобанка.
 */
function reconcile_pending_orders() {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return;
	}

	$token = get_merchant_token();
	if ( empty( $token ) ) {
		log_line( 'merchant token не найден — сверка пропущена' );
		return;
	}

	$orders = wc_get_orders( array(
		'limit'        => CONFIG['batch_size'],
		'status'       => CONFIG['check_statuses'],
		'payment_method' => CONFIG['gateway_id'],
		'date_created' => '>' . ( time() - CONFIG['lookback_hours'] * HOUR_IN_SECONDS ),
		'orderby'      => 'date',
		'order'        => 'DESC',
		'return'       => 'objects',
	) );

	if ( empty( $orders ) ) {
		return;
	}

	log_line( 'tick: найдено ' . count( $orders ) . ' заказ(ов) в работе' );

	foreach ( $orders as $order ) {
		try {
			reconcile_single_order( $order, $token );
		} catch ( \Throwable $e ) {
			log_line( 'ошибка заказа #' . $order->get_id() . ': ' . $e->getMessage() );
		}
	}
}

/**
 * Сверяет один заказ.
 *
 * @param \WC_Order $order
 * @param string    $token
 * @param bool      $force  true — игнорировать троттлинг (ручная проверка).
 */
function reconcile_single_order( $order, $token, $force = false ) {
	$invoice_id = get_invoice_id( $order );
	if ( empty( $invoice_id ) ) {
		return; // нет invoiceId — нечего проверять
	}

	$order_id = $order->get_id();
	$attempts = (int) $order->get_meta( META_ATTEMPTS );

	// Троттлинг: при автоматической сверке проверяем заказ строго по расписанию
	// (CHECK_SCHEDULE) и не больше MAX_CHECKS раз. Ручная кнопка ($force) — мимо.
	if ( ! $force && ! is_due_for_check( $order ) ) {
		log_line( "#{$order_id}: пропуск — рано или лимит (попытка {$attempts}/" . MAX_CHECKS . ')', true );
		return;
	}

	$status = fetch_invoice_status( $invoice_id, $token );

	// Считаем попытку только при автоматической сверке (ручные не жгут лимит).
	if ( ! $force ) {
		record_check_attempt( $order );
	}

	$mode = $force ? 'вручную' : 'попытка ' . ( $attempts + 1 ) . '/' . MAX_CHECKS;
	log_line( sprintf(
		'#%d: invoice %s, %s, статус API=%s, статус заказа=%s',
		$order_id,
		$invoice_id,
		$mode,
		( null === $status ? 'нет ответа' : $status ),
		$order->get_status()
	) );

	if ( null === $status ) {
		return; // сетевая ошибка / нет ответа — попробуем в следующий прогон
	}

	switch ( $status ) {
		case 'success':
			// Оплачено в Монобанке, но заказ ещё не оплачен у нас.
			if ( ! $order->is_paid() && ! $order->has_status( array( 'processing', 'completed' ) ) ) {
				$order->add_order_note( sprintf(
					'Mono-сверка (cron): инвойс %s оплачен (success), но вебхук не дошёл. Заказ помечен как оплаченный автоматически.',
					$invoice_id
				) );
				$order->payment_complete( $invoice_id );
				log_line( "#{$order_id}: success → payment_complete" );
			}
			break;

		case 'reversed':
			$order->add_order_note( sprintf( 'Mono-сверка (cron): инвойс %s возвращён (reversed). Проверьте возврат вручную.', $invoice_id ) );
			log_line( "#{$order_id}: reversed (требует ручной проверки)" );
			break;

		case 'failure':
		case 'expired':
			// Реально не оплачен — оставляем failed, без лишнего шума.
			if ( ! $order->has_status( 'failed' ) ) {
				$order->update_status( 'failed', sprintf( 'Mono-сверка (cron): инвойс %s — %s.', $invoice_id, $status ) );
			}
			break;

		// created / processing / hold — ещё в процессе, ничего не трогаем.
		default:
			break;
	}
}

/* -------------------------------------------------------------------------
 * 3. Обращение к API Монобанка
 * ---------------------------------------------------------------------- */

/**
 * GET /api/merchant/invoice/status?invoiceId=...
 *
 * @return string|null Статус инвойса или null при ошибке.
 */
function fetch_invoice_status( $invoice_id, $token ) {
	$response = wp_remote_get(
		add_query_arg( 'invoiceId', rawurlencode( $invoice_id ), API_STATUS_URL ),
		array(
			'headers' => array( 'X-Token' => $token ),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		log_line( 'API ошибка для invoice ' . $invoice_id . ': ' . $response->get_error_message() );
		return null;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! is_array( $body ) || empty( $body['status'] ) ) {
		log_line( "API код {$code} для invoice {$invoice_id}: " . wp_remote_retrieve_body( $response ) );
		return null;
	}

	return (string) $body['status'];
}

/* -------------------------------------------------------------------------
 * 4. Хелперы поиска токена и invoiceId
 * ---------------------------------------------------------------------- */

/**
 * Merchant-токен: сначала из константы (wp-config.php), затем из настроек шлюза.
 */
function get_merchant_token() {
	if ( defined( 'MONO_MERCHANT_TOKEN' ) && MONO_MERCHANT_TOKEN ) {
		return MONO_MERCHANT_TOKEN;
	}

	$settings = get_option( 'woocommerce_' . CONFIG['gateway_id'] . '_settings' );
	if ( is_array( $settings ) ) {
		// monopay хранит токен под ключом API_KEY; остальные — на всякий случай.
		foreach ( array( 'API_KEY', 'token', 'mono_token', 'api_token', 'x_token' ) as $key ) {
			if ( ! empty( $settings[ $key ] ) ) {
				return $settings[ $key ];
			}
		}
	}

	return '';
}

/**
 * invoiceId заказа.
 *
 * Плагин monopay сохраняет invoiceId в штатный transaction_id заказа
 * (set_transaction_id), поэтому это основной источник. Мета-ключи —
 * запасной вариант на случай нестандартного хранения.
 */
function get_invoice_id( $order ) {
	$transaction_id = $order->get_transaction_id();
	if ( ! empty( $transaction_id ) ) {
		return $transaction_id;
	}

	foreach ( CONFIG['invoice_meta_keys'] as $key ) {
		$value = $order->get_meta( $key );
		if ( ! empty( $value ) ) {
			return $value;
		}
	}
	return '';
}

/**
 * Пора ли автоматически проверять заказ (по расписанию backoff).
 *
 * @return bool false — лимит исчерпан или ещё не наступила пауза.
 */
function is_due_for_check( $order ) {
	$attempts = (int) $order->get_meta( META_ATTEMPTS );

	// Лимит проверок исчерпан — больше не дёргаем.
	if ( $attempts >= MAX_CHECKS ) {
		return false;
	}

	$required_wait = CHECK_SCHEDULE[ $attempts ];

	// Базовая точка отсчёта: время последней проверки, а для первой — создание заказа.
	$last = (int) $order->get_meta( META_LAST );
	if ( 0 === $last ) {
		$created = $order->get_date_created();
		$last    = $created ? $created->getTimestamp() : 0;
	}

	return ( time() - $last ) >= $required_wait;
}

/**
 * Фиксирует факт проверки: +1 к счётчику и метка времени.
 * При достижении лимита оставляет заметку администратору.
 */
function record_check_attempt( $order ) {
	$attempts = (int) $order->get_meta( META_ATTEMPTS ) + 1;
	$order->update_meta_data( META_ATTEMPTS, $attempts );
	$order->update_meta_data( META_LAST, time() );
	$order->save();

	if ( $attempts >= MAX_CHECKS ) {
		$order->add_order_note( sprintf(
			'Mono-сверка (cron): достигнут лимит проверок (%d). Статус автоматически не подтверждён — нужна ручная проверка.',
			MAX_CHECKS
		) );
	}
}

/* -------------------------------------------------------------------------
 * 5. Ручная кнопка в админке заказа (PULL по требованию)
 * ---------------------------------------------------------------------- */

// Добавляет действие «Перевірити статус у mono» в выпадающий список действий заказа.
add_filter( 'woocommerce_order_actions', function ( $actions ) {
	$actions['moveat_mono_check'] = 'Перевірити статус оплати у mono';
	return $actions;
} );

add_action( 'woocommerce_order_action_moveat_mono_check', function ( $order ) {
	$token = get_merchant_token();
	if ( empty( $token ) ) {
		$order->add_order_note( 'Mono-сверка (вручную): merchant token не настроен.' );
		return;
	}
	reconcile_single_order( $order, $token, true ); // true — мимо троттлинга
	$order->add_order_note( 'Mono-сверка (вручную): запрос статуса выполнен.' );
} );
