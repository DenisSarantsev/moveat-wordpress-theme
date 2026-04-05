<?php

// ----------------------------------- Меню: регистрация и классы
// Регистрируем области меню для админки
function moveat_register_menus() {
	register_nav_menus( [
		'header_menu' => __( 'Главное меню в шапке', 'moveat' ),
	] );
}
add_action( 'after_setup_theme', 'moveat_register_menus' );

// Добавляем bootstrap-классы к пунктам меню в шапке
function moveat_nav_menu_css_class( $classes, $item, $args ) {
	if ( isset( $args->theme_location ) && $args->theme_location === 'header_menu' ) {
		$classes[] = 'nav-item';
		// Подсветка активного пункта
		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			$classes[] = 'active';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'moveat_nav_menu_css_class', 10, 3 );

function moveat_nav_menu_link_attributes( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && $args->theme_location === 'header_menu' ) {
		$atts['class'] = isset( $atts['class'] ) ? $atts['class'] . ' nav-link' : 'nav-link';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'moveat_nav_menu_link_attributes', 10, 3 );

