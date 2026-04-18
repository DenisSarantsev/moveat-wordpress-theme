<?php
/*
	Template Name: Успешная оплата
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

// Надёжное серверное удаление флаговой куки перед отправкой заголовков
$cookie_name = 'moveat_pending_order';
if ( ! headers_sent() ) {
	@setcookie( $cookie_name, '', time() - 3600, '/' );
	$domain = parse_url( home_url(), PHP_URL_HOST );
	if ( $domain ) {
		@setcookie( $cookie_name, '', time() - 3600, '/', $domain );
	}
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		unset( $_COOKIE[ $cookie_name ] );
	}
} else {
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		unset( $_COOKIE[ $cookie_name ] );
	}
}

get_header();

$theme_uri     = get_template_directory_uri();
$telegram_icon = $theme_uri . '/assets/images/icons/telegram.png';
$whatsapp_icon = $theme_uri . '/assets/images/icons/whatsapp.png';
$viber_icon    = $theme_uri . '/assets/images/icons/viber.png';
?>

<main class="thankyou-page">
	<div class="thankyou-page__container">
		<div class="thankyou-page__card">

			<!-- Icon -->
			<div class="thankyou-page__icon-wrapper">
				<svg
					class="thankyou-page__icon"
					xmlns="http://www.w3.org/2000/svg"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="2.5"
					stroke-linecap="round"
					stroke-linejoin="round"
					aria-hidden="true">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
			</div>

			<!-- Heading -->
			<div class="thankyou-page__heading">
				<h1 class="thankyou-page__title">Спасибо за покупку!</h1>
				<p class="thankyou-page__subtitle">
					Ваш заказ успешно оформлен и принят в обработку
				</p>
			</div>

			<!-- Divider -->
			<div class="thankyou-page__divider"></div>

			<!-- Email notice -->
			<!-- <div class="thankyou-page__email-notice">
				<div class="thankyou-page__email-notice-icon">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
						aria-hidden="true">
						<rect x="2" y="4" width="20" height="16" rx="2"></rect>
						<polyline points="2,7 12,13 22,7"></polyline>
					</svg>
				</div>
				<div class="thankyou-page__email-notice-content">
					<p class="thankyou-page__email-notice-text">
						Подтверждение заказа отправлено на почту
					</p>
					<span class="thankyou-page__email-notice-address" id="thankyouEmail">
						example@email.com
					</span>
				</div>
			</div> -->

			<!-- Divider -->
			<!-- <div class="thankyou-page__divider"></div> -->

			<!-- Messengers -->
			<div class="thankyou-page__messengers">
				<p class="thankyou-page__messengers-title">
					Остались вопросы? Пишите — мы с радостью на них ответим!
				</p>
				<div class="thankyou-page__messengers-list">
					<!-- Ссылки на мессенджеры -->
					<?php get_template_part( 'template-parts/socials' ); ?>
				</div>
			</div>

			<!-- Actions -->
			<div class="thankyou-page__actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="secondary-button">
					На главную
				</a>
				<a href="<?php echo esc_url( home_url( '/catalog/' ) ); ?>" class="primary-button">
					Продолжить покупки
				</a>
			</div>

		</div>
	</div>
</main>

<!-- Client-side fallback: удаляем куку (host-only и с domain) -->
<script>
(function clearMoveatPending() {
	try {
		var name = 'moveat_pending_order';
		// Удаление host-only
		document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT;';
		// Попробуем удалить с доменом
		try {
			var domain = window.location.hostname;
			document.cookie = name + '=; Path=/; Domain=' + domain + '; Expires=Thu, 01 Jan 1970 00:00:00 GMT;';
		} catch (e) { /* ignore */ }
		// Очистим локальный объект (если читается в JS)
		try { window.moveatPendingOrder && delete window.moveatPendingOrder; } catch(e) {}
	} catch (e) {
		// noop
	}
})();
</script>

<?php get_footer(); ?>
