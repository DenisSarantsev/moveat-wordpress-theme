<?php
/*
	Template Name: Школа здоровья
	Template Post Type: page
*/

get_header();

$theme_uri = get_template_directory_uri();
$cart_icon = $theme_uri . '/assets/images/icons/cart.png';

$product_sections = [];

for ( $index = 1; $index <= 10; $index++ ) {
	$category_id = function_exists( 'get_field' ) ? (int) get_field( 'products_category_' . $index ) : 0;
	$show_all    = function_exists( 'get_field' ) ? (bool) get_field( 'products_show_all_' . $index ) : false;
	$title       = function_exists( 'get_field' ) ? trim( (string) get_field( 'products_title_' . $index ) ) : '';
	$description = function_exists( 'get_field' ) ? trim( (string) get_field( 'products_description_' . $index ) ) : '';
	$selected    = function_exists( 'get_field' ) ? get_field( 'products_list_' . $index ) : [];

	$selected_ids = array_values(
		array_filter(
			array_map( 'intval', is_array( $selected ) ? $selected : [] )
		)
	);

	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	];

	if ( $category_id > 0 ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => [ $category_id ],
			],
		];
	}

	if ( $show_all ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	} else {
		if ( empty( $selected_ids ) ) {
			continue;
		}
		$args['post__in'] = $selected_ids;
		$args['orderby']  = 'post__in';
	}

	$products_query = new WP_Query( $args );
	if ( ! $products_query->have_posts() ) {
		wp_reset_postdata();
		continue;
	}

	$product_sections[] = [
		'title'       => $title,
		'description' => $description,
		'query'       => $products_query,
	];
}
?>

<div class="hero-block">
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php the_title(); ?></h1>
		<nav aria-label="breadcrumb no-padding animated slideInDown page-hero__breadcrumbs">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><a class="text-body" href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a></li>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><span class="text-body"><?php the_title(); ?></span></li>
			</ol>
		</nav>
	</div>
</div>

<div class="products">
	<?php foreach ( $product_sections as $section ) : ?>
		<div class="products__container max-width-limiter">
			<?php if ( $section['title'] || $section['description'] ) : ?>
				<div class="products__header title-subtitle-header">
					<?php if ( $section['title'] ) : ?>
						<h2 class="products__title section-title"><?php echo esc_html( $section['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $section['description'] ) : ?>
						<p class="products__description"><?php echo esc_html( $section['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="products__grid">
				<?php while ( $section['query']->have_posts() ) : $section['query']->the_post(); ?>
					<?php
					$product = wc_get_product( get_the_ID() );
					if ( ! $product instanceof WC_Product ) {
						continue;
					}

					$usd_price = wc_get_price_to_display( $product );
					$uah_price = function_exists( 'moveat_get_uah_price_for_product' ) ? moveat_get_uah_price_for_product( $product ) : '';
					$excerpt = get_post_field( 'post_excerpt', get_the_ID() );
					if ( ! $excerpt ) {
						$excerpt = get_post_field( 'post_content', get_the_ID() );
					}
					?>
					<div class="products__card product-card">
						<div class="product-card__top">
							<div class="product-card__image-wrapper">
								<a href="<?php the_permalink(); ?>">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'woocommerce_thumbnail', [ 'class' => 'product-card__image' ] ); ?>
									<?php else : ?>
										<img class="product-card__image" src="<?php echo esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
									<?php endif; ?>
								</a>
							</div>
							<div class="product-card__content">
								<h3 class="product-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="product-card__description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 22 ) ); ?></p>
							</div>
						</div>
						<div class="product-card__bottom">
							<div class="product-card__price">
								<div class="product-card__price-usd">
									<div class="product-card__price-usd-label">$</div>
									<div class="product-card__price-usd-value"><?php echo esc_html( wc_format_decimal( $usd_price, wc_get_price_decimals() ) ); ?></div>
								</div>
								<?php if ( '' !== $uah_price && null !== $uah_price ) : ?>
									<div class="product-card__price-uah">
										<div class="product-card__price-uah-label">₴</div>
										<div class="product-card__price-uah-value"><?php echo esc_html( wc_format_decimal( $uah_price, 0 ) ); ?></div>
									</div>
								<?php endif; ?>
							</div>
							<div class="product-card__buttons">
								<a href="<?php the_permalink(); ?>" class="product-card__button primary-button">Подробнее</a>
								<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" class="product-card__button product-card__button-cart add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo esc_attr( get_the_ID() ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Добавить "%s" в корзину', get_the_title() ) ); ?>">
									<img src="<?php echo esc_url( $cart_icon ); ?>" alt="Cart">
									<div class="loader disabled"></div>
								</a>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php endforeach; ?>
</div>

<?php get_footer(); ?>
