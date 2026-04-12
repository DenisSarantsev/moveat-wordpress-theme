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
							<a href="#" class="payment-page__messenger-link payment-page__messenger-link--telegram" aria-label="Telegram">
								<img src="<?php echo esc_url( $telegram_icon ); ?>" alt="Telegram">
							</a>
							<a href="#" class="payment-page__messenger-link payment-page__messenger-link--whatsapp" aria-label="WhatsApp">
								<img src="<?php echo esc_url( $whatsapp_icon ); ?>" alt="WhatsApp">
							</a>
							<a href="#" class="payment-page__messenger-link payment-page__messenger-link--viber" aria-label="Viber">
								<img src="<?php echo esc_url( $viber_icon ); ?>" alt="Viber">
							</a>
						</div>
					</div>

					<div class="payment-page__divider"></div>

					<!-- Privacy note -->
					<p class="payment-page__privacy-note">
						Ваши личные данные будут использоваться для обработки ваших заказов, упрощения вашей работы с сайтом и для других целей, описанных в нашей <a href="#">политике конфиденциальности</a>.
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
							Я прочитал(а) и соглашаюсь с <a href="#">правилами сайта</a> <span>*</span>
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
					<a class="payment-page__back" href="javascript:history.back()">← Вернуться назад</a>

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
										<span class="payment-page__summary-item-price-usd">$<?php echo number_format( $price_usd, 0, '.', ' ' ); ?></span>
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

							// Текст чипа скидки
							$discount_chip = '';
							if ( $has_discount ) {
								$coupons = $order->get_coupon_codes();
								if ( ! empty( $coupons ) ) {
									$coupon_obj = new WC_Coupon( $coupons[0] );
									if ( $coupon_obj->get_discount_type() === 'percent' ) {
										$discount_chip = '-' . wc_format_decimal( $coupon_obj->get_amount(), 0 ) . '%';
									} else {
										$discount_chip = '-$' . wc_format_decimal( $subtotal_usd - $total_usd, wc_get_price_decimals() );
									}
								} else {
									$discount_chip = '-$' . wc_format_decimal( $subtotal_usd - $total_usd, wc_get_price_decimals() );
								}
							}
							?>
							<div class="payment-page__summary-total-amount">
								<?php if ( $has_discount ) : ?>
									<span class="payment-page__summary-total-old">
										<span class="discount-chip"><?php echo esc_html( $discount_chip ); ?></span>
										<s>$<?php echo number_format( $subtotal_usd, 0, '.', ' ' ); ?></s>
									</span>
								<?php endif; ?>
								<span class="payment-page__summary-total-usd">$<?php echo number_format( $total_usd, 0, '.', ' ' ); ?></span>
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
