<?php

// ----------------------------------- ID страницы глобальных настроек
// Если страница была пересоздана и ID изменился — заменить значение здесь
define( 'GLOBAL_SETTINGS_PAGE_ID', 217 );

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

