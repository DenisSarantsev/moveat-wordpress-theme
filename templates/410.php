<?php
/*
	Template Name: 410
	Description: Шаблон страницы "410" показывается для мусорных страниц
	
	"Мусорные" адреа прописаны в assets/scripts/php/main/410-rules.php
*/

defined( 'ABSPATH' ) || exit;

get_header();
?>

<!-- 410 Start -->
<main class="page-deleted">
	<div class="page-deleted__wrapper">
		<img
			class="page-deleted__image"
			src="<?php echo get_template_directory_uri() ?>/assets/images/illustrations/deleted-page.png"
			alt="deleted page icon" />
		<h1 class="page-deleted__title">Страница удалена</h1>
		<div class="page-deleted__description">
			Этот контент больше не существует
		</div>
	</div>
</main>
<!-- 410 End -->

<?php get_footer(); ?>
