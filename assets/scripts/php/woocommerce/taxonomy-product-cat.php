<?php
/**
 * Архив категории товара WooCommerce (URL вида /product-category/…).
 * Разметка каталога — в category.php (константа MOVEAT_PRODUCT_CATEGORY_ARCHIVE).
 * Подключается через template_include в product-card/setup.php.
 */
defined( 'ABSPATH' ) || exit;

define( 'MOVEAT_PRODUCT_CATEGORY_ARCHIVE', true );
require get_template_directory() . '/category.php';
