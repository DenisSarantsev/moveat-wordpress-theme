<?php
/*
	Template Name: Успешная оплата
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

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
			<div class="thankyou-page__email-notice">
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
			</div>

			<!-- Divider -->
			<div class="thankyou-page__divider"></div>

			<!-- Messengers -->
			<div class="thankyou-page__messengers">
				<p class="thankyou-page__messengers-title">
					Остались вопросы? Пишите — мы с радостью на них ответим!
				</p>
				<div class="thankyou-page__messengers-list">
					<a
						href="#"
						class="thankyou-page__messenger-link thankyou-page__messenger-link--telegram"
						aria-label="Telegram">
						<img src="<?php echo esc_url( $telegram_icon ); ?>" alt="Telegram" />
					</a>
					<a
						href="#"
						class="thankyou-page__messenger-link thankyou-page__messenger-link--whatsapp"
						aria-label="WhatsApp">
						<img src="<?php echo esc_url( $whatsapp_icon ); ?>" alt="WhatsApp" />
					</a>
					<a
						href="#"
						class="thankyou-page__messenger-link thankyou-page__messenger-link--viber"
						aria-label="Viber">
						<img src="<?php echo esc_url( $viber_icon ); ?>" alt="Viber" />
					</a>
				</div>
			</div>

			<!-- Actions -->
			<div class="thankyou-page__actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="secondary-button">
					На главную
				</a>
				<a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="primary-button">
					Продолжить покупки
				</a>
			</div>

		</div>
	</div>
</main>

<?php get_footer(); ?>
