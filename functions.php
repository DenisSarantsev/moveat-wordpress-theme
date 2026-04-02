<?php

// ----------------------------------- Подключение стилей и скриптов
function moveat_enqueue_scripts() {
	// Основные стили
	wp_enqueue_style( 'moveat-style', get_stylesheet_uri(), [], null );
	// Внешние стили
	wp_enqueue_style( 'moveat-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', [], null );
	wp_enqueue_style( 'moveat-owl-carousel-theme', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', [], null );

	// Основной бандл
	wp_enqueue_script( 'moveat-bundle', get_template_directory_uri() . '/main.js', [], null, true );
	// Внешние скрипты
	wp_enqueue_script( 'moveat-owl-carousel', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', [ 'jquery' ], null, true );
	// Дополнительные скрипты из assets/scripts/js/index.js
	wp_enqueue_script( 'moveat-main', get_template_directory_uri() . '/assets/scripts/js/index.js', [ 'jquery', 'moveat-owl-carousel' ], null, true );
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_scripts' );

// ----------------------------------- Дополнительные настройки скриптов
// Добавляем type="module" для index.js, чтобы ES-импорты работали в браузере
function moveat_add_module_type( $tag, $handle ) {
	if ( 'moveat-main' === $handle ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'moveat_add_module_type', 10, 2 );

// ----------------------------------- Подключение php скриптов
// Подключаем дополнительные php скрипты из assets/scripts/php/index.php
require_once get_template_directory() . '/assets/scripts/php/index.php';