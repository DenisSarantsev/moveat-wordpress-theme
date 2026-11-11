<?php
/**
 * Журнал попыток оплаты: запись в assets/logs/payments.log.
 *
 * Одна строка — одно событие:
 * [2026-11-11 12:00:00] #4814 mono webhook status=failure errCode=59 reason="…"
 */

namespace Moveat\PaymentLogs;

defined( 'ABSPATH' ) || exit;

const LOG_ENABLED   = true;            // главный тумблер записи в файл
const LOG_MAX_BYTES = 5 * 1024 * 1024; // при превышении текущий лог уходит в .old
const VALUE_MAX_LEN = 300;             // длинные значения (тексты ошибок) обрезаем

// Префикс наших заметок в заказе — чтобы не логировать их повторно.
const NOTE_PREFIX = 'Лог оплаты:';

// Счётчик попыток оплаты заказа (каждое нажатие «Оплатить»).
const META_ATTEMPTS = '_moveat_pay_attempts';

/**
 * Путь к файлу журнала. Папка assets/logs закрыта от доступа по URL
 * (та же защита, что у mono-сверки).
 *
 * @return string|null Путь или null, если папку создать не удалось.
 */
function get_log_file() {
	$dir = get_template_directory() . '/assets/logs';

	if ( ! is_dir( $dir ) ) {
		if ( ! wp_mkdir_p( $dir ) ) {
			return null;
		}
		@file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
		@file_put_contents( $dir . '/index.php', '<?php // Silence is golden.' );
	}

	return $dir . '/payments.log';
}

/**
 * Пишет событие в журнал.
 *
 * @param int    $order_id Номер заказа (0, если неизвестен).
 * @param string $gateway  Короткое имя шлюза: mono, paypal, instalments или ID шлюза.
 * @param string $event    Событие: attempt, webhook, reconcile, failed, note.
 * @param array  $data     Поля key => value; пустые значения пропускаются.
 */
function log_event( $order_id, $gateway, $event, array $data = array() ) {
	if ( ! LOG_ENABLED ) {
		return;
	}

	$file = get_log_file();
	if ( null === $file ) {
		return;
	}

	$parts = array(
		'[' . gmdate( 'Y-m-d H:i:s' ) . ']',
		$order_id ? '#' . (int) $order_id : '#-',
		$gateway ? $gateway : '-',
		$event,
	);

	foreach ( $data as $key => $value ) {
		$value = format_value( $value );
		if ( '' === $value ) {
			continue;
		}
		$parts[] = $key . '=' . $value;
	}

	if ( is_file( $file ) && filesize( $file ) > LOG_MAX_BYTES ) {
		@rename( $file, $file . '.old' );
	}

	error_log( implode( ' ', $parts ) . PHP_EOL, 3, $file );
}

/**
 * Значение в одну строку: без переводов строк и HTML, с кавычками при пробелах.
 */
function format_value( $value ) {
	if ( is_bool( $value ) ) {
		return $value ? 'yes' : 'no';
	}
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value );
	}

	$value = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $value ) ) );
	if ( '' === $value ) {
		return '';
	}

	if ( mb_strlen( $value ) > VALUE_MAX_LEN ) {
		$value = mb_substr( $value, 0, VALUE_MAX_LEN ) . '…';
	}

	if ( preg_match( '/[\s"=]/u', $value ) ) {
		$value = '"' . str_replace( '"', '\'', $value ) . '"';
	}

	return $value;
}

/**
 * Короткое имя шлюза для журнала.
 */
function gateway_label( $payment_method ) {
	$payment_method = (string) $payment_method;

	if ( 'mono_gateway' === $payment_method ) {
		return 'mono';
	}
	if ( 0 === strpos( $payment_method, 'ppcp-' ) ) {
		return 'paypal';
	}
	if ( defined( 'MOVEAT_INSTALMENTS_GATEWAY' ) && MOVEAT_INSTALMENTS_GATEWAY === $payment_method ) {
		return 'instalments';
	}

	return $payment_method;
}

/**
 * Текущее число попыток оплаты заказа.
 */
function get_attempts( \WC_Order $order ) {
	return (int) $order->get_meta( META_ATTEMPTS );
}

/**
 * Увеличивает счётчик попыток и возвращает новый номер.
 */
function increment_attempts( \WC_Order $order ) {
	$attempts = get_attempts( $order ) + 1;
	$order->update_meta_data( META_ATTEMPTS, $attempts );
	$order->save_meta_data();

	return $attempts;
}

/**
 * IP посетителя (для разбора повторных попыток с разных устройств).
 */
function client_ip() {
	return class_exists( '\WC_Geolocation' ) ? \WC_Geolocation::get_ip_address() : '';
}
