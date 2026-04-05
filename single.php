<?php get_header(); ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php
			$categories = get_the_category();
			$category_name = $categories ? $categories[0]->name : '';
			$category_link = $categories ? get_category_link($categories[0]->term_id) : '';
			$posts_page = get_permalink(get_option('page_for_posts'));
			$reading_time_minutes = max(1, (int) ceil(str_word_count(wp_strip_all_tags(get_the_content())) / 200));
		?>

		<main class="article-page">
			<div class="product-breadcrumbs">
				<div class="product-breadcrumbs__container max-width-limiter">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb product-breadcrumbs__list">
							<li class="breadcrumb-item product-breadcrumbs__item">
								<a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
							</li>
							<li class="breadcrumb-item product-breadcrumbs__item">
								<a href="<?php echo esc_url($posts_page ?: home_url('/')); ?>">Статьи</a>
							</li>
							<li class="breadcrumb-item product-breadcrumbs__item active" aria-current="page">
								<?php echo esc_html(get_the_title()); ?>
							</li>
						</ol>
					</nav>
				</div>
			</div>

			<div class="article-page__container max-width-limiter">
				<h1 class="article-page__title"><?php the_title(); ?></h1>
				<div class="article-page__meta">
					<?php if ($category_name && $category_link) : ?>
						<a class="article-page__category" href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category_name); ?></a>
						<span class="article-page__divider"></span>
					<?php endif; ?>
					<time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>">
						<?php echo esc_html(date_i18n('j F Y', get_post_timestamp())); ?>
					</time>
					<!-- <span class="article-page__divider"></span>
					<span>Время чтения: <?php echo esc_html($reading_time_minutes); ?> мин</span> -->
				</div>

				<?php if (has_post_thumbnail()) : ?>
					<?php $cover = get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>
					<img class="article-page__cover" src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
				<?php endif; ?>

				<div class="article-page__layout">
					<article class="article-page__content">
						<?php if (has_excerpt()) : ?>
							<p class="article-page__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
						<?php endif; ?>
						<?php the_content(); ?>
						<?php
							// Поля блока CTA (берём из глобальной страницы настроек)
							$cta_title       = trim((string) get_field('cta_title', GLOBAL_SETTINGS_PAGE_ID));
							$cta_subtitle    = trim((string) get_field('cta_subtitle', GLOBAL_SETTINGS_PAGE_ID));
							$cta_button_text = trim((string) get_field('cta_button_text', GLOBAL_SETTINGS_PAGE_ID));
							$cta_button_url  = trim((string) get_field('cta_button_url', GLOBAL_SETTINGS_PAGE_ID));

							// Показываем CTA только если заполнены все поля (см. правила рендера)
							if ($cta_title && $cta_subtitle && $cta_button_text && $cta_button_url) :
						?>
							<div class="article-page__cta">
								<h3><?php echo esc_html($cta_title); ?></h3>
								<p><?php echo esc_html($cta_subtitle); ?></p>
								<a class="primary-button article-page__cta-button" href="<?php echo esc_url($cta_button_url); ?>">
									<?php echo esc_html($cta_button_text); ?>
								</a>
							</div>
						<?php endif; ?>

						<?php
							// Поля блока «Поделиться» (берём из глобальной страницы настроек)
							$share_title   = trim((string) get_field('share_title', GLOBAL_SETTINGS_PAGE_ID));
							$socials_group = (array) get_field('share_socials_group', GLOBAL_SETTINGS_PAGE_ID);

							// Собираем валидные соцсети (нужны и иконка, и ссылка)
							$valid_socials = [];
							$i = 1;
							while ($i <= 10) {
								$icon_url = isset($socials_group["social_{$i}_icon"]) ? trim((string) $socials_group["social_{$i}_icon"]) : '';
								$link_url = isset($socials_group["social_{$i}_url"]) ? trim((string) $socials_group["social_{$i}_url"]) : '';
								if ($icon_url && $link_url) {
									$valid_socials[] = [
										'icon' => $icon_url,
										'url'  => $link_url,
										'label'=> 'Social ' . $i,
									];
								}
								$i++;
							}

							// Показываем блок «Поделиться», если есть заголовок и хотя бы одна валидная соцсеть
							if ($share_title && !empty($valid_socials)) :
						?>
							<div class="article-page__share">
								<h3><?php echo esc_html($share_title); ?></h3>
								<div class="article-page__share-buttons">
									<?php foreach ($valid_socials as $social) : ?>
										<a href="<?php echo esc_url($social['url']); ?>" aria-label="<?php echo esc_attr($social['label']); ?>">
											<img src="<?php echo esc_url($social['icon']); ?>" alt="<?php echo esc_attr($social['label']); ?>">
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</article>
				</div>
			</div>
		</main>

		<?php
			// «Похожие статьи» — показываем секцию только если есть материалы
			$related_args = [
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => 3,
				'post__not_in'        => [get_the_ID()],
				'ignore_sticky_posts' => true,
			];
			if (!empty($categories)) {
				$related_args['category__in'] = wp_list_pluck($categories, 'term_id');
			}
			$related = new WP_Query($related_args);
			if ($related->have_posts()) :
		?>
			<section class="article-related">
				<div class="article-related__container max-width-limiter">
					<div class="title-subtitle-header">
						<h2 class="section-title">Похожие статьи</h2>
						<p class="text-secondary">Материалы, которые помогут закрепить полезные привычки</p>
					</div>
					<div class="article-related__grid">
						<?php while ($related->have_posts()) : $related->the_post(); ?>
							<a href="<?php the_permalink(); ?>" class="article-card">
								<div class="article-card__top">
									<?php if (has_post_thumbnail()) : ?>
										<img class="article-card__image" src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
									<?php endif; ?>
									<div class="article-card__content">
										<h3 class="article-card__title"><?php the_title(); ?></h3>
									</div>
								</div>
								<div class="article-card__bottom">
									<span class="article-card__button primary-button">Читать статью</span>
								</div>
							</a>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				</div>
			</section>
		<?php else : wp_reset_postdata(); endif; ?>

	<?php endwhile; endif; ?>

<?php get_footer(); ?>