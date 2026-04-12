<?php get_header(); ?>

	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

		<main class="default-page">
			<div class="default-page__container">

				<h1 class="default-page__title"><?php the_title(); ?></h1>

				<div class="default-page__content">
					<?php the_content(); ?>
				</div>

			</div>
		</main>

	<?php endwhile; endif; ?>

<?php get_footer(); ?>
