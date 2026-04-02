<?php

// ----------------------------------- Подключение стилей и скриптов
function moveat_enqueue_scripts() {
	// Основной stylesheet темы (style.css)
	wp_enqueue_style(
		'moveat-style',
		get_stylesheet_uri(),
		[],
		null
	);

	// OwlCarousel CSS
	wp_enqueue_style(
		'moveat-owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css',
		[],
		null
	);

	wp_enqueue_style(
		'moveat-owl-carousel-theme',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css',
		[],
		null
	);

	// Главный скрипт темы
	wp_enqueue_script(
		'moveat-main',
		get_template_directory_uri() . '/assets/scripts/js/main.js',
		[ 'jquery' ],
		null,
		true
	);

	// OwlCarousel JS (загружается после встроенного jQuery WordPress)
	wp_enqueue_script(
		'moveat-owl-carousel',
		'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js',
		[ 'jquery' ],
		null,
		true
	);

	// Инициализация карусели
	wp_enqueue_script(
		'moveat-carousel-init',
		get_template_directory_uri() . '/assets/scripts/js/modules/carousel-init.js',
		[ 'moveat-owl-carousel' ],
		null,
		true
	);

	// Спиннер (inline в header.php, скрываем после загрузки страницы)
	wp_enqueue_script(
		'moveat-spinner',
		get_template_directory_uri() . '/assets/scripts/js/modules/spinner.js',
		[],
		null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_scripts' );

// type="module" убран: main.js — скомпилированный бандл, не нуждается в module-режиме.
// Порядок выполнения: jquery → main.js (window.jQuery=zr) → owl.carousel (расширяет zr.fn) → carousel-init
