<?php
/**
 * Tallanto CRM — общий клиент (ядро интеграции).
 *
 * Здесь живёт всё, что не зависит от конкретного источника данных и может
 * переиспользоваться любыми синхронизациями с Tallanto (WooCommerce, формы и т.д.):
 *   - подпись и отправка запроса в entryPoint=dataCapture;
 *   - получение подписного ключа;
 *   - логирование.
 *
 * Формат dataCapture (НЕ JSON/REST):
 *   POST {CRM_URL}/index.php?entryPoint=dataCapture&module={Module}
 *   тело application/x-www-form-urlencoded, подпись crc = md5(значения_по_алфавиту . ключ).
 * Документация: /Users/webriter/Downloads/Moveat.html
 */

namespace Moveat\Tallanto;

defined( 'ABSPATH' ) || exit;

const CRM_URL = 'https://moveat.tallanto.com';

// Логирование.
const LOG_ENABLED   = true;
const LOG_MAX_BYTES = 5 * 1024 * 1024;

/**
 * Подписной ключ dataCapture: сначала из wp-config (MOVEAT_TALLANTO_KEY),
 * иначе — значение из документации.
 *
 * @return string
 */
function get_key() {
	if ( defined( 'MOVEAT_TALLANTO_KEY' ) && MOVEAT_TALLANTO_KEY ) {
		return MOVEAT_TALLANTO_KEY;
	}
	return '7c93dddc5da232b2a0f8d1e6c29e8f035fd1275e';
}

/**
 * Путь к файлу лога: wp-content/themes/<тема>/assets/logs/tallanto.log.
 * При первом обращении создаёт защищённый от внешнего доступа каталог.
 *
 * @return string|null
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

	return $dir . '/tallanto.log';
}

/**
 * Пишет строку в лог интеграции (с простой ротацией по размеру).
 *
 * @param string $message
 */
function log_line( $message ) {
	if ( ! LOG_ENABLED ) {
		return;
	}

	$file = get_log_file();
	if ( null === $file ) {
		return;
	}

	if ( is_file( $file ) && filesize( $file ) > LOG_MAX_BYTES ) {
		@rename( $file, $file . '.old' );
	}

	error_log(
		'[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL,
		3,
		$file
	);
}

/**
 * Отправляет данные в Tallanto через entryPoint=dataCapture.
 *
 * Подпись воспроизводит документацию: сортировка ключей через strcasecmp,
 * конкатенация значений (вложенные массивы дают литерал 'Array'),
 * crc = md5(конкатенация . ключ).
 *
 * @param string $module Модуль Tallanto (напр. 'Contact').
 * @param array  $params Параметры записи.
 * @return array|null Декодированный ответ { result, message, id, duplicate } или null при сбое.
 */
function send( $module, array $params ) {
	$url = CRM_URL . '/index.php?entryPoint=dataCapture&module=' . rawurlencode( $module );

	uksort( $params, 'strcasecmp' );

	$values = '';
	foreach ( $params as $value ) {
		if ( is_array( $value ) && count( $value ) === 0 ) {
			continue;
		}
		$values .= is_array( $value ) ? 'Array' : $value;
	}

	$params['crc'] = md5( $values . get_key() );

	$response = wp_remote_post( $url, array(
		'headers' => array( 'Content-type' => 'application/x-www-form-urlencoded' ),
		'body'    => http_build_query( $params ),
		'timeout' => 20,
	) );

	if ( is_wp_error( $response ) ) {
		log_line( 'HTTP-ошибка [' . $module . ']: ' . $response->get_error_message() );
		return null;
	}

	$body   = wp_remote_retrieve_body( $response );
	$result = json_decode( $body, true );

	if ( ! is_array( $result ) ) {
		log_line( "Некорректный ответ [{$module}], код " . wp_remote_retrieve_response_code( $response ) . ': ' . $body );
		return null;
	}

	if ( empty( $result['result'] ) ) {
		log_line( "Ошибка CRM [{$module}]: " . ( $result['message'] ?? '—' ) );
		return $result;
	}

	log_line( sprintf(
		'OK [%s]: id=%s, duplicate=%s, msg=%s',
		$module,
		$result['id'] ?? '—',
		! empty( $result['duplicate'] ) ? 'yes' : 'no',
		$result['message'] ?? '—'
	) );

	return $result;
}
