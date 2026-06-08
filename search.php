<?php
/*
 Страница результатов поиска
*/
defined( 'ABSPATH' ) || exit;

$search_query = get_search_query();
$found_posts  = (int) $GLOBALS['wp_query']->found_posts;
$icons_uri    = get_template_directory_uri() . '/assets/images/icons/filled/';

get_header();
?>

<!-- Search Results Start -->
<div class="search-results">
	<div class="search-results__container max-width-limiter" data-search-results>
		<?php if ( $search_query ) : ?>
			<p class="search-results__query">
				Результаты по запросу:
				<span class="search-results__query-phrase">«<?php echo esc_html( $search_query ); ?>»</span>
			</p>
		<?php endif; ?>

		<form
			role="search"
			method="get"
			class="search-results__form"
			action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="search-results__label" for="search-results-input">
				<?php esc_html_e( 'Новый поиск', 'moveat' ); ?>
			</label>
			<div class="search-results__field">
				<div class="search-results__input-wrapper">
					<span class="search-results__input-icon" aria-hidden="true">
						<img src="<?php echo get_template_directory_uri() . '/assets/images/icons/search.png' ?>" alt="search icon">
					</span>
					<input
						id="search-results-input"
						class="search-results__input"
						type="search"
						name="s"
						value="<?php echo esc_attr( $search_query ); ?>"
						placeholder="<?php esc_attr_e( 'Введите запрос…', 'moveat' ); ?>"
						required>
				</div>
				<button class="search-results__submit primary-button" type="submit">
					<?php esc_html_e( 'Найти', 'moveat' ); ?>
				</button>
			</div>
		</form>

		<?php if ( have_posts() ) : ?>
			<p class="search-results__count">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of search results */
						_n( 'Найден %d результат', 'Найдено %d результатов', $found_posts, 'moveat' ),
						$found_posts
					)
				);
				?>
			</p>

			<!-- Чипы-фильтры по типу добавляются скриптом (search-results.ts) -->
			<div class="search-results__filters" data-search-filters hidden>
				<span class="search-results__filters-label">Тип:</span>
				<div
					class="search-results__filters-chips"
					data-search-filter-chips></div>
			</div>

			<div class="search-results__grid articles__grid">
				<?php
				while ( have_posts() ) :
					the_post();

					$post_type      = get_post_type();
					$type_modifier  = 'post';
					$type_icon      = 'article.png';
					$type_label     = 'Статья';

					if ( 'page' === $post_type ) {
						$type_modifier = 'page';
						$type_icon     = 'page.png';
						$type_label    = 'Страница';
					} elseif ( 'product' === $post_type ) {
						$type_modifier = 'product';
						$type_icon     = 'product.png';
						$type_label    = 'Товар';
					}

					$excerpt_text = wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) );
					$excerpt_text = preg_replace( '/\s+/u', ' ', trim( html_entity_decode( $excerpt_text, ENT_QUOTES, 'UTF-8' ) ) );

					if ( mb_strlen( $excerpt_text ) > 120 ) {
						$excerpt_text = mb_substr( $excerpt_text, 0, 120 ) . ' […]';
					}
					?>
					<a href="<?php the_permalink(); ?>" target="_blank" rel="noopener" class="search-results__card articles__card article-card">
						<div class="article-card__top">
							<div class="article-card__content">
								<span class="search-results__type search-results__type--<?php echo esc_attr( $type_modifier ); ?>">
									<img src="<?php echo esc_url( $icons_uri . $type_icon ); ?>" alt="">
									<div><?php echo esc_html( $type_label ); ?></div>
								</span>
								<h3 class="article-card__title"><?php the_title(); ?></h3>
								<?php if ( $excerpt_text ) : ?>
									<p class="search-results__excerpt"><?php echo esc_html( $excerpt_text ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<div class="article-card__bottom">
							<?php
							$search_btn_title = __( 'Подробнее', 'moveat' );
							if ( 'product' === $post_type && function_exists( 'moveat_get_product_button_title' ) ) {
								$search_btn_title = moveat_get_product_button_title( get_the_ID() );
							} elseif ( 'post' === $post_type && function_exists( 'moveat_get_post_button_title' ) ) {
								$search_btn_title = moveat_get_post_button_title( get_the_ID() );
							}
							?>
							<span class="article-card__button primary-button"><?php echo esc_html( $search_btn_title ); ?></span>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<div class="search-results__empty" data-search-empty hidden>
				<span class="search-results__empty-icon" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/search.png' ); ?>" alt="">
				</span>
				<p class="search-results__empty-text">
					По выбранным фильтрам ничего не найдено.
				</p>
			</div>

			<!-- Пагинация добавляется скриптом (search-results.ts) -->
			<nav
				class="pagination max-width-limiter"
				data-search-pagination
				aria-label="Постраничная навигация по результатам"
				hidden></nav>
		<?php else : ?>
			<div class="search-results__empty">
				<span class="search-results__empty-icon" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/search.png' ); ?>" alt="">
				</span>
				<?php if ( $search_query ) : ?>
					<p class="search-results__empty-text">
						<?php
						echo esc_html(
							sprintf(
								'По запросу «%s» ничего не найдено. Попробуйте изменить формулировку.',
								$search_query
							)
						);
						?>
					</p>
				<?php else : ?>
					<p class="search-results__empty-text">Введите запрос в поле выше.</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<!-- Search Results End -->

<?php get_footer(); ?>
