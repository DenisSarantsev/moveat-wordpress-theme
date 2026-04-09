<?php

/* Настройки технического режима и логика редиректа. */
function moveat_customize_maintenance_mode( $wp_customize ) {
	$wp_customize->add_section(
		'moveat_maintenance_section',
		[
			'title'    => __( 'Техническое обслуживание', 'moveat' ),
			'priority' => 160,
		]
	);

	$wp_customize->add_setting(
		'moveat_maintenance_mode_enabled',
		[
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		]
	);

	$wp_customize->add_control(
		'moveat_maintenance_mode_enabled',
		[
			'section' => 'moveat_maintenance_section',
			'label'   => __( 'Включить страницу технического обслуживания для посетителей', 'moveat' ),
			'type'    => 'checkbox',
		]
	);
}
add_action( 'customize_register', 'moveat_customize_maintenance_mode' );

/*
 * Режим технического обслуживания: при включении рендерим фиксированный
 * шаблон `templates/maintenance.php` и возвращаем HTTP 503. Никакого
 * взаимодействия с WP-страницей не требуется.
 */

/* Перенаправляет посетителей на страницу техрежима, если режим включен. */
function moveat_handle_maintenance_mode() {
	if ( ! get_theme_mod( 'moveat_maintenance_mode_enabled', false ) ) {
		return;
	}

	// Разрешаем администраторам продолжать работу без редиректа.
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( false !== strpos( $request_uri, 'wp-login.php' ) || false !== strpos( $request_uri, 'xmlrpc.php' ) ) {
		return;
	}

	// Отдаём статус 503 и отключаем кэширование
	status_header( 503 );
	nocache_headers();

	// Подключаем фиксированный шаблон темы, если он есть.
	$maintenance_template = locate_template( 'templates/maintenance.php' );
	if ( $maintenance_template ) {
		include $maintenance_template;
		exit;
	}

	// Если шаблон не найден — отдаём минимальный 503 HTML.
	echo '<!doctype html><html><head><meta charset="utf-8"><title>Технические работы</title></head><body><h1>Сайт временно недоступен</h1><p>Мы проводим технические работы. Попробуйте зайти позже.</p></body></html>';
	exit;
}
add_action( 'template_redirect', 'moveat_handle_maintenance_mode' );

/* Отключает подмену страниц режимом Coming Soon в WooCommerce. */
function moveat_exclude_woocommerce_coming_soon( $exclude ) {
	return true;
}
add_filter( 'woocommerce_coming_soon_exclude', 'moveat_exclude_woocommerce_coming_soon' );
