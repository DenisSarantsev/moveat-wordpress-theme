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

/* Возвращает ID страницы техрежима по назначенному шаблону. */
function moveat_get_maintenance_page_id() {
	$maintenance_page = get_posts(
		[
			'post_type'      => 'page',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'templates/maintenance.php',
		]
	);

	if ( ! empty( $maintenance_page ) ) {
		return (int) $maintenance_page[0];
	}

	return 0;
}

/* Возвращает URL страницы техрежима по шаблону, с запасным URL. */
function moveat_get_maintenance_page_url() {
	$maintenance_page_id = moveat_get_maintenance_page_id();
	if ( $maintenance_page_id > 0 ) {
		$page_url = get_permalink( $maintenance_page_id );
		if ( $page_url ) {
			return $page_url;
		}
	}

	return home_url( '/maintenance/' );
}

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

	$maintenance_page_id = moveat_get_maintenance_page_id();
	if ( $maintenance_page_id > 0 && is_page( $maintenance_page_id ) ) {
		status_header( 503 );
		nocache_headers();
		return;
	}

	$maintenance_url = moveat_get_maintenance_page_url();
	wp_safe_redirect( $maintenance_url, 302 );
	exit;
}
add_action( 'template_redirect', 'moveat_handle_maintenance_mode' );

/* Отключает подмену страниц режимом Coming Soon в WooCommerce. */
function moveat_exclude_woocommerce_coming_soon( $exclude ) {
	return true;
}
add_filter( 'woocommerce_coming_soon_exclude', 'moveat_exclude_woocommerce_coming_soon' );
