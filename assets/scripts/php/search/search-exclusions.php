<?php
/*
 Исключения из поисковой выдачи. Три блока (метабокса) на странице глобальных настроек:
   - «Поиск — скрыть записи»:   целые рубрики (мультиселект) + отдельные записи (Relationship);
   - «Поиск — скрыть товары»:   целые категории товаров + отдельные товары (только при WooCommerce);
   - «Поиск — скрыть страницы»: отдельные страницы (у страниц рубрик нет).
 Целые рубрики/категории — живое правило (tax_query NOT IN, ловит и будущие элементы).
 Отдельные элементы — post__not_in. Применяется к основному поисковому запросу.
*/
defined( 'ABSPATH' ) || exit;

// ----------------------------------- Регистрация трёх ACF-блоков на странице настроек
// Поздний приоритет на init — чтобы WooCommerce уже зарегистрировал product_cat.
add_action(
	'init',
	static function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) || ! defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ) {
			return;
		}

		$location = [
			[
				[
					'param'    => 'page',
					'operator' => '==',
					'value'    => GLOBAL_SETTINGS_PAGE_ID,
				],
			],
		];

		// --- Блок «Записи»
		acf_add_local_field_group(
			[
				'key'                   => 'group_moveat_search_excl_posts',
				'title'                 => 'Поиск — скрыть записи',
				'fields'                => [
					[
						'key'       => 'field_search_excl_post_summary',
						'label'     => '',
						'name'      => '',
						'type'      => 'message',
						'message'   => 'Скрыто из поиска: 0 из 0 записей',
						'new_lines' => '',
						'esc_html'  => 0,
					],
					[
						'key'           => 'field_search_excl_post_cats',
						'label'         => 'Скрыть целые рубрики',
						'name'          => 'search_excluded_post_cats',
						'type'          => 'taxonomy',
						'instructions'  => 'Все записи из выбранных рубрик не попадут в поиск (в том числе те, что добавят позже).',
						'taxonomy'      => 'category',
						'field_type'    => 'multi_select',
						'add_term'      => 0,
						'save_terms'    => 0,
						'load_terms'    => 0,
						'return_format' => 'id',
					],
					[
						'key'           => 'field_search_excl_post_items',
						'label'         => 'Скрыть отдельные записи',
						'name'          => 'search_excluded_post_items',
						'type'          => 'relationship',
						'instructions'  => 'Точечно. Фильтр по рубрике над списком помогает быстро найти нужные.',
						'post_type'     => [ 'post' ],
						'filters'       => [ 'search', 'taxonomy' ],
						'return_format' => 'id',
					],
				],
				'location'              => $location,
				'position'              => 'normal',
				'style'                 => 'default',
				'instruction_placement' => 'label',
				'active'                => true,
			]
		);

		// --- Блок «Товары» (только при WooCommerce)
		if ( taxonomy_exists( 'product_cat' ) ) {
			acf_add_local_field_group(
				[
					'key'                   => 'group_moveat_search_excl_products',
					'title'                 => 'Поиск — скрыть товары',
					'fields'                => [
						[
							'key'       => 'field_search_excl_product_summary',
							'label'     => '',
							'name'      => '',
							'type'      => 'message',
							'message'   => 'Скрыто из поиска: 0 из 0 товаров',
							'new_lines' => '',
							'esc_html'  => 0,
						],
						[
							'key'           => 'field_search_excl_product_cats',
							'label'         => 'Скрыть целые категории товаров',
							'name'          => 'search_excluded_product_cats',
							'type'          => 'taxonomy',
							'instructions'  => 'Все товары из выбранных категорий не попадут в поиск (в том числе будущие).',
							'taxonomy'      => 'product_cat',
							'field_type'    => 'multi_select',
							'add_term'      => 0,
							'save_terms'    => 0,
							'load_terms'    => 0,
							'return_format' => 'id',
						],
						[
							'key'           => 'field_search_excl_product_items',
							'label'         => 'Скрыть отдельные товары',
							'name'          => 'search_excluded_product_items',
							'type'          => 'relationship',
							'instructions'  => 'Точечно. Фильтр по категории над списком помогает быстро найти нужные.',
							'post_type'     => [ 'product' ],
							'filters'       => [ 'search', 'taxonomy' ],
							'return_format' => 'id',
						],
					],
					'location'              => $location,
					'position'              => 'normal',
					'style'                 => 'default',
					'instruction_placement' => 'label',
					'active'                => true,
				]
			);
		}

		// --- Блок «Страницы»
		acf_add_local_field_group(
			[
				'key'                   => 'group_moveat_search_excl_pages',
				'title'                 => 'Поиск — скрыть страницы',
				'fields'                => [
					[
						'key'       => 'field_search_excl_page_summary',
						'label'     => '',
						'name'      => '',
						'type'      => 'message',
						'message'   => 'Скрыто из поиска: 0 из 0 страниц',
						'new_lines' => '',
						'esc_html'  => 0,
					],
					[
						'key'           => 'field_search_excl_page_items',
						'label'         => 'Скрыть страницы',
						'name'          => 'search_excluded_page_items',
						'type'          => 'relationship',
						'instructions'  => 'Выбранные страницы не будут показываться в результатах поиска.',
						'post_type'     => [ 'page' ],
						'filters'       => [ 'search' ],
						'return_format' => 'id',
					],
				],
				'location'              => $location,
				'position'              => 'normal',
				'style'                 => 'default',
				'instruction_placement' => 'label',
				'active'                => true,
			]
		);
	},
	20
);

// ----------------------------------- Высота списков Relationship вдвое больше дефолтной
add_action(
	'acf/input/admin_head',
	static function () {
		?>
		<style>
			[data-key="field_search_excl_post_items"] .acf-relationship .list,
			[data-key="field_search_excl_product_items"] .acf-relationship .list,
			[data-key="field_search_excl_page_items"] .acf-relationship .list {
				height: 368px;
			}
		</style>
		<?php
	}
);

// ----------------------------------- В фильтре таксономий товара оставляем только категории
add_action(
	'acf/input/admin_footer',
	static function () {
		?>
		<script>
		(function () {
			if ( typeof acf === 'undefined' ) { return; }
			function keepOnlyProductCats() {
				var fields = document.querySelectorAll( '[data-key="field_search_excl_product_items"]' );
				fields.forEach( function ( field ) {
					field.querySelectorAll( '.acf-relationship .filters select' ).forEach( function ( select ) {
						select.querySelectorAll( 'optgroup' ).forEach( function ( group ) {
							var opt = group.querySelector( 'option' );
							var val = opt ? ( opt.value || '' ) : '';
							if ( val.indexOf( 'product_cat:' ) !== 0 ) {
								group.remove();
							}
						} );
					} );
				} );
			}
			acf.addAction( 'ready', keepOnlyProductCats );
			acf.addAction( 'append', keepOnlyProductCats );
		})();
		</script>
		<?php
	}
);

// ----------------------------------- Счётчик «скрыто N из M» в сводках блоков
if ( ! function_exists( 'moveat_search_count_hidden' ) ) {
	function moveat_search_count_hidden( $post_type, $taxonomy, $cat_field, $item_field ) {
		$pid   = defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ? GLOBAL_SETTINGS_PAGE_ID : 0;
		$total = 0;
		$counts = wp_count_posts( $post_type );
		if ( $counts && isset( $counts->publish ) ) {
			$total = (int) $counts->publish;
		}

		$hidden_ids = moveat_search_collect_ids( get_field( $item_field, $pid ) );

		if ( $taxonomy && $cat_field && taxonomy_exists( $taxonomy ) ) {
			$cat_ids = array_values( array_filter( array_map( 'intval', (array) get_field( $cat_field, $pid ) ) ) );
			if ( $cat_ids ) {
				$in_cats = get_posts(
					[
						'post_type'      => $post_type,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'no_found_rows'  => true,
						'tax_query'      => [
							[
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => $cat_ids,
							],
						],
					]
				);
				$hidden_ids = array_merge( $hidden_ids, array_map( 'intval', (array) $in_cats ) );
			}
		}

		$hidden = count( array_values( array_unique( array_filter( $hidden_ids ) ) ) );
		if ( $hidden > $total ) {
			$hidden = $total;
		}
		return [ $hidden, $total ];
	}
}

if ( ! function_exists( 'moveat_search_summary_message' ) ) {
	function moveat_search_summary_message( $field, $post_type, $taxonomy, $cat_field, $item_field, $noun ) {
		if ( ! is_admin() || ! function_exists( 'get_field' ) ) {
			return $field;
		}
		list( $hidden, $total ) = moveat_search_count_hidden( $post_type, $taxonomy, $cat_field, $item_field );
		$field['message']       = sprintf( '<strong>Скрыто из поиска: %d из %d %s</strong>', $hidden, $total, $noun );
		return $field;
	}
}

add_filter(
	'acf/load_field/key=field_search_excl_post_summary',
	static function ( $field ) {
		return moveat_search_summary_message( $field, 'post', 'category', 'search_excluded_post_cats', 'search_excluded_post_items', 'записей' );
	}
);
add_filter(
	'acf/load_field/key=field_search_excl_product_summary',
	static function ( $field ) {
		return moveat_search_summary_message( $field, 'product', 'product_cat', 'search_excluded_product_cats', 'search_excluded_product_items', 'товаров' );
	}
);
add_filter(
	'acf/load_field/key=field_search_excl_page_summary',
	static function ( $field ) {
		return moveat_search_summary_message( $field, 'page', '', '', 'search_excluded_page_items', 'страниц' );
	}
);

// ----------------------------------- Применение исключений к поисковому запросу
if ( ! function_exists( 'moveat_search_collect_ids' ) ) {
	function moveat_search_collect_ids( $value ) {
		$ids = [];
		if ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_numeric( $item ) ) {
					$ids[] = (int) $item;
				} elseif ( $item instanceof WP_Post ) {
					$ids[] = (int) $item->ID;
				}
			}
		}
		return array_values( array_filter( array_unique( $ids ) ) );
	}
}

function moveat_search_filter_excluded( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) || ! defined( 'GLOBAL_SETTINGS_PAGE_ID' ) ) {
		return;
	}

	$pid = GLOBAL_SETTINGS_PAGE_ID;

	// 1) Отдельные элементы (записи + товары + страницы) → post__not_in.
	$item_ids = array_merge(
		moveat_search_collect_ids( get_field( 'search_excluded_post_items', $pid ) ),
		moveat_search_collect_ids( get_field( 'search_excluded_product_items', $pid ) ),
		moveat_search_collect_ids( get_field( 'search_excluded_page_items', $pid ) )
	);
	$item_ids = array_values( array_unique( $item_ids ) );
	if ( $item_ids ) {
		$not_in = (array) $query->get( 'post__not_in' );
		$query->set( 'post__not_in', array_values( array_unique( array_merge( $not_in, $item_ids ) ) ) );
	}

	// 2) Целые рубрики записей и категории товаров → tax_query NOT IN.
	$tax_query = (array) $query->get( 'tax_query' );
	$cat_ids   = array_values( array_filter( array_map( 'intval', (array) get_field( 'search_excluded_post_cats', $pid ) ) ) );
	$pcat_ids  = array_values( array_filter( array_map( 'intval', (array) get_field( 'search_excluded_product_cats', $pid ) ) ) );

	if ( $cat_ids ) {
		$tax_query[] = [
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $cat_ids,
			'operator' => 'NOT IN',
		];
	}
	if ( $pcat_ids && taxonomy_exists( 'product_cat' ) ) {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $pcat_ids,
			'operator' => 'NOT IN',
		];
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'moveat_search_filter_excluded' );
