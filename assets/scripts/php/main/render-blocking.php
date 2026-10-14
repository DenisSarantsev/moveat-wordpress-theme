<?php
/**
 * Меньше блокирующих запросов в <head> (Lighthouse: Render-blocking requests).
 *
 * Пока браузер не скачает все CSS и синхронные скрипты из <head>, он ничего
 * не рисует — на мобильном интернете это заметно задерживает первый экран.
 */

defined( 'ABSPATH' ) || exit;

/*
	Стандартные стили WooCommerce (woocommerce.css, -layout, -smallscreen).
	Корзину, оформление и оплату тема рисует своей вёрсткой, стандартных
	страниц магазина (my-account, checkout) нет — эти стили не используются.
*/
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/*
	jquery-migrate — прослойка для кода под jQuery 1.x. На сайте предупреждений
	JQMIGRATE в консоли нет, значит устаревшее API никто не использует.
	Убираем только на сайте: админка WordPress пусть работает как прежде.
*/
add_action( 'wp_default_scripts', function ( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		[ 'jquery-migrate' ]
	);
} );

/*
	Стили плагинов, которые не нужны для первого экрана: виджет Google Language
	Translator (у нас свой переключатель) и переключатель валют YayCurrency
	(на страницах не выводится). Грузим без блокировки: media="print" браузер
	качает с низким приоритетом и не ждёт, onload включает стили для экрана.
*/
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	$non_blocking = [ 'google-language-translator', 'yay-currency-frontend-style' ];

	if ( is_admin() || ! in_array( $handle, $non_blocking, true ) ) {
		return $tag;
	}

	$async = preg_replace(
		"/media=(['\"])[^'\"]*\\1/",
		'media="print" onload="this.media=\'all\'"',
		$tag,
		1,
		$count
	);
	if ( ! $count ) {
		$async = str_replace( '<link ', '<link media="print" onload="this.media=\'all\'" ', $tag );
	}

	return $async . '<noscript>' . trim( $tag ) . '</noscript>' . "\n";
}, 10, 2 );
