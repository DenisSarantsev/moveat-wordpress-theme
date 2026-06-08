<?php
defined( 'ABSPATH' ) || exit;

$term = get_queried_object();
if ( ! $term instanceof WP_Term || 'product_cat' !== $term->taxonomy ) {
	return;
}

$theme_uri = get_template_directory_uri();
$cart_icon = $theme_uri . '/assets/images/icons/cart.png';

$button_title = function_exists( 'moveat_get_category_button_title' ) ? moveat_get_category_button_title( $term->term_id ) : 'Подробнее';

$shop_id   = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
$shop_url  = $shop_id > 0 ? get_permalink( $shop_id ) : '';
$shop_name = $shop_id > 0 ? get_the_title( $shop_id ) : '';

$term_description = get_term_field( 'description', $term );
if ( is_wp_error( $term_description ) || ! is_string( $term_description ) ) {
	$term_description = '';
} else {
	$term_description = trim( wp_strip_all_tags( $term_description ) );
}
?>

<div class="hero-block">
	<div class="hero-block__bg-wrapper">
		<img
			class="hero-block__bg-image"
			src="<?php echo esc_url( $theme_uri . '/assets/images/illustrations/vegetables.jpg' ); ?>"
			alt="<?php echo esc_attr__( 'vegetables', 'moveat' ); ?>" />
	</div>
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php echo esc_html( $term->name ); ?></h1>
		<nav aria-label="breadcrumb no-padding animated slideInDown page-hero__breadcrumbs">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><a class="text-body" href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a></li>
				<?php if ( $shop_url ) : ?>
					<li class="breadcrumb-item page-hero__breadcrumbs-item white"><a class="text-body" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( $shop_name ? $shop_name : __( 'Каталог', 'moveat' ) ); ?></a></li>
				<?php endif; ?>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white"><span class="text-body"><?php echo esc_html( $term->name ); ?></span></li>
			</ol>
		</nav>
	</div>
</div>

<div class="products">
	<div class="products__container max-width-limiter">
		<?php if ( $term_description ) : ?>
			<div class="products__header title-subtitle-header">
				<p class="products__description"><?php echo esc_html( $term_description ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="products__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;
					if ( ! $product instanceof WC_Product ) {
						continue;
					}

					$usd_price = wc_get_price_to_display( $product );
					$uah_price = function_exists( 'moveat_get_uah_price_for_product' ) ? moveat_get_uah_price_for_product( $product ) : '';
					$excerpt   = get_post_field( 'post_excerpt', get_the_ID() );
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
								<a href="<?php the_permalink(); ?>" class="product-card__button primary-button"><?php echo esc_html( $button_title ); ?></a>
								<a
									href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
									data-quantity="1"
									class="product-card__button product-card__button-cart"
									data-product_id="<?php echo esc_attr( get_the_ID() ); ?>"
									data-add-to-cart
									data-product-action="add-to-cart"
									data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"
									aria-label="<?php echo esc_attr( sprintf( 'Добавить "%s" в корзину', get_the_title() ) ); ?>"
								>
									<img class="product-card__button-cart-icon" src="<?php echo esc_url( $cart_icon ); ?>" alt="Cart">
									<div class="loader disabled"></div>
								</a>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>

			<?php if ( function_exists( 'woocommerce_pagination' ) ) : ?>
				<div class="products__pagination">
					<?php woocommerce_pagination(); ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="products__description"><?php esc_html_e( 'В этой категории пока нет товаров.', 'moveat' ); ?></p>
		<?php endif; ?>
	</div>
</div>
