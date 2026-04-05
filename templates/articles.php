<?php
/*
	Template Name: Статьи
	Template Post Type: page
*/
?>
<?php get_header(); ?>

	<!-- Page Header Start -->
	<div class="hero-block">
		<div class="hero-block__container">
			<h1 class="hero-block__title"><?php echo esc_html( get_the_title() ); ?></h1>
			<nav aria-label="breadcrumb no-padding animated slideInDown page-hero__breadcrumbs">
				<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
					<li class="breadcrumb-item page-hero__breadcrumbs-item white">
						<a class="text-body" href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
					</li>
					<li class="breadcrumb-item page-hero__breadcrumbs-item white">
						<span class="text-body"><?php echo esc_html( get_the_title() ); ?></span>
					</li>
				</ol>
			</nav>
		</div>
	</div>
	<!-- Page Header End -->

	<!-- Articles Start -->
	<div class="articles">
		<div class="articles__container max-width-limiter">
			<div class="articles__grid">
				<?php
					$paged = max(1, get_query_var('paged'));
					$query = new WP_Query([
						'post_type'      => 'post',
						'post_status'    => 'publish',
						'posts_per_page' => 12,
						'paged'          => $paged,
					]);
				?>
				<?php if ($query->have_posts()) : ?>
					<?php while ($query->have_posts()) : $query->the_post(); ?>
						<a href="<?php the_permalink(); ?>" class="articles__card article-card">
							<div class="article-card__top">
								<div class="article-card__image-wrapper">
									<?php
										$thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
										if ($thumb_url) :
									?>
										<img class="article-card__image" src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
									<?php endif; ?>
								</div>
								<div class="article-card__content">
									<h3 class="article-card__title"><?php the_title(); ?></h3>
								</div>
							</div>
							<div class="article-card__bottom">
								<span class="article-card__button primary-button">Читать статью</span>
							</div>
						</a>
					<?php endwhile; wp_reset_postdata(); ?>
				<?php else : ?>
					<p>Статей пока нет.</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
			$total_pages = isset($query) ? (int) $query->max_num_pages : 0;
			if ($total_pages > 1) :
				$current = max(1, $paged);
				$links = paginate_links([
					'base'      => get_pagenum_link(1) . '%_%',
					'format'    => 'page/%#%/',
					'current'   => $current,
					'total'     => $total_pages,
					'prev_text' => 'Предыдущая',
					'next_text' => 'Следующая',
					'type'      => 'array',
				]);
		?>
			<?php if (!empty($links) && is_array($links)) : ?>
				<div class="pagination max-width-limiter">
					<?php foreach ($links as $link_html) : ?>
						<?php
							$is_active = strpos($link_html, 'current') !== false ? ' is-active' : '';
							$link_clean = preg_replace('/class="[^"]*"/', '', $link_html);
						?>
						<span class="pagination__item<?php echo $is_active; ?>"><?php echo $link_clean; ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<!-- Articles End -->

<?php get_footer(); ?>

