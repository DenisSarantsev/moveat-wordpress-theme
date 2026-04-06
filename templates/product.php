<?php get_header(); ?>
	<?php
		global $post;
		$product = function_exists('wc_get_product') ? wc_get_product( $post ? $post->ID : 0 ) : null;
		$theme_uri = get_template_directory_uri();
		$icons_base = $theme_uri . '/assets/icons/formats';
		$fallback_icons = $theme_uri . '/assets/images/icons/colored';
		$moveat_formats_map = [
			'pdf'   => [
				'icon'  => ( file_exists( get_template_directory() . '/assets/icons/formats/pdf.svg' ) ? $icons_base . '/pdf.svg' : $fallback_icons . '/pdf.png' ),
				'label' => __( 'PDF файл', 'moveat' ),
			],
			'audio' => [
				'icon'  => ( file_exists( get_template_directory() . '/assets/icons/formats/audio.svg' ) ? $icons_base . '/audio.svg' : $fallback_icons . '/audio.png' ),
				'label' => __( 'Аудио файл', 'moveat' ),
			],
			'video' => [
				'icon'  => $icons_base . '/video.svg',
				'label' => __( 'Видео файл', 'moveat' ),
			],
			'zip'   => [
				'icon'  => $icons_base . '/zip.svg',
				'label' => __( 'Архив ZIP', 'moveat' ),
			],
			'image' => [
				'icon'  => $icons_base . '/image.svg',
				'label' => __( 'Изображения', 'moveat' ),
			],
			'text'  => [
				'icon'  => $icons_base . '/txt.svg',
				'label' => __( 'Текстовый файл', 'moveat' ),
			],
		];
		$moveat_get_primary_price = function( $p ) {
			if ( ! ( $p instanceof WC_Product ) ) {
				return 0.0;
			}
			$price = function_exists( 'wc_get_price_to_display' ) ? wc_get_price_to_display( $p ) : 0;
			return is_numeric( $price ) ? (float) $price : 0.0;
		};
		$moveat_get_uah_rate = function () {
			// YayCurrency (free/pro): get configured UAH rate with fee.
			if ( class_exists( '\Yay_Currency\Helpers\YayCurrencyHelper' ) ) {
				$currencies   = \Yay_Currency\Helpers\YayCurrencyHelper::converted_currency();
				$uah_currency = \Yay_Currency\Helpers\YayCurrencyHelper::get_currency_by_currency_code( 'UAH', $currencies );
				if ( $uah_currency ) {
					$rate = \Yay_Currency\Helpers\YayCurrencyHelper::get_rate_fee( $uah_currency );
					if ( $rate ) {
						return (float) $rate;
					}
				}
			}
			if ( has_filter( 'woocs_exchange_value' ) ) {
				$rate = apply_filters( 'woocs_exchange_value', 1, 'UAH', 'USD' );
				if ( $rate ) return (float) $rate;
			}
			if ( function_exists( 'wmc_get_price' ) ) {
				$rate = (float) wmc_get_price( 1, 'UAH' );
				if ( $rate ) return $rate;
			}
			$rate = get_option( 'moveat_uah_rate' );
			return $rate ? (float) $rate : 0.0;
		};
		$moveat_get_uah_price = function( $p ) use ( $moveat_get_uah_rate, $moveat_get_primary_price ) {
			if ( ! ( $p instanceof WC_Product ) ) return '';
			if ( function_exists( 'moveat_get_uah_price_for_product' ) ) {
				$uah = moveat_get_uah_price_for_product( $p );
				if ( is_numeric( $uah ) ) return round( (float) $uah );
			}
			$usd = $moveat_get_primary_price( $p );
			$rate = $moveat_get_uah_rate();
			if ( ! $rate ) return '';
			return round( $usd * $rate );
		};
	?>

    <!-- Programs Start -->
    <section class="product-page">
			<div class="product-breadcrumbs">
				<div class="product-breadcrumbs__container max-width-limiter">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb product-breadcrumbs__list">
							<?php
								if ( function_exists( 'woocommerce_breadcrumb' ) ) {
									woocommerce_breadcrumb( [
										'wrap_before' => '',
										'wrap_after'  => '',
										'before'      => '<li class="breadcrumb-item product-breadcrumbs__item">',
										'after'       => '</li>',
										'delimiter'   => '',
										'home'        => _x( 'Главная', 'breadcrumb', 'moveat' ),
									] );
								}
							?>
						</ol>
					</nav>
				</div>
			</div>
      <div class="product-page__container max-width-limiter">
        <div class="product-page__gallery" data-product-gallery>
          <div class="product-page__main-image-wrapper">
            <button class="product-page__arrow product-page__arrow--prev" type="button" aria-label="Предыдущее фото" data-gallery-prev>
              <i class="bi bi-chevron-left"></i>
            </button>
						<div class="product-page__main-image" data-gallery-main
							<?php
								// Проставим текущий full URL для лайтбокса
								if ( $product ) {
									$main_id   = $product->get_image_id();
									$main_full = $main_id ? wp_get_attachment_image_url( $main_id, 'full' ) : get_the_post_thumbnail_url( $product->get_id(), 'full' );
									if ( $main_full ) {
										echo ' data-current-full="' . esc_url( $main_full ) . '"';
									}
								}
							?>
						>
							<?php
								if ( $product && function_exists('wc_get_gallery_image_html') ) {
									$main_id = $product->get_image_id();
									if ( $main_id ) {
										echo wc_get_gallery_image_html( $main_id, true );
									} else {
										echo get_the_post_thumbnail( $product->get_id(), 'large' );
									}
								}
							?>
						</div>
            <button class="product-page__arrow product-page__arrow--next" type="button" aria-label="Следующее фото" data-gallery-next>
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
          <div class="product-page__thumbnails" role="tablist" aria-label="Галерея товара">
						<?php
							if ( $product ) {
								$thumb_ids = $product->get_gallery_image_ids();
								if ( empty( $thumb_ids ) ) {
									$main_id = $product->get_image_id();
									if ( $main_id ) {
										$full = wp_get_attachment_image_url( $main_id, 'full' );
										echo '<button class="product-page__thumb is-active" type="button" data-gallery-thumb data-index="0" aria-label="Фото 1" data-full="' . esc_url( $full ) . '">';
										echo wp_get_attachment_image( $main_id, 'thumbnail', false, [ 'data-full' => $full ] );
										echo '</button>';
									}
								} else {
									$index = 0;
									foreach ( $thumb_ids as $tid ) {
										$is_active = $index === 0 ? ' is-active' : '';
										$full     = wp_get_attachment_image_url( $tid, 'full' );
										echo '<button class="product-page__thumb' . esc_attr( $is_active ) . '" type="button" data-gallery-thumb data-index="' . esc_attr( $index ) . '" aria-label="Фото ' . esc_attr( $index + 1 ) . '" data-full="' . esc_url( $full ) . '">';
										echo wp_get_attachment_image( $tid, 'thumbnail', false, [ 'data-full' => $full ] );
										echo '</button>';
										$index++;
									}
								}
							}
						?>
          </div>
        </div>

        <div class="product-page__content">
          <h2 class="product-page__title"><?php echo esc_html( get_the_title() ); ?></h2>
          <div class="product-page__description">
						<?php
							if ( $product ) {
								$short = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
								echo wp_kses_post( wpautop( $short ) );
							}
						?>
          </div>
					<div class="product-page__audio-player">
						<?php
							if ( function_exists('get_field') ) {
								$audio = get_field( 'product_audio' );
								$src = is_array( $audio ) && ! empty( $audio['url'] ) ? $audio['url'] : ( is_string( $audio ) ? $audio : '' );
								if ( $src ) {
									echo '<div class="product-page__audio-player-title">' . esc_html__( 'Аудио фрагмент:', 'moveat' ) . '</div>';
									echo '<audio src="' . esc_url( $src ) . '" controls></audio>';
								}
							}
						?>
					</div>
					<div class="product-page__formats" aria-label="Форматы получения товара">
            <div class="product-page__formats-title">
							Вы получаете:
						</div>
						<div class="product-page__formats-wrapper">
							<?php
								if ( function_exists('get_field') ) {
									$formats = (array) get_field( 'product_formats' );
									foreach ( $formats as $key ) {
										if ( empty( $moveat_formats_map[ $key ] ) ) continue;
										$icon = $moveat_formats_map[ $key ]['icon'];
										$label = $moveat_formats_map[ $key ]['label'];
										echo '<div class="product-page__format-item">';
										echo '<img src="' . esc_url( $icon ) . '" alt="' . esc_attr( $label ) . '">';
										echo '<div>' . esc_html( $label ) . '</div>';
										echo '</div>';
									}
								}
							?>
						</div>
          </div>
          <div class="product-page__price">
            <span class="product-page__price-currency">$</span>
            <span class="product-page__price-value">
							<?php
								echo esc_html( wc_format_decimal( $moveat_get_primary_price( $product ), wc_get_price_decimals() ) );
							?>
						</span>
						<?php $uah = $moveat_get_uah_price( $product ); ?>
						<?php if ( $uah !== '' ) : ?>
							<span class="product-page__price-secondary">
								<?php echo '₴' . esc_html( wc_format_decimal( $uah, 0 ) ); ?>
							</span>
						<?php endif; ?>
          </div>
          <div class="product-page__buttons">
            <a href="#" class="primary-button" data-product-action="buy-now" data-product-id="<?php echo esc_attr( $product ? $product->get_id() : 0 ); ?>">Купить</a>
            <a href="#" class="secondary-button" data-product-action="add-to-cart" data-product-id="<?php echo esc_attr( $product ? $product->get_id() : 0 ); ?>">Добавить в корзину</a>
          </div>
        </div>
      </div>
			<div class="product-page__full-description">
				<div class="product-page__full-description-content">
					<h3 class="product-page__full-description-title">Полное описание</h3>
					<div class="product-page__full-description-text">
						<?php the_content(); ?>
					</div>
				</div>
			</div>

      <div class="product-lightbox" data-product-lightbox aria-hidden="true">
        <button class="product-lightbox__close" type="button" aria-label="Закрыть" data-lightbox-close>
          <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/cross.png' ); ?>" alt="Закрыть">
        </button>
        <button class="product-lightbox__arrow product-lightbox__arrow--prev" type="button" aria-label="Предыдущее фото" data-lightbox-prev>
          <i class="bi bi-chevron-left"></i>
        </button>
        <img class="product-lightbox__image" src="" alt="Изображение товара" data-lightbox-image>
        <button class="product-lightbox__arrow product-lightbox__arrow--next" type="button" aria-label="Следующее фото" data-lightbox-next>
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
			
    </section>

		<?php
			$related_ids = [];
			if ( $product instanceof WC_Product ) {
				$product_cat_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
				if ( ! empty( $product_cat_ids ) ) {
					$related_query = new WP_Query( [
						'post_type'      => 'product',
						'post_status'    => 'publish',
						'posts_per_page' => 4,
						'post__not_in'   => [ $product->get_id() ],
						'tax_query'      => [
							[
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $product_cat_ids,
							],
						],
					] );

					if ( $related_query->have_posts() ) {
						$related_ids = wp_list_pluck( $related_query->posts, 'ID' );
					}
					wp_reset_postdata();
				}
			}
		?>
		<?php if ( ! empty( $related_ids ) ) : ?>
			<section class="product-related">
				<div class="product-related__container max-width-limiter">
					<div class="title-subtitle-header">
						<h2 class="section-title">Похожие товары</h2>
						<p class="text-secondary">Выберите материалы, которые дополнят программу и помогут закрепить результат.</p>
					</div>
					<div class="product-related__grid">
						<?php foreach ( $related_ids as $related_id ) : ?>
							<?php
								$related_product = wc_get_product( $related_id );
								if ( ! $related_product ) {
									continue;
								}
								$related_uah = $moveat_get_uah_price( $related_product );
							?>
							<div class="product-card">
								<div class="product-card__top">
									<div class="product-card__image-wrapper">
										<a href="<?php echo esc_url( get_permalink( $related_id ) ); ?>">
											<?php echo get_the_post_thumbnail( $related_id, 'woocommerce_thumbnail', [ 'class' => 'product-card__image' ] ); ?>
										</a>
									</div>
									<div class="product-card__content">
										<h3 class="product-card__title">
											<a href="<?php echo esc_url( get_permalink( $related_id ) ); ?>">
												<?php echo esc_html( get_the_title( $related_id ) ); ?>
											</a>
										</h3>
										<p class="product-card__description"><?php echo esc_html( wp_trim_words( get_post_field( 'post_excerpt', $related_id ) ?: get_post_field( 'post_content', $related_id ), 18 ) ); ?></p>
									</div>
								</div>
								<div class="product-card__bottom">
									<div class="product-card__price">
										<div class="product-card__price-usd"><div>$</div><div><?php echo esc_html( wc_format_decimal( $moveat_get_primary_price( $related_product ), wc_get_price_decimals() ) ); ?></div></div>
										<?php if ( $related_uah !== '' ) : ?>
											<div class="product-card__price-uah"><div>₴</div><div><?php echo esc_html( wc_format_decimal( $related_uah, 0 ) ); ?></div></div>
										<?php endif; ?>
									</div>
									<div class="product-card__buttons">
										<a href="<?php echo esc_url( get_permalink( $related_id ) ); ?>" class="primary-button">Купить</a>
										<a href="<?php echo esc_url( $related_product->add_to_cart_url() ); ?>" data-quantity="1" class="product-card__button-cart add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo esc_attr( $related_id ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Добавить "%s" в корзину', get_the_title( $related_id ) ) ); ?>">
											<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/cart.png' ); ?>" alt="Корзина">
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- Product End -->
	<?php get_footer(); ?>