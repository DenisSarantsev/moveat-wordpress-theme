<?php
/*
	Template Name: Корзина
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri        = get_template_directory_uri();
$theme_dir        = get_template_directory();
$shop_url         = home_url( '/kursy/' );
$checkout_url     = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '#';
$cart_icon        = $theme_uri . '/assets/images/icons/cart.png';
$minus_icon       = $theme_uri . '/assets/images/icons/minus.png';
$plus_icon        = $theme_uri . '/assets/images/icons/plus.png';
$cross_icon       = $theme_uri . '/assets/images/icons/cross.png';
$telegram_icon    = $theme_uri . '/assets/images/icons/telegram.png';
$whatsapp_icon    = $theme_uri . '/assets/images/icons/whatsapp.png';
$viber_icon       = $theme_uri . '/assets/images/icons/viber.png';
$has_woo_cart     = function_exists( 'WC' ) && WC()->cart;
$cart_items       = $has_woo_cart ? WC()->cart->get_cart() : [];
$cart_is_empty    = empty( $cart_items );
$cart_items_count = $has_woo_cart ? (int) WC()->cart->get_cart_contents_count() : 0;
$cart_total_raw   = $has_woo_cart ? (float) WC()->cart->get_total( 'edit' ) : 0;
// Для отображения в USD используем минимум 2 десятичных знака, чтобы не округлять до целого
$display_decimals = max(2, (int) wc_get_price_decimals());
$cart_total_usd   = wc_format_decimal( $cart_total_raw, $display_decimals );

if ( ! function_exists( 'moveat_cart_get_uah_rate' ) ) {
	function moveat_cart_get_uah_rate() {
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
			if ( $rate ) {
				return (float) $rate;
			}
		}
		if ( function_exists( 'wmc_get_price' ) ) {
			$rate = (float) wmc_get_price( 1, 'UAH' );
			if ( $rate ) {
				return $rate;
			}
		}
		$option_rate = get_option( 'moveat_uah_rate' );
		return $option_rate ? (float) $option_rate : 0.0;
	}
}

$uah_rate       = moveat_cart_get_uah_rate();
$cart_total_uah = $uah_rate > 0 ? (float) $cart_total_raw * $uah_rate : 0;

// Для отображения в USD используем минимум 2 десятичных знака, чтобы не округлять до целого
$display_decimals = max(2, (int) wc_get_price_decimals());

// Получаем список применённых купонов из корзины WooCommerce
$applied_coupons     = $has_woo_cart ? WC()->cart->get_applied_coupons() : [];
$has_applied_coupon  = ! empty( $applied_coupons );
$applied_coupon_code = $has_applied_coupon ? strtoupper( reset( $applied_coupons ) ) : '';

// Считаем сумму до скидки (subtotal) и размер скидки для блока discount
$cart_subtotal_raw  = $has_woo_cart ? (float) WC()->cart->get_subtotal() : 0;
$cart_discount_raw  = $has_woo_cart ? (float) WC()->cart->get_discount_total() : 0;

// Определяем тип и значение первого купона для отображения в чипе скидки
$discount_chip_text = '';
if ( $has_applied_coupon && $cart_discount_raw > 0 ) {
	$first_coupon_code = reset( $applied_coupons );
	$coupon_obj        = new WC_Coupon( $first_coupon_code );
	$discount_type     = $coupon_obj->get_discount_type(); // 'percent', 'fixed_cart', 'fixed_product'
	if ( $discount_type === 'percent' ) {
		$discount_chip_text = '-' . wc_format_decimal( $coupon_obj->get_amount(), 0 ) . '%';
	} else {
		$discount_chip_text = '-$' . wc_format_decimal( $cart_discount_raw, $display_decimals );
	}
}
?>
<script>
	/* Экспорт курса UAH в клиентский JS для корректных расчётов в refreshCartData */
 	window.MOVEAT_UAH_RATE = <?php echo wp_json_encode( (float) $uah_rate ); ?>;
</script>

<main class="cart-page">
	<div class="cart-page__container max-width-limiter">
		<section class="cart-page__content">
			<header class="cart-page__header">
				<h2 class="section-title">Ваш заказ</h2>
				<p class="text-secondary">Проверьте товары, измените количество и перейдите к оплате.</p>
			</header>

			<?php if ( $cart_is_empty ) : ?>
				<div class="cart-page__empty">
					<!-- <img class="cart-page__empty-icon" src="<?php echo esc_url( $cart_icon ); ?>" alt="Корзина пуста"> -->
					<h3 class="cart-page__empty-title">Корзина пуста</h3>
					<p class="cart-page__empty-text">Вы ещё не добавили ни одного товара. Перейдите в каталог, чтобы выбрать продукты.</p>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="primary-button cart-page__empty-action">Перейти в каталог</a>
				</div>
			<?php else : ?>
				<div class="cart-page__list">
					<?php foreach ( $cart_items as $cart_item_key => $cart_item ) : ?>
						<?php
						$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
						if ( ! $product instanceof WC_Product || ! $product->exists() ) {
							continue;
						}

						$product_id    = $product->get_id();
						$product_name  = $product->get_name();
						$product_url   = get_permalink( $product_id );
						$product_price = (float) wc_get_price_to_display( $product );
						$quantity      = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
						$line_total    = $product_price * $quantity;
						$line_uah      = $uah_rate > 0 ? $line_total * $uah_rate : 0;
						$description   = get_post_field( 'post_excerpt', $product_id );
						if ( ! $description ) {
							$description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $product_id ) ), 18 );
						}
						$image_html = $product->get_image( 'woocommerce_thumbnail', [ 'class' => 'cart-page__item-image' ] );
						?>
						<article class="cart-page__item" data-cart-item data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>">
							<a href="<?php echo esc_url( $product_url ); ?>" class="cart-page__item-image-link">
								<?php
								if ( $image_html ) {
									echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo '<img class="cart-page__item-image" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ) . '" alt="' . esc_attr( $product_name ) . '">';
								}
								?>
							</a>
							<div class="cart-page__item-content">
								<h3 class="cart-page__item-title"><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a></h3>
								<p class="cart-page__item-description"><?php echo esc_html( $description ); ?></p>
							</div>
							<div class="cart-page__item-qty" aria-label="Количество товара">
								<button class="cart-page__qty-button" type="button" aria-label="Уменьшить количество" data-cart-action="decrease" aria-disabled="true">
									<img src="<?php echo esc_url( $minus_icon ); ?>" alt="Уменьшить количество">
								</button>
								<span class="cart-page__qty-value"><?php echo esc_html( $quantity ); ?></span>
								<button class="cart-page__qty-button" type="button" aria-label="Увеличить количество" data-cart-action="increase" aria-disabled="true">
									<img src="<?php echo esc_url( $plus_icon ); ?>" alt="Увеличить количество">
								</button>
							</div>
							<div class="cart-page__item-price">
								<span class="cart-page__item-price-main">$<?php echo esc_html( wc_format_decimal( $line_total, $display_decimals ) ); ?></span>
								<?php if ( $line_uah > 0 ) : ?>
									<span class="cart-page__item-price-secondary"><?php echo esc_html( wc_format_decimal( $line_uah, 0 ) ); ?> грн</span>
								<?php endif; ?>
							</div>
							<button class="cart-page__remove" type="button" aria-label="Удалить товар" data-cart-action="remove" aria-disabled="true">
								<img src="<?php echo esc_url( $cross_icon ); ?>" alt="Удалить товар">
							</button>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<aside class="cart-page__summary">
			<div class="cart-page__summary-card">
				<div class="cart-page__summary-header">
					<span class="cart-page__summary-label">Итоговая сумма</span>
					<?php
						/*
							Блок скидки: показывается только если применён купон.
							data-subtotal — сумма без скидки (для обновления через JS).
							data-discount-chip — текст чипа скидки (например «-10%» или «-$5.00»).
						*/
					?>
					<div class="cart-page__summary-amount-discount<?php echo $has_applied_coupon ? '' : ' hidden'; ?>"
						data-discount-block
						data-subtotal="<?php echo esc_attr( wc_format_decimal( $cart_subtotal_raw, $display_decimals ) ); ?>"
						data-discount-chip="<?php echo esc_attr( $discount_chip_text ); ?>">
						<div class="cart-page__summary-amount-discount_price">
							$<?php echo esc_html( wc_format_decimal( $cart_subtotal_raw, $display_decimals ) ); ?>
						</div>
						<div class="cart-page__summary-amount-discount_discount">
							<?php echo esc_html( $discount_chip_text ); ?>
						</div>
					</div>
					<div class="cart-page__summary-amount-wrapper">
						<div class="cart-page__summary-amount">$<?php echo esc_html( $cart_total_usd ); ?></div>
						<div class="cart-page__summary-amount-secondary"><?php echo esc_html( wc_format_decimal( $cart_total_uah, 0 ) ); ?> грн</div>
						<div class="cart-page__summary-loader disabled">
							<div class="loader"></div>
						</div>
					</div>
					<div class="cart-page__summary-count"><?php echo esc_html( $cart_items_count ); ?> товара в корзине</div>
				</div>
				<div class="cart-page__promo">
					<?php
						/*
							Состояние 1 (нет купона): cart-page__promo-control видим, cart-page__promo-message-wrapper скрыт.
							Состояние 2 (купон применён): cart-page__promo-control скрыт, cart-page__promo-message-wrapper видим.
						*/
					?>
					<div class="cart-page__promo-control<?php echo $has_applied_coupon ? ' hidden' : ''; ?>">
						<input
							class="cart-page__promo-input form-input"
							type="text"
							name="promocode"
							placeholder="Промокод"
							aria-label="Введите промокод" />
						<button
							class="cart-page__promo-button secondary-button unactive"
							type="button">
							Применить
						</button>
					</div>
					<div class="cart-page__promo-message-wrapper<?php echo $has_applied_coupon ? '' : ' hidden'; ?>">
						<div class="cart-page__promo-message cart-page__promo-message--error hidden">
							Промокод истёк или уже был использован
						</div>
						<div class="cart-page__promo-message cart-page__promo-message--success<?php echo $has_applied_coupon ? '' : ' hidden'; ?>">
							<div>Применён промокод:</div>
							<div><?php echo esc_html( $applied_coupon_code ); ?></div>
						</div>
						<button
							class="cart-page__promo-button_delete secondary-button red<?php echo $has_applied_coupon ? '' : ' hidden'; ?>"
							type="button">
							Удалить промокод
						</button>
					</div>
				</div>
				<div class="cart-page__summary-divider"></div>
				<div class="cart-page__summary-actions">
					<a href="<?php echo esc_url( $checkout_url ); ?>" class="cart-page__summary-actions_order primary-button<?php echo $cart_is_empty ? ' unactive' : ''; ?>"<?php echo $cart_is_empty ? ' aria-disabled="true"' : ''; ?>>Оформить заказ</a>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="secondary-button">Продолжить покупки</a>
				</div>
				<div class="cart-page__payment-note">
					<p class="cart-page__payment-note-text">По вопросам оплаты вы можете написать нам в мессенджеры</p>
					<div class="cart-page__payment-messengers">
						<!-- Ссылки на мессенджеры -->
						<?php get_template_part( 'template-parts/socials' ); ?>
					</div>
				</div>
			</div>
		</aside>
	</div>
</main>

<?php get_footer(); ?>