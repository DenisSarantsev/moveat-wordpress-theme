<?php
// ----------------------------------- Тип записей «Баннеры»
add_action('init', function () {
	register_post_type(
		'banner',
		[
			'labels'              => [
				'name'               => 'Баннеры',
				'singular_name'      => 'Баннер',
				'add_new'            => 'Добавить баннер',
				'add_new_item'       => 'Добавить баннер',
				'edit_item'          => 'Редактировать баннер',
				'new_item'           => 'Новый баннер',
				'view_item'          => 'Просмотреть баннер',
				'search_items'       => 'Искать баннеры',
				'not_found'          => 'Баннеров не найдено',
				'not_found_in_trash' => 'В корзине баннеров нет',
				'all_items'          => 'Все баннеры',
				'menu_name'          => 'Баннеры',
				'name_admin_bar'     => 'Баннер',
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-images-alt2',
			'supports'            => ['title', 'thumbnail', 'revisions'],
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
		]
	);
});

// ----------------------------------- Переводы баннеров (Polylang)
// Тип непубличный, поэтому Polylang не предлагает его в своих настройках —
// включаем переводы кодом. В списке настроек Polylang тип не показываем,
// чтобы переводы случайно не выключили.
add_filter('pll_get_post_types', function ($post_types, $is_settings) {
	if ($is_settings) {
		unset($post_types['banner']);
	} else {
		$post_types['banner'] = 'banner';
	}
	return $post_types;
}, 10, 2);
?>
