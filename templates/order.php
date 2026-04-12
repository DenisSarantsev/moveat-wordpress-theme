<?php
/*
	Template Name: Оформление заказа
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri     = get_template_directory_uri();
$telegram_icon = $theme_uri . '/assets/images/icons/telegram.png';
$whatsapp_icon = $theme_uri . '/assets/images/icons/whatsapp.png';
$viber_icon    = $theme_uri . '/assets/images/icons/viber.png';
?>

<main class="order-page">
	<div class="order-page__container max-width-limiter">

		<!-- Form card -->
		<div class="order-page__card">
			<header class="order-page__header">
				<h2 class="section-title">Оформление заказа</h2>
				<p class="order-page__subtitle text-secondary">Заполните данные для получения ссылки на оплату. Поля, отмеченные <span style="color: var(--secondary-color);">*</span>, обязательны для заполнения.</p>
			</header>

			<form class="order-page__form" id="orderForm" novalidate>

				<!-- Имя + Фамилия -->
				<div class="order-page__form-row">
					<div class="order-page__field">
						<label class="order-page__label" for="firstName">
							Имя<span class="order-page__required-mark" aria-hidden="true">*</span>
						</label>
						<input
							class="order-page__input form-input"
							type="text"
							id="firstName"
							name="firstName"
							placeholder="Иван"
							autocomplete="given-name"
							required
							aria-required="true"
						>
					</div>

					<div class="order-page__field">
						<label class="order-page__label" for="lastName">
							Фамилия
						</label>
						<input
							class="order-page__input form-input"
							type="text"
							id="lastName"
							name="lastName"
							placeholder="Иванов"
							autocomplete="family-name"
						>
					</div>
				</div>

				<!-- Email -->
				<div class="order-page__field">
					<label class="order-page__label" for="email">
						Email<span class="order-page__required-mark" aria-hidden="true">*</span>
					</label>
					<input
						class="order-page__input form-input"
						type="email"
						id="email"
						name="email"
						placeholder="ivan@example.com"
						autocomplete="email"
						required
						aria-required="true"
					>
				</div>

				<!-- Телефон -->
				<div class="order-page__field">
					<label class="order-page__label" for="phone">
						Телефон<span
							class="order-page__required-mark"
							aria-hidden="true"
							>*</span
						>
					</label>
					<input
						class="order-page__input form-input"
						type="tel"
						id="phone"
						name="phone"
						autocomplete="tel"
						required
						aria-required="true" />
				</div>

				<div class="order-page__divider"></div>

				<!-- Кнопка отправки -->
				<button
					type="submit"
					id="orderSubmit"
					class="primary-button order-page__submit unactive"
					disabled
				>
					Перейти к оплате
				</button>

				<!-- Мессенджеры -->
				<div class="order-page__messengers-card">
					<p class="order-page__messengers-text">По вопросам оплаты вы можете написать нам в мессенджеры</p>
					<div class="order-page__messengers-list">
						<a href="#" class="order-page__messenger-link order-page__messenger-link--telegram" aria-label="Telegram">
							<img src="<?php echo esc_url( $telegram_icon ); ?>" alt="Telegram">
						</a>
						<a href="#" class="order-page__messenger-link order-page__messenger-link--whatsapp" aria-label="WhatsApp">
							<img src="<?php echo esc_url( $whatsapp_icon ); ?>" alt="WhatsApp">
						</a>
						<a href="#" class="order-page__messenger-link order-page__messenger-link--viber" aria-label="Viber">
							<img src="<?php echo esc_url( $viber_icon ); ?>" alt="Viber">
						</a>
					</div>
				</div>

			</form>
		</div>

	</div>
</main>

<?php get_footer(); ?>
