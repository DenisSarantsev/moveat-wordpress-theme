<?php
/*
 Страница результатов поиска
*/
defined( 'ABSPATH' ) || exit;

$search_query = get_search_query();
$found_posts  = (int) $GLOBALS['wp_query']->found_posts;
$icons_uri    = get_template_directory_uri() . '/assets/images/icons/filled/';

// Тексты вёрстки на языке страницы — assets/dictionaries/templates/search.
$page_dict = moveat_dictionary( 'templates/search' );
$t = function ( $key ) use ( $page_dict ) {
	return moveat_dictionary_text( $page_dict, $key );
};

get_header();
?>

<!-- Search Results Start -->
<div class="search-results">
	<div class="search-results__container max-width-limiter" data-search-results>
		<?php if ( $search_query ) : ?>
			<p class="search-results__query">
				<?php echo esc_html( $t( 'query_label' ) ); ?>
				<span class="search-results__query-phrase">«<?php echo esc_html( $search_query ); ?>»</span>
			</p>
		<?php endif; ?>

		<form
			role="search"
			method="get"
			class="search-results__form"
			action="<?php echo esc_url( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ); ?>">
			<label class="search-results__label" for="search-results-input">
				<?php echo esc_html( $t( 'new_search' ) ); ?>
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
						placeholder="<?php echo esc_attr( $t( 'placeholder' ) ); ?>"
						required>
				</div>
				<button class="search-results__submit primary-button" type="submit">
					<?php echo esc_html( $t( 'submit' ) ); ?>
				</button>
			</div>
		</form>

		<?php if ( have_posts() ) : ?>
			<p class="search-results__count">
				<?php
				echo esc_html( moveat_dictionary_plural( $page_dict, 'count', $found_posts ) );
				?>
			</p>

			<!-- Чипы-фильтры по типу добавляются скриптом (search-results.ts) -->
			<div class="search-results__filters" data-search-filters hidden>
				<span class="search-results__filters-label"><?php echo esc_html( $t( 'filters_label' ) ); ?></span>
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
					$type_label     = $t( 'type_post' );

					if ( 'page' === $post_type ) {
						$type_modifier = 'page';
						$type_icon     = 'page.png';
						$type_label    = $t( 'type_page' );
					} elseif ( 'product' === $post_type ) {
						$type_modifier = 'product';
						$type_icon     = 'product.png';
						$type_label    = $t( 'type_product' );
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
							// Для страниц своего поля нет — дефолт на языке сайта («Подробнее» / «Детальніше»).
							$search_btn_title = function_exists( 'moveat_default_button_title' ) ? moveat_default_button_title( moveat_category_button_current_lang() ) : 'Подробнее';
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
					<?php echo esc_html( $t( 'empty_filtered' ) ); ?>
				</p>
			</div>

			<!-- Пагинация добавляется скриптом (search-results.ts) -->
			<nav
				class="pagination max-width-limiter"
				data-search-pagination
				aria-label="<?php echo esc_attr( $t( 'pagination_label' ) ); ?>"
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
								$t( 'empty_query' ),
								$search_query
							)
						);
						?>
					</p>
				<?php else : ?>
					<p class="search-results__empty-text"><?php echo esc_html( $t( 'empty_no_query' ) ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<!-- Search Results End -->

<?php get_footer(); ?>
