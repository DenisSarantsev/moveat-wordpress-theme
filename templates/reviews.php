<?php
/*
	Template Name: Отзывы
	Template Post Type: page
*/

require_once get_template_directory() . '/assets/scripts/php/reviews/reviews-topic-helpers.php';

if (! function_exists('moveat_reviews_social_icon_class')) {
	function moveat_reviews_social_icon_class($type) {
		$map = [
			'instagram' => 'fab fa-instagram reviews-page__social-icon',
			'telegram'  => 'fab fa-telegram-plane reviews-page__social-icon',
			'facebook'  => 'fab fa-facebook-f reviews-page__social-icon',
			'link'        => 'fas fa-link reviews-page__social-icon',
		];
		$t = is_string($type) ? $type : '';
		return isset($map[ $t ]) ? $map[ $t ] : $map['instagram'];
	}
}

if (! function_exists('moveat_reviews_is_truthy')) {
	function moveat_reviews_is_truthy($val) {
		return $val === true || $val === 1 || $val === '1';
	}
}

$page_id = get_queried_object_id();

$filter_title       = 'Темы отзывов:';
$filter_hint        = '';
$filter_select_all  = 'Выбрать все';
$filter_clear       = 'Очистить фильтр';
$empty_message      = '';

if (function_exists('get_field')) {
	$t = get_field('reviews_filter_title', $page_id);
	if (is_string($t) && trim($t) !== '') {
		$filter_title = trim($t);
	}
	$h = get_field('reviews_filter_hint', $page_id);
	if (is_string($h)) {
		$filter_hint = trim($h);
	}
	$s = get_field('reviews_filter_select_all', $page_id);
	if (is_string($s) && trim($s) !== '') {
		$filter_select_all = trim($s);
	}
	$c = get_field('reviews_filter_clear', $page_id);
	if (is_string($c) && trim($c) !== '') {
		$filter_clear = trim($c);
	}
	$e = get_field('reviews_empty_message', $page_id);
	if (is_string($e)) {
		$empty_message = trim($e);
	}
}

if ($filter_hint === '') {
	$filter_hint = 'Отметьте одну или несколько тем. Показываются отзывы, где встречается хотя бы одна выбранная тема. Без выбора видны все отзывы.';
}
if ($empty_message === '') {
	$empty_message = 'По выбранным темам пока нет отзывов. Снимите часть фильтров или очистите выбор полностью.';
}

get_header();
?>

<!-- Page Header Start -->
<div class="hero-block">
	<div class="hero-block__bg-wrapper">
		<img
			class="hero-block__bg-image"
			src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/illustrations/vegetables.jpg' ); ?>"
			alt="<?php echo esc_attr__( 'vegetables', 'moveat' ); ?>" />
	</div>
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php echo esc_html(get_the_title()); ?></h1>
		<nav aria-label="breadcrumb no-padding animated slideInDown page-hero__breadcrumbs">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<a class="text-body" href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
				</li>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<span class="text-body"><?php echo esc_html(get_the_title()); ?></span>
				</li>
			</ol>
		</nav>
	</div>
</div>
<!-- Page Header End -->

<div class="reviews-page__container max-width-limiter" data-reviews-page>
	<section
		class="reviews-page__filter"
		aria-labelledby="reviews-filter-heading">
		<h2 class="reviews-page__filter-label anim-fade-up _delay-01" id="reviews-filter-heading">
			<?php echo esc_html($filter_title); ?>
		</h2>
		<p class="reviews-page__hint anim-fade-up _delay-02" id="reviews-filter-hint">
			<?php echo esc_html($filter_hint); ?>
		</p>
		<div class="reviews-page__filter-chips-row anim-fade-up">
			<div
				class="reviews-page__filter-chips"
				data-reviews-filter-chips
				role="group"
				aria-describedby="reviews-filter-hint"></div>
			<div class="reviews-page__filter-actions">
				<button
					type="button"
					class="reviews-page__filter-select-all anim-button-pop"
					data-reviews-select-all>
					<?php echo esc_html($filter_select_all); ?>
				</button>
				<button
					type="button"
					class="reviews-page__filter-clear-filter anim-button-pop"
					data-reviews-clear-filter>
					<?php echo esc_html($filter_clear); ?>
				</button>
			</div>
		</div>
	</section>

	<div class="reviews-page__list-wrap anim-fade-up _delay-03" data-reviews-list-wrap>
		<div
			class="reviews-page__list-loader"
			data-reviews-loader
			aria-hidden="true">
			<div class="loader" role="presentation"></div>
		</div>
		<div class="reviews-page__list" data-reviews-list>
			<?php
			for ($i = 1; $i <= 30; $i++) {
				if (! function_exists('get_field')) {
					break;
				}
				$quote = get_field('review_' . $i . '_quote', $page_id);
				if (! is_string($quote) || trim($quote) === '') {
					continue;
				}
				$reverse      = moveat_reviews_is_truthy(get_field('review_' . $i . '_reverse', $page_id));
				$media_type   = get_field('review_' . $i . '_media_type', $page_id);
				$media_type   = is_string($media_type) && $media_type === 'video' ? 'video' : 'image';
				$image_url    = get_field('review_' . $i . '_image', $page_id);
				$image_url    = is_string($image_url) ? trim($image_url) : '';
				$image_alt    = get_field('review_' . $i . '_image_alt', $page_id);
				$image_alt    = is_string($image_alt) ? trim($image_alt) : '';
				$video_url    = get_field('review_' . $i . '_video_url', $page_id);
				$video_url    = is_string($video_url) ? trim($video_url) : '';
				$video_poster = get_field('review_' . $i . '_video_poster', $page_id);
				$video_poster = is_string($video_poster) ? trim($video_poster) : '';

				$social_type  = get_field('review_' . $i . '_social_type', $page_id);
				$social_type  = is_string($social_type) ? $social_type : 'instagram';
				$social_label = get_field('review_' . $i . '_social_label', $page_id);
				$social_label = is_string($social_label) ? trim($social_label) : '';
				$social_url   = get_field('review_' . $i . '_social_url', $page_id);
				$social_url   = is_string($social_url) ? trim($social_url) : '';

				$city   = get_field('review_' . $i . '_city', $page_id);
				$city   = is_string($city) ? trim($city) : '';
				$author = get_field('review_' . $i . '_author', $page_id);
				$author = is_string($author) ? trim($author) : '';
				$topics = get_field('review_' . $i . '_topics', $page_id);

				$topic_list = moveat_reviews_normalize_topics_field($topics);
				$chips_attr = implode('|', $topic_list);

				$card_classes = 'reviews-page__card';
				if ($reverse) {
					$card_classes .= ' reviews-page__card--reverse';
				}
				?>
				<article
					class="<?php echo esc_attr($card_classes); ?>"
					data-review
					<?php if ($chips_attr !== '') : ?>
						data-chips="<?php echo esc_attr($chips_attr); ?>"
					<?php endif; ?>>
					<div class="reviews-page__media">
						<?php if ($media_type === 'video' && $video_url !== '') : ?>
							<video
								class="reviews-page__video"
								controls
								playsinline
								preload="metadata"
								<?php if ($video_poster !== '') : ?>
									poster="<?php echo esc_url($video_poster); ?>"
								<?php endif; ?>>
								<source src="<?php echo esc_url($video_url); ?>" type="video/mp4" />
								<?php esc_html_e('Ваш браузер не поддерживает воспроизведение видео.', 'moveat'); ?>
							</video>
						<?php elseif ($image_url !== '') : ?>
							<img
								class="reviews-page__media-inner"
								src="<?php echo esc_url($image_url); ?>"
								alt="<?php echo esc_attr($image_alt !== '' ? $image_alt : $author); ?>"
								width="640"
								height="400"
								loading="lazy" />
						<?php endif; ?>
					</div>
					<div class="reviews-page__body">
						<?php if ($social_url !== '' || $city !== '') : ?>
							<div class="reviews-page__row-meta">
								<?php if ($social_url !== '') : ?>
									<a
										class="reviews-page__social"
										href="<?php echo esc_url($social_url); ?>"
										target="_blank"
										rel="noopener noreferrer">
										<i
											class="<?php echo esc_attr(moveat_reviews_social_icon_class($social_type)); ?>"
											aria-hidden="true"></i>
										<?php echo esc_html($social_label); ?>
									</a>
								<?php endif; ?>
								<?php if ($city !== '') : ?>
									<span class="reviews-page__city"><?php echo esc_html($city); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ($author !== '') : ?>
							<p class="reviews-page__author"><?php echo esc_html($author); ?></p>
						<?php endif; ?>
						<blockquote class="reviews-page__quote">
							<?php echo wp_kses_post(wpautop(trim($quote))); ?>
						</blockquote>
						<?php if (! empty($topic_list)) : ?>
							<div class="reviews-page__topics" aria-label="<?php echo esc_attr__('Темы отзыва', 'moveat'); ?>">
								<?php foreach ($topic_list as $topic_label) : ?>
									<span class="reviews-page__topic-pill"><?php echo esc_html($topic_label); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</article>
				<?php
			}
			?>
		</div>
		<p class="reviews-page__empty" data-reviews-empty>
			<?php echo esc_html($empty_message); ?>
		</p>
	</div>
</div>

<?php
get_footer();
