<?php

// ----------------------------------- Версия ресурса = время изменения файла
// Любой локальный CSS/JS подключаем с этой версией: каждое обновление файла меняет
// ?ver=… и заставляет браузер/CDN скачать новую версию (старая больше не залипает).
// Путь — относительно корня темы, напр. moveat_asset_ver( 'assets/scripts/js/woo.bundle.js' ).
function moveat_asset_ver( $relative_path ) {
	$file = get_template_directory() . '/' . ltrim( $relative_path, '/' );
	return file_exists( $file ) ? filemtime( $file ) : null;
}

// ----------------------------------- Подключение стилей и скриптов
function moveat_enqueue_scripts() {
	// Основные стили
	wp_enqueue_style( 'moveat-style', get_stylesheet_uri(), [], moveat_asset_ver( 'style.css' ) );

	// Основной бандл вёрстки/анимаций (сборка Vite-проекта; owl.carousel включён внутрь него — CDN не нужен).
	wp_enqueue_script( 'moveat-bundle', get_template_directory_uri() . '/main.js', [], moveat_asset_ver( 'main.js' ), true );
	// Бизнес-логика темы: собранный esbuild бандл из assets/scripts/js/index.js (источник).
	// Сборка: `npm run build` (одноразово) / `npm run watch` (разработка). Файл woo.bundle.js коммитится.
	wp_enqueue_script( 'moveat-main', get_template_directory_uri() . '/assets/scripts/js/woo.bundle.js', [ 'jquery' ], moveat_asset_ver( 'assets/scripts/js/woo.bundle.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_scripts' );

// ----------------------------------- Дополнительные настройки скриптов
// moveat-main (woo.bundle.js) — как ES-модуль; moveat-bundle (main.js, ~849 КБ) — defer,
// чтобы тяжёлый бандл вёрстки не блокировал парсер и не задерживал DOMContentLoaded.
function moveat_add_module_type( $tag, $handle ) {
	if ( 'moveat-main' === $handle ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	if ( 'moveat-bundle' === $handle && strpos( $tag, ' defer' ) === false ) {
		return str_replace( '<script ', '<script defer ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'moveat_add_module_type', 10, 2 );

// ----------------------------------- Подключение шрифтов (self-hosted, без Google CDN)
function moveat_enqueue_fonts() {
	wp_enqueue_style(
		'moveat-fonts',
		get_template_directory_uri() . '/assets/fonts/fonts.css',
		[],
		moveat_asset_ver( 'assets/fonts/fonts.css' )
	);
}
add_action('wp_enqueue_scripts', 'moveat_enqueue_fonts');

// Preload ключевых начертаний первого экрана, чтобы текст меньше «прыгал» при swap.
// crossorigin обязателен: шрифты всегда грузятся в CORS-режиме, иначе preload не зачтётся.
function moveat_preload_fonts() {
	$fonts_uri = get_template_directory_uri() . '/assets/fonts';
	$preload = [
		'montserrat-v31-cyrillic_cyrillic-ext_latin_latin-ext-regular.woff2',
		'montserrat-v31-cyrillic_cyrillic-ext_latin_latin-ext-600.woff2',
	];
	foreach ( $preload as $file ) {
		printf(
			'<link rel="preload" href="%s/%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $fonts_uri ),
			esc_attr( $file )
		);
	}
}
add_action('wp_head', 'moveat_preload_fonts', 1);

// ----------------------------------- Подключение php скриптов
// Подключаем дополнительные php скрипты из assets/scripts/php/index.php
require_once get_template_directory() . '/assets/scripts/php/index.php';

// ----------------------------------- Подключение title
add_action('wp_head', function() {
	echo '<title>' . get_bloginfo('name') . '</title>';
}, 1);

// ----------------------------------- Поиск: все результаты на одной странице
function moveat_search_show_all_results( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'posts_per_page', -1 );
}
add_action( 'pre_get_posts', 'moveat_search_show_all_results' );