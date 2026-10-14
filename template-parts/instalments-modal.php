<?php
/**
 * Модалка оплаты частями monobank на странице товара.
 *
 * Открытие/закрытие — универсальные модалки из main.js (.js-modal-open → .js-modal).
 * Состояние и суммы пересчитывает instalments-modal.js по актуальной корзине:
 * страница товара на проде кэшируется, а корзина меняется уже после загрузки.
 * PHP рендерит стартовое состояние «в корзине только этот товар».
 *
 * Состояния (модификатор на контейнере .product-modal):
 * - без модификатора           — сумма в допустимом диапазоне, можно оформить;
 * - product-modal--below-limit — сумма меньше минимума: прогресс + кнопка в каталог;
 * - product-modal--above-limit — сумма больше максимума: предупреждение, без кнопок.
 *
 * @var array $args product_id, offer (moveat_instalments_product_offer())
 */

defined( 'ABSPATH' ) || exit;

$product_id = (int) $args['product_id'];
$offer      = $args['offer'];
$theme_uri  = get_template_directory_uri();

$min_uah   = (float) MOVEAT_INSTALMENTS_MIN_UAH;
$max_uah   = (float) MOVEAT_INSTALMENTS_MAX_UAH;
$total_uah = round( $offer['price_usd'] * $offer['rate'], 2 );

if ( $min_uah > 0 && $total_uah < $min_uah ) {
	$state_class = ' product-modal--below-limit';
} elseif ( $max_uah > 0 && $total_uah > $max_uah ) {
	$state_class = ' product-modal--above-limit';
} else {
	$state_class = '';
}

$progress = $min_uah > 0 ? min( 100, $total_uah / $min_uah * 100 ) : 100;

$format_uah = static function ( $amount ) {
	return number_format( (float) $amount, 0, '.', ' ' ) . ' ₴';
};

$checkout_url = add_query_arg( 'payment', 'parts', home_url( '/oformlenie-zakaza/' ) );
?>
<div
	class="product-modal<?php echo esc_attr( $state_class ); ?> js-modal"
	data-modal-id="mono-installments"
	data-instalments-modal
	data-product-id="<?php echo esc_attr( $product_id ); ?>"
	data-price-usd="<?php echo esc_attr( $offer['price_usd'] ); ?>"
	data-uah-rate="<?php echo esc_attr( $offer['rate'] ); ?>"
	data-min-uah="<?php echo esc_attr( $min_uah ); ?>"
	data-max-uah="<?php echo esc_attr( $max_uah ); ?>"
	data-checkout-url="<?php echo esc_url( $checkout_url ); ?>"
	aria-hidden="true">
	<div
		class="product-modal__window js-modal-window"
		role="dialog"
		aria-modal="true"
		aria-labelledby="mono-installments-title">
		<div class="product-modal__header">
			<h3 class="product-modal__title" id="mono-installments-title">Оплата частями</h3>
			<button class="product-modal__close js-modal-close" type="button" aria-label="Закрыть">
				<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/cross.png' ); ?>" alt="Закрыть" />
			</button>
		</div>
		<div class="product-modal__body">
			<div class="product-modal__bank">
				<img class="product-modal__bank-logo" src="<?php echo esc_url( $theme_uri . '/assets/images/logotypes/mono-paw.png' ); ?>" alt="Monobank" />
				<div class="product-modal__bank-name">Покупка частями monobank</div>
			</div>

			<!-- Сумма меньше минимума -->
			<div class="product-modal__limit">
				<div class="product-modal__limit-title">Оплата частями доступна от <?php echo esc_html( $format_uah( $min_uah ) ); ?></div>
				<div class="product-modal__progress">
					<div class="product-modal__progress-bar">
						<div class="product-modal__progress-fill" data-instalments-progress style="width: <?php echo esc_attr( round( $progress, 2 ) ); ?>%"></div>
					</div>
					<div class="product-modal__progress-values">
						<span data-instalments-total><?php echo esc_html( $format_uah( $total_uah ) ); ?></span>
						<span><?php echo esc_html( $format_uah( $min_uah ) ); ?></span>
					</div>
				</div>
				<div class="product-modal__limit-text">
					Добавьте товаров ещё на <strong data-instalments-missing><?php echo esc_html( $format_uah( max( 0, $min_uah - $total_uah ) ) ); ?></strong>, чтобы оформить оплату частями
				</div>
			</div>

			<!-- Сумма больше максимума -->
			<div class="product-modal__notice">
				<i class="bi bi-info-circle"></i>
				<div>
					Оплата частями доступна для заказов до <strong><?php echo esc_html( $format_uah( $max_uah ) ); ?></strong>. Уменьшите сумму заказа или выберите
					другой способ оплаты.
				</div>
			</div>

			<div class="product-modal__info">
				<div class="product-modal__info-title">Условия оформления</div>
				<ol class="product-modal__info-list">
					<li>Наличие кредитной карты от monobank</li>
					<li>Доступный кредитный лимит по сервису «Оплата частями»</li>
					<li>Первый платёж будет списан в день оформления</li>
				</ol>
			</div>
			<div class="product-modal__actions">
				<a href="<?php echo esc_url( $checkout_url ); ?>" class="primary-button product-modal__button product-modal__button--submit" data-instalments-submit>Оформить</a>
				<a href="<?php echo esc_url( home_url( '/catalog/' ) ); ?>" class="secondary-button product-modal__button product-modal__button--catalog">
					Перейти в каталог
				</a>
			</div>
		</div>
	</div>
</div>
