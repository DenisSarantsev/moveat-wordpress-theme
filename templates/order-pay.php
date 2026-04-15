<?php
/*
	Template Name: Оплата заказа
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri       = get_template_directory_uri();
$telegram_icon   = $theme_uri . '/assets/images/icons/telegram.png';
$whatsapp_icon   = $theme_uri . '/assets/images/icons/whatsapp.png';
$viber_icon      = $theme_uri . '/assets/images/icons/viber.png';
$paypal_logo     = $theme_uri . '/assets/images/logotypes/paypal.png';
$visa_logo       = $theme_uri . '/assets/images/logotypes/visa.png';
$mastercard_logo = $theme_uri . '/assets/images/logotypes/mastercard.png';

// ─── Данные заказа ────────────────────────────────────────────────────────────

$order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( $_GET['order_key'] ) : '';
$order     = $order_id ? wc_get_order( $order_id ) : null;
$valid     = $order && $order->key_is_valid( $order_key );

// ─── Курс UAH ─────────────────────────────────────────────────────────────────

function moveat_order_pay_get_uah_rate() {
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
	return (float) get_option( 'moveat_uah_rate', 0 );
}

$uah_rate = $valid ? moveat_order_pay_get_uah_rate() : 0;

// Для отображения в USD используем минимум 2 десятичных знака
$display_decimals = max(2, (int) wc_get_price_decimals());
?>

<main class="payment-page">
	<div class="payment-page__container max-width-limiter">

		<!-- Two-column grid -->
		<div class="payment-page__grid">

			<!-- Left column: Payment form -->
			<div class="payment-page__col-form">
				<div class="payment-page__card">

					<!-- Header -->
					<header class="payment-page__header">
						<h2 class="section-title">Оплата заказа</h2>
						<p class="payment-page__subtitle text-secondary">Проверьте состав заказа и выберите удобный способ оплаты.</p>
					</header>

					<div class="payment-page__divider"></div>

					<!-- Payment methods -->
					<div class="payment-page__methods">
						<p class="payment-page__methods-title">Способ оплаты</p>
						<div class="payment-page__methods-list" id="paymentMethodsList">

							<!-- PayPal -->
							<button
								type="button"
								class="payment-page__method-button"
								data-method="paypal"
								aria-pressed="false"
							>
								<div class="payment-page__method-left">
									<div class="payment-page__method-radio" aria-hidden="true"></div>
									<div class="payment-page__method-text">
										<span class="payment-page__method-name">PayPal</span>
										<span class="payment-page__method-description">Оплата через PayPal аккаунт</span>
									</div>
								</div>
								<div class="payment-page__method-icons">
									<img class="payment-page__method-icon" src="<?php echo esc_url( $paypal_logo ); ?>" alt="PayPal">
								</div>
							</button>

							<!-- Bank card -->
							<button
								type="button"
								class="payment-page__method-button"
								data-method="card"
								aria-pressed="false"
							>
								<div class="payment-page__method-left">
									<div class="payment-page__method-radio" aria-hidden="true"></div>
									<div class="payment-page__method-text">
										<span class="payment-page__method-name">Банковская карта</span>
										<span class="payment-page__method-description">Visa, Mastercard</span>
									</div>
								</div>
								<div class="payment-page__method-icons">
									<img class="payment-page__method-icon" src="<?php echo esc_url( $visa_logo ); ?>" alt="Visa">
									<img class="payment-page__method-icon" src="<?php echo esc_url( $mastercard_logo ); ?>" alt="Mastercard">
								</div>
							</button>

						</div>
					</div>

					<div class="payment-page__divider"></div>

					<!-- Russian bank info -->
					<div class="payment-page__info-card" role="note">
						<p class="payment-page__info-card-text">
							Если у вас <span>карта российского банка,</span> свяжитесь с нами по одному из мессенджеров и мы поможем вам с оплатой.
						</p>
						<div class="payment-page__info-messengers">
							<!-- Ссылки на мессенджеры -->
							<?php get_template_part( 'template-parts/socials' ); ?>
						</div>
					</div>

					<div class="payment-page__divider"></div>

					<!-- Privacy note -->
					<p class="payment-page__privacy-note">
						Ваши личные данные будут использоваться для обработки ваших заказов, упрощения вашей работы с сайтом и для других целей, описанных в нашей 
						<a href="https://moveat.expert/politika-konfidentsialnosti/">политике конфиденциальности</a>.
					</p>

					<!-- Terms checkbox -->
					<div class="payment-page__checkbox-wrapper">
						<input
							class="payment-page__checkbox"
							type="checkbox"
							id="agreeTerms"
							name="agreeTerms"
							required
							aria-required="true"
						>
						<label class="payment-page__checkbox-label" for="agreeTerms">
							Я прочитал(а) и соглашаюсь с <a href="https://moveat.expert/public-contract/">публичным договором (офертой)</a>
						</label>
					</div>

					<!-- Submit -->
					<button
						type="button"
						id="paymentSubmit"
						class="primary-button payment-page__submit unactive"
						disabled
					>
						Оплатить заказ
					</button>

					<!-- Back link -->
					<!-- <a class="payment-page__back" href="javascript:history.back()">← Вернуться назад</a> -->

				</div>
			</div>

			<!-- Right column: Order summary -->
			<aside class="payment-page__col-summary">
				<div class="payment-page__summary-card">
					<p class="payment-page__summary-title">Ваш заказ</p>
					<div class="payment-page__summary-list">
						<?php if ( $valid ) : ?>
							<?php foreach ( $order->get_items() as $item ) : ?>
								<?php
								$name      = $item->get_name();
								$qty       = $item->get_quantity();
								$price_usd = (float) $item->get_subtotal();
								$price_uah = $uah_rate > 0 ? $price_usd * $uah_rate : 0;
								?>
								<div class="payment-page__summary-item">
									<div class="payment-page__summary-item-info">
										<span class="payment-page__summary-item-name"><?php echo esc_html( $name ); ?></span>
										<span class="payment-page__summary-item-qty"><?php echo esc_html( $qty ); ?> шт.</span>
									</div>
									<div class="payment-page__summary-item-price">
										<span class="payment-page__summary-item-price-usd">$<?php echo esc_html( wc_format_decimal( $price_usd, $display_decimals ) ); ?></span>
										<span class="payment-page__summary-item-price-uah"><?php echo number_format( $price_uah, 0, '.', ' ' ); ?> грн</span>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<div class="payment-page__divider"></div>
					<div class="payment-page__summary-total">
						<span class="payment-page__summary-total-label">Итого к оплате:</span>

						<?php if ( $valid ) : ?>
							<?php
							$total_usd    = (float) $order->get_total();
							$total_uah    = $uah_rate > 0 ? $total_usd * $uah_rate : 0;
							$subtotal_usd = (float) $order->get_subtotal();
							$has_discount = $subtotal_usd > $total_usd;

							// Вычислим денежную сумму скидки и запасной итог на основе subtotal + купона
							$calculated_discount_usd = 0.0;
							$calculated_total_usd    = $total_usd;
							if ( $has_discount ) {
								$coupons = $order->get_coupon_codes();
								if ( ! empty( $coupons ) ) {
									$coupon_obj = new WC_Coupon( $coupons[0] );
									$coupon_type = $coupon_obj->get_discount_type();
									$coupon_amount = (float) $coupon_obj->get_amount();

									if ( $coupon_type === 'percent' ) {
										// процентная скидка: считаем от subtotal
										$calculated_discount_usd = $subtotal_usd * ( $coupon_amount / 100 );
									} elseif ( $coupon_type === 'fixed_cart' ) {
										// фиксированная скидка на корзину
										$calculated_discount_usd = min( $coupon_amount, $subtotal_usd );
									} else {
										// fallback: попробуем взять разницу между subtotal и order total
										$calculated_discount_usd = $subtotal_usd - $total_usd;
									}
								} else {
									$calculated_discount_usd = $subtotal_usd - $total_usd;
								}

								$calculated_total_usd = max( 0, $subtotal_usd - $calculated_discount_usd );
							}

							// Текст чипа скидки
							$discount_chip = '';
							if ( $has_discount ) {
								$coupons = $order->get_coupon_codes();
								if ( ! empty( $coupons ) ) {
									$coupon_obj = new WC_Coupon( $coupons[0] );
									if ( $coupon_obj->get_discount_type() === 'percent' ) {
										$coupon_amount     = (float) $coupon_obj->get_amount();
										// Показываем 1 знак после запятой, если есть дробная часть (например 99.5)
										$percent_decimals = ( floor( $coupon_amount ) != $coupon_amount ) ? 1 : 0;
										$discount_chip     = '-' . wc_format_decimal( $coupon_amount, $percent_decimals ) . '%';
									} else {
										$discount_chip = '-$' . wc_format_decimal( $subtotal_usd - $total_usd, $display_decimals );
									}
								} else {
									$discount_chip = '-$' . wc_format_decimal( $subtotal_usd - $total_usd, $display_decimals );
								}
							}
							?>
							<div class="payment-page__summary-total-amount">
								<?php if ( $has_discount ) : ?>
									<span class="payment-page__summary-total-old">
										<span class="discount-chip"><?php echo esc_html( $discount_chip ); ?></span>
										<s>$<?php echo esc_html( wc_format_decimal( $subtotal_usd, $display_decimals ) ); ?></s>
									</span>
								<?php endif; ?>
								<?php
								// Если order->get_total() равен 0 или сильно отличается из-за округлений, используем рассчитанный запасной total
								$display_total = $total_usd;
								if ( isset( $calculated_total_usd ) && abs( $calculated_total_usd - $total_usd ) > 0.0001 ) {
									$display_total = $calculated_total_usd;
								}
								?>
								<span class="payment-page__summary-total-usd">$<?php echo esc_html( wc_format_decimal( $display_total, $display_decimals ) ); ?></span>
								<span class="payment-page__summary-total-uah"><?php echo number_format( $total_uah, 0, '.', ' ' ); ?> грн</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</aside>

		</div>
		<!-- /grid -->

	</div>
</main>

<?php get_footer(); ?>

<?php if ( $valid ) : ?>
<!-- Cookie теперь устанавливается по клику на кнопку «Оплатить» в JS-модуле payment-process.js -->
<?php endif; ?>
