<?php
/**
 * Template Name: About
 * Description: Шаблон страницы "О нас" — использует ACF-поля из assets/acf-fields/about.json
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>


<?php
// Hero: allow override via ACF about_title, fallback to static title
$about_title = get_field( 'about_title' );
?>

<div class="hero-block">
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php echo esc_html( $about_title ?: 'Наша команда' ); ?></h1>
		<nav aria-label="breadcrumb no-padding animated slideInDown page-hero__breadcrumbs">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><a class="text-body" href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a></li>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><span class="text-body">О нас</span></li>
			</ol>
		</nav>
	</div>
</div>

<!-- About Page Start -->
<main class="about-page">
	<div class="about-page__container max-width-limiter">

		<?php
		// Вывод участников: поля member_1..member_10
		$max_members = 10;
		for ( $i = 1; $i <= $max_members; $i++ ) {
			$name  = get_field( "member_{$i}_name" );
			$role  = get_field( "member_{$i}_role" );
			$bio   = get_field( "member_{$i}_bio" ); // wysiwyg
			$photo = get_field( "member_{$i}_photo" ); // return_format: url

			// Пропускаем пустые слоты
			if ( empty( $name ) && empty( $role ) && empty( $bio ) && empty( $photo ) ) {
				continue;
			}

			$reverse_class = ( $i % 2 === 0 ) ? ' about-page__section--reverse' : '';
			?>

			<section class="about-page__section<?php echo $reverse_class; ?>">
				<?php if ( ! empty( $photo ) ) : ?>
					<div class="about-page__photo-wrapper">
						<img class="about-page__photo" src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ?: 'Участник' ); ?>" loading="lazy" />
					</div>
				<?php endif; ?>

				<div class="about-page__content">
					<?php if ( ! empty( $name ) ) : ?>
						<h3 class="about-page__name"><?php echo esc_html( $name ); ?></h3>
					<?php endif; ?>

					<?php if ( ! empty( $role ) ) : ?>
						<p class="about-page__role"><?php echo esc_html( $role ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $bio ) ) : ?>
						<div class="about-page__text"><?php echo wp_kses_post( $bio ); ?></div>
					<?php endif; ?>
				</div>
			</section>

			<?php
		} // end for
		?>

	</div>
</main>
<!-- About Page End -->

<?php get_footer(); ?>
