<?php

// ----------------------------------- ID страницы глобальных настроек
// Если страница была пересоздана и ID изменился — заменить значение здесь
define( 'GLOBAL_SETTINGS_PAGE_ID', 217 );

// ----------------------------------- ID страницы глобальных настроек на текущем языке
// Polylang хранит украинскую версию отдельной страницей со своими значениями полей.
// Нет перевода или Polylang не активен — берём русскую страницу целиком (не по полю,
// чтобы в футере не смешались украинские и русские ссылки).
// Кнопки рубрик читают страницу на языке рубрики — см. category-buttons.php.
if ( ! function_exists( 'moveat_global_page_id' ) ) {
	function moveat_global_page_id() {
		if ( function_exists( 'pll_get_post' ) ) {
			$translated = pll_get_post( GLOBAL_SETTINGS_PAGE_ID );
			if ( $translated ) {
				return (int) $translated;
			}
		}
		return GLOBAL_SETTINGS_PAGE_ID;
	}
}

// ----------------------------------- ID всех языковых версий страницы глобальных настроек
// Для правил показа групп полей, которые нужно заполнять на каждом языке отдельно.
if ( ! function_exists( 'moveat_global_page_ids' ) ) {
	function moveat_global_page_ids() {
		$ids = [ GLOBAL_SETTINGS_PAGE_ID ];
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$ids = array_merge( $ids, array_map( 'intval', array_values( (array) pll_get_post_translations( GLOBAL_SETTINGS_PAGE_ID ) ) ) );
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}
}

// ----------------------------------- ACF: Поля продукта (free)
if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group( [
		'key' => 'group_moveat_product_fields',
		'title' => 'Поля товара',
		'fields' => [
			[
				'key' => 'field_moveat_product_audio',
				'label' => 'Аудиофайл',
				'name' => 'product_audio',
				'type' => 'file',
				'return_format' => 'array',
				'library' => 'all',
			],
			[
				'key' => 'field_moveat_product_formats',
				'label' => 'Форматы получения',
				'name' => 'product_formats',
				'type' => 'checkbox',
				'choices' => [
					'pdf' => 'PDF',
					'audio' => 'Аудио',
					'video' => 'Видео',
					'zip' => 'ZIP',
					'image' => 'Изображения',
					'text' => 'Текст',
				],
				'layout' => 'horizontal',
				'return_format' => 'value',
			],
		],
		'location' => [
			[
				[
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'product',
				],
			],
		],
		'position' => 'normal',
		'style' => 'default',
	] );
}

