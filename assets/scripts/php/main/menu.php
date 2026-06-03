<?php

// ----------------------------------- Меню: регистрация и вывод
function moveat_register_menus() {
	register_nav_menus( [
		'header_menu' => __( 'Главное меню в шапке', 'moveat' ),
	] );
}
add_action( 'after_setup_theme', 'moveat_register_menus' );

class Moveat_Header_Nav_Walker extends Walker_Nav_Menu {

	private function is_top_level_link_active( $item ) {
		$active_classes = [ 'current-menu-item', 'current_page_item' ];

		return (bool) array_intersect( $active_classes, $item->classes );
	}

	private function build_html_attributes( $atts ) {
		$html = '';

		foreach ( $atts as $attr => $value ) {
			if ( $value === '' || $value === null ) {
				continue;
			}

			$html .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
		}

		return $html;
	}

	private function get_menu_link_atts( $item, $class ) {
		$atts = [
			'href'  => ! empty( $item->url ) ? $item->url : '',
			'class' => $class,
		];

		if ( ! empty( $item->attr_title ) ) {
			$atts['title'] = $item->attr_title;
		}

		if ( ! empty( $item->target ) ) {
			$atts['target'] = $item->target;
		}

		if ( ! empty( $item->xfn ) ) {
			$atts['rel'] = $item->xfn;
		}

		return $atts;
	}

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Обёртка подпунктов открывается в start_el родителя
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '</div></div>';
		}
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = in_array( 'menu-item-has-children', $item->classes, true );

		if ( $depth === 0 && $has_children ) {
			$arrow_url = esc_url( get_template_directory_uri() . '/assets/images/icons/arrow.png' );

			$output .= '<div class="navbar-nav-list drop-menu">';
			$output .= '<div class="navbar-nav-list_title">';
			$output .= '<div class="navbar-nav-list_title-text">' . esc_html( $item->title ) . '</div>';
			$output .= '<img src="' . $arrow_url . '" alt="arrow" class="img-fluid" />';
			$output .= '</div>';
			$output .= '<div class="navbar-nav-list_items"><div class="navbar-nav-list_items-inner">';

			return;
		}

		if ( $depth === 0 ) {
			$link_classes = [ 'nav-item', 'nav-link' ];

			if ( $this->is_top_level_link_active( $item ) ) {
				$link_classes[] = 'active';
			}

			$atts = $this->get_menu_link_atts( $item, implode( ' ', $link_classes ) );
			$output        .= '<a' . $this->build_html_attributes( $atts ) . '>';
			$output        .= esc_html( $item->title );
			$output        .= '</a>';

			return;
		}

		$atts    = $this->get_menu_link_atts( $item, 'navbar-nav-list_item' );
		$output .= '<a' . $this->build_html_attributes( $atts ) . '>';
		$output .= esc_html( $item->title );
		$output .= '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $depth === 0 && in_array( 'menu-item-has-children', $item->classes, true ) ) {
			$output .= '</div>';
		}
	}
}

function moveat_header_nav_menu_args( $args ) {
	if ( isset( $args['theme_location'] ) && $args['theme_location'] === 'header_menu' ) {
		$args['container']   = false;
		$args['menu_class']  = 'navbar-nav ms-auto p-lg-0';
		$args['items_wrap']  = '<div id="%1$s" class="%2$s">%3$s</div>';
		$args['walker']      = new Moveat_Header_Nav_Walker();
		$args['fallback_cb'] = false;
	}

	return $args;
}
add_filter( 'wp_nav_menu_args', 'moveat_header_nav_menu_args' );
