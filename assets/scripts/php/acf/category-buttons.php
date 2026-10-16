<?php
/*
 ACF-группа «Рубрики — текст кнопок» на странице глобальных настроек (GLOBAL_SETTINGS_PAGE_ID).
 Для каждой рубрики записей (taxonomy `category`) генерируется текстовое поле с подписью кнопки.
 Единый источник правды: и архив рубрики, и карточки на странице статей берут текст отсюда.
 Мультиязычность (Polylang): у каждой языковой версии страницы настроек своя группа
 с рубриками только этого языка. Текст кнопки берётся со страницы на языке рубрики.
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

// Дефолтная подпись на нужном языке ('ru' | 'uk').
if ( ! function_exists( 'moveat_default_button_title' ) ) {
	function moveat_default_button_title( $lang = 'ru' ) {
		return 'uk' === $lang ? 'Детальніше' : MOVEAT_DEFAULT_BUTTON_TITLE;
	}
}

// Текущий язык сайта без привязки к Polylang (локально плагина нет).
if ( ! function_exists( 'moveat_category_button_current_lang' ) ) {
	function moveat_category_button_current_lang() {
		return function_exists( 'moveat_current_lang' ) ? moveat_current_lang() : 'ru';
	}
}

// Текст кнопки для конкретной рубрики — со страницы настроек на языке рубрики.
if ( ! function_exists( 'moveat_get_category_button_title' ) ) {
	function moveat_get_category_button_title( $term_id ) {
		$term_id   = (int) $term_id;
		$title     = '';
		$lang      = moveat_category_button_current_lang();
		$term_slug = ( $term_id > 0 && function_exists( 'pll_get_term_language' ) ) ? pll_get_term_language( $term_id ) : '';
		if ( $term_slug && function_exists( 'moveat_normalize_lang' ) ) {
			$lang = moveat_normalize_lang( $term_slug );
		}
		if ( $term_id > 0 && function_exists( 'get_field' ) && defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ) {
			$page_id = $term_slug ? (int) pll_get_post( GLOBAL_SETTINGS_PAGE_ID, $term_slug ) : 0;
			if ( ! $page_id ) {
				$page_id = moveat_global_page_id();
			}
			$value = get_field( moveat_category_button_title_field_name( $term_id ), $page_id );
			if ( is_string( $value ) ) {
				$title = trim( $value );
			}
		}
		if ( $title === '' ) {
			$title = moveat_default_button_title( $lang );
		}
		return $title;
	}
}

// Текст кнопки для объекта — по его первой рубрике в указанной таксономии.
if ( ! function_exists( 'moveat_get_object_button_title' ) ) {
	function moveat_get_object_button_title( $object_id, $taxonomy ) {
		$terms = get_the_terms( $object_id, $taxonomy );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$first = reset( $terms );
			if ( $first instanceof WP_Term ) {
				return moveat_get_category_button_title( $first->term_id );
			}
		}
		return moveat_default_button_title( moveat_category_button_current_lang() );
	}
}

// Текст кнопки для поста — по его первой рубрике записей.
if ( ! function_exists( 'moveat_get_post_button_title' ) ) {
	function moveat_get_post_button_title( $post_id ) {
		return moveat_get_object_button_title( $post_id, 'category' );
	}
}

// Текст кнопки для товара — по его первой рубрике товаров.
if ( ! function_exists( 'moveat_get_product_button_title' ) ) {
	function moveat_get_product_button_title( $product_id ) {
		return moveat_get_object_button_title( $product_id, 'product_cat' );
	}
}

// Регистрация групп: по группе на каждую языковую версию страницы настроек,
// в группе — по полю на каждую рубрику записей и товаров этого языка.
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

		// Ключ поля = term_id, поэтому одна рубрика может попасть только в одну группу.
		$used_keys = [];

		foreach ( moveat_global_page_ids() as $page_id ) {
			// Язык страницы в Polylang ('ru', 'uk'/'ua'); без плагина — пусто, берём все рубрики.
			$lang_slug = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page_id ) : '';
			$lang      = ( $lang_slug && function_exists( 'moveat_normalize_lang' ) ) ? moveat_normalize_lang( $lang_slug ) : 'ru';
			$default   = moveat_default_button_title( $lang );

			$fields = [];
			foreach ( $taxonomies as $taxonomy => $label_prefix ) {
				if ( ! taxonomy_exists( $taxonomy ) ) {
					continue;
				}
				$args = [
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				];
				if ( $lang_slug ) {
					$args['lang'] = $lang_slug;
				}
				$terms = get_terms( $args );
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					continue;
				}
				foreach ( $terms as $term_item ) {
					if ( ! $term_item instanceof WP_Term ) {
						continue;
					}
					$key = moveat_category_button_title_field_key( $term_item->term_id );
					if ( isset( $used_keys[ $key ] ) ) {
						continue;
					}
					$used_keys[ $key ] = true;
					$fields[]          = [
						'key'           => $key,
						'label'         => $label_prefix . $term_item->name,
						'name'          => moveat_category_button_title_field_name( $term_item->term_id ),
						'type'          => 'text',
						'default_value' => $default,
						'placeholder'   => $default,
					];
				}
			}
			if ( empty( $fields ) ) {
				continue;
			}
			acf_add_local_field_group(
				[
					// Ключ русской группы прежний — у неё уже есть сохранённые значения.
					'key'                   => GLOBAL_SETTINGS_PAGE_ID === $page_id ? 'group_moveat_category_buttons' : 'group_moveat_category_buttons_' . $page_id,
					'title'                 => 'Рубрики — текст кнопок',
					'fields'                => $fields,
					'location'              => [
						[
							[
								'param'    => 'page',
								'operator' => '==',
								'value'    => $page_id,
							],
						],
					],
					'position'              => 'normal',
					'style'                 => 'default',
					'instruction_placement' => 'label',
					'active'                => true,
				]
			);
		}
	},
	20
);
