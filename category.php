<?php
/*
 Рубрики записей WordPress (taxonomy `category`).
 Архив категорий товаров: assets/scripts/php/woocommerce/taxonomy-product-cat.php (template_include в product-card/setup.php).
*/
defined( 'ABSPATH' ) || exit;

$moveat_is_wc_product_cat_archive = defined( 'MOVEAT_PRODUCT_CATEGORY_ARCHIVE' ) && MOVEAT_PRODUCT_CATEGORY_ARCHIVE;

get_header();

if ( $moveat_is_wc_product_cat_archive ) {
	get_template_part( 'template-parts/content', 'product-category-archive' );
} elseif ( is_category() ) {
	get_template_part( 'template-parts/content', 'post-category-archive' );
}

get_footer();
