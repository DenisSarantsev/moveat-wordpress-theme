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

// Оптимизируем подгрузку стилей по CDN
add_filter('style_loader_tag', function($html, $handle) {
	$async_styles = [
		'moveat-owl-carousel',
		'moveat-owl-carousel-theme'
	];
	if (in_array($handle, $async_styles)) {
		$html = str_replace(
			"rel='stylesheet'",
			"rel='preload' as='style' onload=\"this.rel='stylesheet'\"",
			$html
		);
	}
  return $html;
}, 10, 2);
// Добавляем noscript в тег head
add_action('wp_head', function() {
    ?>
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    </noscript>
    <?php
});

// ----------------------------------- Дополнительные настройки скриптов
// Добавляем type="module" для index.js, чтобы ES-импорты работали в браузере
function moveat_add_module_type( $tag, $handle ) {
	if ( 'moveat-main' === $handle ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'moveat_add_module_type', 10, 2 );

// ----------------------------------- Подключение шрифтов
function moveat_enqueue_fonts() {
	wp_enqueue_style(
		'google-fonts',
		'https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap',
		[],
		null
	);
}
add_action('wp_enqueue_scripts', 'moveat_enqueue_fonts');
// Оптимизируем подключение
function moveat_preconnect_fonts() {
	echo '
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	';
}
add_action('wp_head', 'moveat_preconnect_fonts', 1);

// ----------------------------------- Подключение php скриптов
// Подключаем дополнительные php скрипты из assets/scripts/php/index.php
require_once get_template_directory() . '/assets/scripts/php/index.php';

