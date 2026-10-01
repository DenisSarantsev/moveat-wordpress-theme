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

// moveat_order_pay_get_uah_rate() — в assets/scripts/php/woocommerce/instalments.php

$uah_rate = $valid ? moveat_order_pay_get_uah_rate() : 0;

// Итог в гривнах: доллары на странице только для ориентира, списание всегда в UAH.
$total_uah = $valid && $uah_rate > 0 ? (float) $order->get_total() * $uah_rate : 0;

// ─── Оплата частями (monobank, плагин CatCode) ───────────────────────────────

// Варианты количества платежей — moveat_order_pay_get_installment_terms() в instalments.php.

// «3 платежа», «5 платежей», «21 платёж».
function moveat_order_pay_payments_label( $count ) {
	$mod10  = $count % 10;
	$mod100 = $count % 100;

	if ( 1 === $mod10 && 11 !== $mod100 ) {
		$word = 'платёж';
	} elseif ( $mod10 >= 2 && $mod10 <= 4 && ( $mod100 < 12 || $mod100 > 14 ) ) {
		$word = 'платежа';
	} else {
		$word = 'платежей';
	}

	return $count . ' ' . $word;
}

$installment_terms = $total_uah > 0
	&& function_exists( 'moveat_order_pay_get_installment_terms' )
	&& moveat_instalments_allowed_for_current_user()
	? moveat_order_pay_get_installment_terms()
	: array();
$mono_logo         = $theme_uri . '/assets/images/logotypes/mono.webp';

// TODO: временная диагностика оплаты частями — удалить после проверки.
?>
<!-- parts-debug:
	user_id=<?php echo (int) get_current_user_id(); ?>
	allowed=<?php echo function_exists( 'moveat_instalments_allowed_for_current_user' ) && moveat_instalments_allowed_for_current_user() ? 'yes' : 'no'; ?>
	total_uah=<?php echo esc_html( $total_uah ); ?>
	terms=<?php echo esc_html( function_exists( 'moveat_order_pay_get_installment_terms' ) ? implode( ',', moveat_order_pay_get_installment_terms() ) : 'no-function' ); ?>
	time=<?php echo esc_html( gmdate( 'H:i:s' ) ); ?>
-->
<?php

// Для отображения в USD используем минимум 2 десятичных знака
$display_decimals = max(2, (int) wc_get_price_decimals());
?>

<main class="payment-page">
	<?php if ( $installment_terms ) : ?>
	<!-- Состояния оплаты частями: ожидание / ошибка / успех.
	     Видна одна модалка — та, что задана в data-checkout-state.
	     Весь блок скрыт, пока оплату не запустили. Управляет payment-process.js. -->
	<div
		class="payment-page__checkout payment-page-checkout"
		data-checkout
		data-checkout-state="waiting"
		hidden
	>
		<div class="payment-page-checkout__overlay" data-checkout-overlay></div>
		<div class="payment-page-checkout__wrapper">

			<!-- Ожидание подтверждения в приложении банка: закрыть нельзя -->
			<div
				class="payment-page-checkout__waiting-modal payment-page-checkout__modal"
				role="status"
				aria-live="polite"
				data-checkout-modal="waiting"
			>
				<div class="payment-page-checkout__spinner" aria-hidden="true"></div>
				<h3 class="payment-page-checkout__title">Ожидаем подтверждение оплаты</h3>
				<p class="payment-page-checkout__text">
					Запрос отправлен в банк. Воспользуйтесь приложением Monobank для завершения оплаты
				</p>
				<div class="payment-page-checkout__meta">
					<div class="payment-page-checkout__meta-row">
						<span class="payment-page-checkout__meta-label">Сумма</span>
						<span class="payment-page-checkout__meta-value" data-checkout-amount><?php echo esc_html( number_format( $total_uah, 0, '.', ' ' ) ); ?> грн</span>
					</div>
					<div class="payment-page-checkout__meta-row">
						<span class="payment-page-checkout__meta-label">Способ оплаты</span>
						<span class="payment-page-checkout__meta-value" data-checkout-method>Оплата частями · Monobank</span>
					</div>
				</div>
				<p class="payment-page-checkout__note">
					Не закрывайте страницу — после подтверждения в приложении мы перенаправим вас автоматически. На подтверждение есть 15 минут.
				</p>
			</div>

			<!-- Платёж не прошёл: даём повторить или сменить способ -->
			<div
				class="payment-page-checkout__error-modal payment-page-checkout__modal"
				role="alertdialog"
				aria-labelledby="checkoutErrorTitle"
				aria-describedby="checkoutErrorText"
				data-checkout-modal="error"
			>
				<button type="button" class="payment-page-checkout__close" aria-label="Закрыть" data-checkout-close>&times;</button>
				<div class="payment-page-checkout__icon payment-page-checkout__icon--error" aria-hidden="true">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/error.png' ); ?>" alt="">
				</div>
				<h3 class="payment-page-checkout__title" id="checkoutErrorTitle">Платёж не прошёл</h3>
				<p class="payment-page-checkout__text" id="checkoutErrorText">
					Банк отклонил операцию или истекло время ожидания. Деньги не списаны — попробуйте оплатить ещё раз или выберите другой способ.
				</p>
				<p class="payment-page-checkout__error-code" data-checkout-error-code-row>
					Код ошибки:
					<span data-checkout-error-code>—</span>
				</p>
				<div class="payment-page-checkout__actions">
					<button type="button" class="primary-button payment-page-checkout__action" data-checkout-retry>
						Попробовать снова
					</button>
					<!-- Дублирует крестик: закрывает окно и возвращает к выбору способа оплаты -->
					<button type="button" class="payment-page-checkout__ghost-button" data-checkout-close>
						Закрыть
					</button>
				</div>
				<p class="payment-page-checkout__note">
					После закрытия окна вы вернётесь к оформлению — можно повторить оплату или выбрать другой способ.
				</p>
				<div class="payment-page-checkout__support">
					<span class="payment-page-checkout__support-text">Не получается оплатить? Напишите нам:</span>
					<div class="payment-page__info-messengers">
						<?php get_template_part( 'template-parts/socials' ); ?>
					</div>
				</div>
			</div>

			<!-- Оплата прошла: подтверждение, затем JS переводит на «Спасибо» -->
			<div
				class="payment-page-checkout__success-modal payment-page-checkout__modal"
				role="alertdialog"
				aria-labelledby="checkoutSuccessTitle"
				aria-describedby="checkoutSuccessText"
				data-checkout-modal="success"
			>
				<div class="payment-page-checkout__icon payment-page-checkout__icon--success" aria-hidden="true">
					<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/check.png' ); ?>" alt="">
				</div>
				<h3 class="payment-page-checkout__title" id="checkoutSuccessTitle">Оплата прошла успешно</h3>
				<p class="payment-page-checkout__text" id="checkoutSuccessText">
					Спасибо! Мы получили платёж и отправили письмо с доступом к материалам на вашу почту.
				</p>
			</div>

		</div>
	</div>
	<?php endif; ?>

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

							<?php if ( $installment_terms ) : ?>
							<!-- Оплата частями monobank.
							     Выбор срока живёт внутри этой же кнопки: чипсы — span, а не button
							     (кнопка в кнопке — невалидная разметка), клик по ним ловит JS. -->
							<button
								type="button"
								class="payment-page__method-button payment-page__method-button--installments pay-parts"
								data-method="parts"
								data-installments
								aria-pressed="false"
								aria-expanded="false"
							>
								<span class="payment-page__method-row pb">
									<span class="payment-page__method-left">
										<span class="payment-page__method-radio" aria-hidden="true"></span>
										<span class="payment-page__method-text">
											<span class="payment-page__method-name">Оплата частями</span>
											<span class="payment-page__method-description">Monobank</span>
										</span>
									</span>
									<span class="payment-page__method-icons">
										<img class="payment-page__method-icon" src="<?php echo esc_url( $mono_logo ); ?>" alt="Monobank">
									</span>
								</span>

								<span class="payment-page__method-terms" role="radiogroup" aria-label="Количество платежей" hidden>
									<?php foreach ( $installment_terms as $index => $months ) : ?>
										<?php $is_default = 0 === $index; ?>
										<span
											class="payment-page__term<?php echo $is_default ? ' is-active' : ''; ?>"
											role="radio"
											aria-checked="<?php echo $is_default ? 'true' : 'false'; ?>"
											data-installment-term="<?php echo esc_attr( $months ); ?>"
										>
											<span class="payment-page__term-count"><?php echo esc_html( moveat_order_pay_payments_label( $months ) ); ?></span>
											<span class="payment-page__term-payment" data-installment-payment><?php echo esc_html( number_format( $total_uah / $months, 0, '.', ' ' ) ); ?> грн/мес</span>
										</span>
									<?php endforeach; ?>
								</span>

								<span class="payment-page__method-terms-note" hidden>
									Оплата равными частями без переплаты — комиссию берём на себя
								</span>
							</button>
							<?php endif; ?>

						</div>
					</div>

					<div class="payment-page__divider"></div>

					<!-- Russian bank info -->
					<div class="payment-page__info-card" role="note">
						<p class="payment-page__info-card-text">
							Если у вас <span>НЕ ПРОХОДИТ ОПЛАТА,</span> свяжитесь с нами по одному из мессенджеров и мы поможем вам с оплатой.
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
