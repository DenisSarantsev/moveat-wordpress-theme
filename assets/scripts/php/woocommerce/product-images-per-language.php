<?php
/**
 * Свои картинки у каждой языковой версии товара (Polylang for WooCommerce).
 *
 * Polylang for WooCommerce (src/product-language-cpt.php, get_legacy_metas())
 * всегда добавляет _thumbnail_id и _product_image_gallery в список
 * синхронизируемых полей товара — в обход галочки «Изображение записи»
 * в настройках Polylang. Из-за этого смена фото в одной версии
 * перезатирала фото во всех переводах.
 *
 * Убираем эти поля только из синхронизации ($sync === true).
 * При создании перевода ($sync === false) картинки по-прежнему копируются
 * как заготовка, дальше каждая версия живёт своими.
 */
defined( 'ABSPATH' ) || exit;

add_filter(
	'pllwc_copy_post_metas',
	function ( $metas, $sync ) {
		if ( $sync ) {
			unset( $metas['_thumbnail_id'], $metas['_product_image_gallery'] );
		}
		return $metas;
	},
	20,
	2
);
