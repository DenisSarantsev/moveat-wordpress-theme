<?php
/*
	Страница 404
*/

defined( 'ABSPATH' ) || exit;

get_header();

// Тексты вёрстки на языке страницы — assets/dictionaries/templates/404.
$page_dict = moveat_dictionary( 'templates/404' );
$t = function ( $key ) use ( $page_dict ) {
	return moveat_dictionary_text( $page_dict, $key );
};
?>

<!-- 404 Start -->
<main class="page-not-found">
	<div class="page-not-found__wrapper">
		<img
			class="page-not-found__image"
			src="<?php echo get_template_directory_uri() ?>/assets/images/illustrations/not-found-page.png"
			alt="page not found" />
		<h1 class="page-not-found__title"><?php echo esc_html( $t( 'title' ) ); ?></h1>
		<div class="page-not-found__description">
			<?php echo esc_html( $t( 'description' ) ); ?>
		</div>
	</div>
</main>
<!-- 404 End -->

<?php get_footer(); ?>
