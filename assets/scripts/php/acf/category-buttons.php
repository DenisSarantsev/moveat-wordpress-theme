<?php
/*
 ACF-группа «Рубрики — текст кнопок» на странице глобальных настроек (GLOBAL_SETTINGS_PAGE_ID).
 Для каждой рубрики записей (taxonomy `category`) генерируется текстовое поле с подписью кнопки.
 Единый источник правды: и архив рубрики, и карточки на странице статей берут текст отсюда.
 Группа регистрируется программно (а не статичным JSON), потому что:
   - список рубрик динамический (новая рубрика => новое поле автоматически);
   - в free-версии ACF нет повторителя.
*/
defined( 'ABSPATH' ) || exit;

// Дефолтная подпись, если для рубрики ничего не задано.
if ( ! defined( 'MOVEAT_DEFAULT_BUTTON_TITLE' ) ) {
	define( 'MOVEAT_DEFAULT_BUTTON_TITLE', 'Подробнее' );
}

if ( ! function_exists( 'moveat_category_button_title_field_key' ) ) {
	function moveat_category_button_title_field_key( $term_id ) {
		return 'field_category_button_title_' . (int) $term_id;
	}
}

if ( ! function_exists( 'moveat_category_button_title_field_name' ) ) {
	function moveat_category_button_title_field_name( $term_id ) {
		return 'category_button_title_' . (int) $term_id;
	}
}

// Текст кнопки для конкретной рубрики.
if ( ! function_exists( 'moveat_get_category_button_title' ) ) {
	function moveat_get_category_button_title( $term_id ) {
		$term_id = (int) $term_id;
		$title   = '';
		if ( $term_id > 0 && function_exists( 'get_field' ) && defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ) {
			$value = get_field( moveat_category_button_title_field_name( $term_id ), GLOBAL_SETTINGS_PAGE_ID );
			if ( is_string( $value ) ) {
				$title = trim( $value );
			}
		}
		if ( $title === '' ) {
			$title = MOVEAT_DEFAULT_BUTTON_TITLE;
		}
		return $title;
	}
}

// Текст кнопки для поста — по его первой рубрике.
if ( ! function_exists( 'moveat_get_post_button_title' ) ) {
	function moveat_get_post_button_title( $post_id ) {
		$terms = get_the_terms( $post_id, 'category' );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$first = reset( $terms );
			if ( $first instanceof WP_Term ) {
				return moveat_get_category_button_title( $first->term_id );
			}
		}
		return MOVEAT_DEFAULT_BUTTON_TITLE;
	}
}

// Регистрация группы: по полю на каждую рубрику записей и товаров.
// Поздний приоритет на `init` — чтобы WooCommerce уже зарегистрировал `product_cat`
// (ACF `acf/init` срабатывает раньше регистрации таксономий Woo).
add_action(
	'init',
	static function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) || ! defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ) {
			return;
		}
			// Рубрики записей + (если есть WooCommerce) рубрики товаров. Для наглядности
			// рубрики товаров помечаем префиксом в подписи поля. Term ID в WP уникален
			// между таксономиями, поэтому имя поля по term_id не конфликтует.
			$taxonomies = [
				'category'    => '',
				'product_cat' => 'Товары: ',
			];

			$fields = [];
			foreach ( $taxonomies as $taxonomy => $label_prefix ) {
				if ( ! taxonomy_exists( $taxonomy ) ) {
					continue;
				}
				$terms = get_terms(
					[
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					]
				);
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					continue;
				}
				foreach ( $terms as $term_item ) {
					if ( ! $term_item instanceof WP_Term ) {
						continue;
					}
					$fields[] = [
						'key'           => moveat_category_button_title_field_key( $term_item->term_id ),
						'label'         => $label_prefix . $term_item->name,
						'name'          => moveat_category_button_title_field_name( $term_item->term_id ),
						'type'          => 'text',
						'default_value' => MOVEAT_DEFAULT_BUTTON_TITLE,
						'placeholder'   => MOVEAT_DEFAULT_BUTTON_TITLE,
					];
				}
			}
			if ( empty( $fields ) ) {
				return;
			}
			acf_add_local_field_group(
				[
					'key'                    => 'group_moveat_category_buttons',
					'title'                  => 'Рубрики — текст кнопок',
					'fields'                 => $fields,
					'location'               => [
						[
							[
								'param'    => 'page',
								'operator' => '==',
								'value'    => GLOBAL_SETTINGS_PAGE_ID,
							],
						],
					],
					'position'               => 'normal',
					'style'                  => 'default',
					'instruction_placement'  => 'label',
					'active'                 => true,
				]
			);
	},
	20
);
