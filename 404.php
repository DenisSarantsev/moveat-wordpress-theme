<?php
/*
	Страница 404
*/

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- 404 Start -->
<main class="page-not-found">
	<div class="page-not-found__wrapper">
		<img
			class="page-not-found__image"
			src="<?php echo get_template_directory_uri() ?>/assets/images/illustrations/not-found-page.png"
			alt="page not found" />
		<h1 class="page-not-found__title">Страница не найдена</h1>
		<div class="page-not-found__description">
			Этой страницы не существует
		</div>
	</div>
</main>
<!-- 404 End -->

<?php get_footer(); ?>
