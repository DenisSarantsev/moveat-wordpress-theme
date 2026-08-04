<?php
/**
 * Template Name: Страница для донатов
 * Template Post Type: page
 * Description: Страница донатов — поля из assets/acf-fields/donations.json.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_queried_object_id();

$theme_uri = get_template_directory_uri();

$icon_cross               = $theme_uri . '/assets/images/icons/cross.png';
$icon_open_tab            = $theme_uri . '/assets/images/icons/open-in-new-tab.png';
$icon_content_copy        = $theme_uri . '/assets/images/icons/content-copy.png';
$icon_qr_code             = $theme_uri . '/assets/images/icons/qr-code.png';
$icon_credit_card         = $theme_uri . '/assets/images/illustrations/credit-card.png';
$illustration_paypal_card = $theme_uri . '/assets/images/illustrations/paypal-card.png';
$logotypes_uri            = $theme_uri . '/assets/images/logotypes/';
$logo_paypal              = $logotypes_uri . 'paypal.png';
$logo_visa                = $logotypes_uri . 'visa.png';
$logo_mastercard          = $logotypes_uri . 'mastercard.png';

$privacy_url = 'https://moveat.expert/politika-konfidentsialnosti/';
$terms_url   = 'https://moveat.expert/public-contract/';

$donations_page_title = trim( (string) get_field( 'donations_page_title', $page_id ) );
$donations_page_title = $donations_page_title !== '' ? $donations_page_title : 'Поддержать проект Moveat';

$donations_page_subtitle = trim( (string) get_field( 'donations_page_subtitle', $page_id ) );
$donations_page_subtitle = $donations_page_subtitle !== '' ? $donations_page_subtitle : 'Выберите удобный способ регулярной поддержки или отправьте разовый донат. Каждый вклад помогает создавать полезный контент о здоровом питании.';

$donations_page_content = get_field( 'donations_page_content', $page_id );

$donations_onetime_block_title = trim( (string) get_field( 'donations_onetime_block_title', $page_id ) );
$donations_onetime_block_title = $donations_onetime_block_title !== '' ? $donations_onetime_block_title : 'Единоразовый платеж';

$donations_onetime_button_text = trim( (string) get_field( 'donations_onetime_button_text', $page_id ) );
$donations_onetime_button_text = $donations_onetime_button_text !== '' ? $donations_onetime_button_text : 'Поддержать';

$donations_subscriptions_block_title = trim( (string) get_field( 'donations_subscriptions_block_title', $page_id ) );
$donations_subscriptions_block_title = $donations_subscriptions_block_title !== '' ? $donations_subscriptions_block_title : 'Подписки и спонсорство';

$donations_is_subscription_enabled = static function ( $field_name ) use ( $page_id ) {
	$value = get_field( $field_name, $page_id );
	if ( $value === null || $value === '' ) {
		return true;
	}
	return (bool) $value;
};

$donations_messengers = [];
$donations_messenger_slots = [
	[
		'slug'  => 'telegram',
		'field' => 'donations_onetime_messenger_telegram_url',
		'icon'  => $theme_uri . '/assets/images/icons/telegram.png',
		'label' => 'Telegram',
	],
	[
		'slug'  => 'whatsapp',
		'field' => 'donations_onetime_messenger_whatsapp_url',
		'icon'  => $theme_uri . '/assets/images/icons/whatsapp.png',
		'label' => 'WhatsApp',
	],
	[
		'slug'  => 'viber',
		'field' => 'donations_onetime_messenger_viber_url',
		'icon'  => $theme_uri . '/assets/images/icons/viber.png',
		'label' => 'Viber',
	],
];

foreach ( $donations_messenger_slots as $messenger_slot ) {
	$messenger_url = trim( (string) get_field( $messenger_slot['field'], $page_id ) );
	if ( $messenger_url === '' || $messenger_url === '#' ) {
		continue;
	}

	$donations_messengers[] = [
		'url'   => $messenger_url,
		'icon'  => $messenger_slot['icon'],
		'label' => $messenger_slot['label'],
		'slug'  => $messenger_slot['slug'],
	];
}

$subscription_platforms = [];
$subscription_platforms_config = [
	[
		'modifier'          => 'youtube',
		'enabled_field'     => 'donations_subscription_youtube_enabled',
		'url_field'         => 'donations_subscription_youtube_url',
		'name_field'        => 'donations_subscription_youtube_name',
		'description_field' => 'donations_subscription_youtube_description',
		'icon'              => $logotypes_uri . 'youtube.png',
		'icon_alt'          => 'YouTube',
		'name'              => 'Спонсорство на YouTube',
		'description'       => 'Ежемесячная поддержка через YouTube Membership',
	],
	[
		'modifier'          => 'patreon',
		'enabled_field'     => 'donations_subscription_patreon_enabled',
		'url_field'         => 'donations_subscription_patreon_url',
		'name_field'        => 'donations_subscription_patreon_name',
		'description_field' => 'donations_subscription_patreon_description',
		'icon'              => $logotypes_uri . 'patreon.png',
		'icon_alt'          => 'Patreon',
		'name'              => 'Patreon',
		'description'       => 'Регулярная подписка и эксклюзивные материалы',
	],
	[
		'modifier'          => 'paypal',
		'enabled_field'     => 'donations_subscription_paypal_enabled',
		'url_field'         => 'donations_subscription_paypal_url',
		'name_field'        => 'donations_subscription_paypal_name',
		'description_field' => 'donations_subscription_paypal_description',
		'icon'              => $logotypes_uri . 'paypal.png',
		'icon_alt'          => 'PayPal',
		'name'              => 'PayPal',
		'description'       => 'Подписка $10 / месяц',
	],
	[
		'modifier'          => 'lava-top',
		'enabled_field'     => 'donations_subscription_lava_top_enabled',
		'url_field'         => 'donations_subscription_lava_top_url',
		'name_field'        => 'donations_subscription_lava_top_name',
		'description_field' => 'donations_subscription_lava_top_description',
		'icon'              => $logotypes_uri . 'lava-top.png',
		'icon_alt'          => 'Lava.top',
		'name'              => 'Lava.top',
		'description'       => 'Разовая/регулярная поддержка через Lava/top',
	],
	[
		'modifier'          => 'boosty',
		'enabled_field'     => 'donations_subscription_boosty_enabled',
		'url_field'         => 'donations_subscription_boosty_url',
		'name_field'        => 'donations_subscription_boosty_name',
		'description_field' => 'donations_subscription_boosty_description',
		'icon'              => $logotypes_uri . 'boosty.webp',
		'icon_alt'          => 'Boosty',
		'name'              => 'Boosty',
		'description'       => 'Разовая/регулярная поддержка через Boosty',
	],
];

foreach ( $subscription_platforms_config as $platform_config ) {
	if ( ! $donations_is_subscription_enabled( $platform_config['enabled_field'] ) ) {
		continue;
	}

	$platform_url = trim( (string) get_field( $platform_config['url_field'], $page_id ) );

	$platform_name = trim( (string) get_field( $platform_config['name_field'], $page_id ) );
	$platform_name = $platform_name !== '' ? $platform_name : $platform_config['name'];

	$platform_description = trim( (string) get_field( $platform_config['description_field'], $page_id ) );
	$platform_description = $platform_description !== '' ? $platform_description : $platform_config['description'];

	$subscription_platforms[] = [
		'modifier'    => $platform_config['modifier'],
		'icon'        => $platform_config['icon'],
		'icon_alt'    => $platform_config['icon_alt'],
		'name'        => $platform_name,
		'description' => $platform_description,
		'url'         => $platform_url !== '' ? $platform_url : '#',
	];
}

$usdt_enabled = $donations_is_subscription_enabled( 'donations_subscription_usdt_enabled' );

$usdt_address = trim( (string) get_field( 'donations_subscription_usdt_wallet', $page_id ) );
$usdt_address = $usdt_address !== '' ? $usdt_address : 'TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

$usdt_qr_image = get_field( 'donations_subscription_usdt_qr_image', $page_id );
$usdt_qr_image = is_string( $usdt_qr_image ) ? trim( $usdt_qr_image ) : '';

$donations_min_amount = moveat_get_min_donation_amount();
$donations_min_amount_attr = esc_attr( (string) $donations_min_amount );
$donations_min_amount_label = moveat_format_donation_amount( $donations_min_amount );
?>

<main
	class="donations"
	data-donations-min-amount="<?php echo $donations_min_amount_attr; ?>">
	<div class="donations__wrapper">
		<header class="donations__header title-subtitle-header">
			<h1 class="section-title"><?php echo esc_html( $donations_page_title ); ?></h1>
			<p class="donations__subtitle text-secondary">
				<?php echo esc_html( $donations_page_subtitle ); ?>
			</p>
		</header>

		<div class="donations__platforms">
			<div class="donations__right">
				<h2 class="donations__block-title"><?php echo esc_html( $donations_onetime_block_title ); ?></h2>
				<div class="donations__onetime-card-button">
					<div class="donations__onetime-card-inner">
						<div class="donations__onetime-card-inner_left">
							<div class="donations__onetime-card-inner_left_images">
								<img
									src="<?php echo esc_url( $icon_credit_card ); ?>"
									alt="<?php echo esc_attr__( 'Банковская карта', 'moveat' ); ?>"
									loading="lazy"
									decoding="async" />
								<img
									src="<?php echo esc_url( $illustration_paypal_card ); ?>"
									alt="PayPal"
									loading="lazy"
									decoding="async" />
							</div>
							<div class="donations__onetime-card-inner_left_text">
								<span class="donations__onetime-card-inner_left-text-title">
									<?php echo esc_html__( 'Банковская карта или PayPal', 'moveat' ); ?>
								</span>
								<span class="donations__onetime-card-inner_left-text-description">
									<?php echo esc_html__( 'Разовый платеж через PayPal или банковскую карту', 'moveat' ); ?>
								</span>
							</div>
						</div>
						<div class="donations__onetime-card-inner_right">
							<button
								type="button"
								class="donations__onetime-card-inner_button"
								aria-haspopup="dialog">
								<div><?php echo esc_html( $donations_onetime_button_text ); ?></div>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="donations__left">
				<h2 class="donations__block-title"><?php echo esc_html( $donations_subscriptions_block_title ); ?></h2>
				<div class="donations__platforms-grid">
					<?php foreach ( $subscription_platforms as $platform ) : ?>
						<div class="donations__platform-card <?php echo esc_attr( $platform['modifier'] ); ?>">
							<div class="donations__platform-icon <?php echo esc_attr( $platform['modifier'] ); ?>">
								<img
									src="<?php echo esc_url( $platform['icon'] ); ?>"
									alt="<?php echo esc_attr( $platform['icon_alt'] ); ?>"
									loading="lazy"
									decoding="async" />
							</div>
							<div class="donations__platform-body">
								<span class="donations__platform-name"><?php echo esc_html( $platform['name'] ); ?></span>
								<span class="donations__platform-description"><?php echo esc_html( $platform['description'] ); ?></span>
							</div>
							<a
								class="donations__card-btn <?php echo esc_attr( $platform['modifier'] ); ?>"
								href="<?php echo esc_url( $platform['url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer">
								<div><?php echo esc_html__( 'Перейти', 'moveat' ); ?></div>
								<img src="<?php echo esc_url( $icon_open_tab ); ?>" alt="" loading="lazy" decoding="async" />
							</a>
						</div>
					<?php endforeach; ?>

					<?php if ( $usdt_enabled ) : ?>
					<div class="donations__platform-card usdt">
						<div class="donations__platform-icon usdt">
							<img
								src="<?php echo esc_url( $logotypes_uri . 'usdt.png' ); ?>"
								alt="USDT"
								loading="lazy"
								decoding="async" />
						</div>
						<div class="donations__platform-body">
							<span class="donations__platform-name">USDT (TRC20)</span>
							<span class="donations__platform-description">
								<?php echo esc_html__( 'Криптовалютный перевод на кошелёк TRC20', 'moveat' ); ?>
							</span>
						</div>
						<div class="donations__card-btns-usdt">
							<button
								type="button"
								class="donations__card-btn donations__card-btn--copy usdt"
								data-usdt-copy
								data-usdt-address="<?php echo esc_attr( $usdt_address ); ?>">
								<div><?php echo esc_html__( 'Скопировать адрес', 'moveat' ); ?></div>
								<img src="<?php echo esc_url( $icon_content_copy ); ?>" alt="<?php echo esc_attr__( 'Скопировать', 'moveat' ); ?>" loading="lazy" decoding="async" />
							</button>
							<button
								type="button"
								class="donations__card-btn-usdt-qr usdt"
								aria-label="<?php echo esc_attr__( 'Показать QR-код', 'moveat' ); ?>">
								<img src="<?php echo esc_url( $icon_qr_code ); ?>" alt="" loading="lazy" decoding="async" />
							</button>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $donations_page_content ) ) : ?>
		<div class="donations__content">
			<div class="donations__content_wrapper">
				<?php echo wp_kses_post( $donations_page_content ); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<div class="donations__payment-modal" role="dialog" aria-modal="true" aria-labelledby="donations-payment-modal-title">
		<div class="donations__payment-modal_wrapper">
			<div class="donations__payment-modal_form">
				<div class="donations__payment-modal_header">
					<button
						class="donations__payment-modal_close"
						type="button"
						aria-label="<?php echo esc_attr__( 'Закрыть', 'moveat' ); ?>">
						<img src="<?php echo esc_url( $icon_cross ); ?>" alt="" loading="lazy" decoding="async" />
					</button>
					<h2 class="donations__payment-modal_title" id="donations-payment-modal-title">
						<?php echo esc_html( $donations_onetime_block_title ); ?>
					</h2>
				</div>
				<div class="donations__payment-modal_content">
					<div class="donations__methods">
						<p class="donations__methods-title"><?php echo esc_html__( 'Способ оплаты', 'moveat' ); ?></p>
						<div class="donations__methods-list" data-donations-methods>
							<button
								type="button"
								class="donations__method-button"
								data-method="paypal"
								aria-pressed="false">
								<div class="donations__method-left">
									<div class="donations__method-radio" aria-hidden="true"></div>
									<div class="donations__method-text">
										<span class="donations__method-name">PayPal</span>
									</div>
								</div>
								<div class="donations__method-icons">
									<img
										class="donations__method-icon"
										src="<?php echo esc_url( $logo_paypal ); ?>"
										alt="PayPal"
										loading="lazy"
										decoding="async" />
								</div>
							</button>

							<button
								type="button"
								class="donations__method-button"
								data-method="mono_gateway"
								aria-pressed="false">
								<div class="donations__method-left">
									<div class="donations__method-radio" aria-hidden="true"></div>
									<div class="donations__method-text">
										<span class="donations__method-name"><?php echo esc_html__( 'Банковская карта', 'moveat' ); ?></span>
									</div>
								</div>
								<div class="donations__method-icons">
									<img
										class="donations__method-icon"
										src="<?php echo esc_url( $logo_visa ); ?>"
										alt="Visa"
										loading="lazy"
										decoding="async" />
									<img
										class="donations__method-icon"
										src="<?php echo esc_url( $logo_mastercard ); ?>"
										alt="Mastercard"
										loading="lazy"
										decoding="async" />
								</div>
							</button>
						</div>
					</div>

					<div class="donations__amounts">
						<p class="donations__amounts-title"><?php echo esc_html__( 'Сумма доната', 'moveat' ); ?></p>
						<div class="donations__amounts-wrapper">
							<div class="donations__amounts-list" data-donations-amounts>
								<button type="button" class="donations__amount-button" data-amount="5">$5</button>
								<button type="button" class="donations__amount-button" data-amount="15">$15</button>
								<button type="button" class="donations__amount-button" data-amount="75">$75</button>
								<button type="button" class="donations__amount-button" data-amount="150">$150</button>
								<input
									class="form-input donations__amount-custom-input"
									type="number"
									min="<?php echo $donations_min_amount_attr; ?>"
									step="0.01"
									placeholder="<?php echo esc_attr__( 'Другая сумма, $', 'moveat' ); ?>"
									data-donations-custom-amount
									inputmode="decimal" />
							</div>
							<div
								class="donations__amount-error"
								data-donations-min-amount-message>
								<?php
								printf(
									/* translators: %s: minimum donation amount in USD */
									esc_html__( 'Минимальная сумма доната - %s$', 'moveat' ),
									esc_html( $donations_min_amount_label )
								);
								?>
							</div>
						</div>
					</div>

					<div class="donations__personal">
						<div class="donations__field donations__field--half">
							<label
								class="donations__field-label"
								for="donationsFirstName">
								<?php echo esc_html__( 'Имя', 'moveat' ); ?><span aria-hidden="true">*</span>
							</label>
							<input
								class="form-input donations__field-input"
								type="text"
								id="donationsFirstName"
								name="firstName"
								placeholder="<?php echo esc_attr__( 'Иван', 'moveat' ); ?>"
								autocomplete="given-name"
								data-donations-firstname
								required
								aria-required="true" />
							<div
								class="donations__field-error"
								data-donations-firstname-error>
								<?php echo esc_html__( 'Введите имя', 'moveat' ); ?>
							</div>
						</div>

						<div class="donations__field donations__field--half">
							<label
								class="donations__field-label"
								for="donationsLastName">
								<?php echo esc_html__( 'Фамилия', 'moveat' ); ?>
							</label>
							<input
								class="form-input donations__field-input"
								type="text"
								id="donationsLastName"
								name="lastName"
								placeholder="<?php echo esc_attr__( 'Иванов', 'moveat' ); ?>"
								autocomplete="family-name"
								data-donations-lastname />
						</div>

						<div class="donations__field donations__field--half">
							<label class="donations__field-label" for="donationsPhone">
								<?php echo esc_html__( 'Номер телефона', 'moveat' ); ?><span aria-hidden="true">*</span>
							</label>
							<input
								class="form-input donations__field-input"
								type="tel"
								id="donationsPhone"
								name="phone"
								autocomplete="tel"
								data-donations-phone
								required
								aria-required="true" />
							<div
								class="donations__field-error"
								data-donations-phone-error>
								<?php echo esc_html__( 'Введите корректный номер телефона', 'moveat' ); ?>
							</div>
						</div>

						<div class="donations__field donations__field--half">
							<label class="donations__field-label" for="donationsEmail">
								Email<span aria-hidden="true">*</span>
							</label>
							<input
								class="form-input donations__field-input"
								type="email"
								id="donationsEmail"
								name="email"
								placeholder="ivan@example.com"
								autocomplete="email"
								data-donations-email
								required
								aria-required="true" />
							<div
								class="donations__field-error"
								data-donations-email-error>
								<?php echo esc_html__( 'Введите корректный email', 'moveat' ); ?>
							</div>
						</div>
					</div>

					<div
						class="donations__info-card"
						role="note"
						aria-label="<?php echo esc_attr__( 'Информация об оплате картой российского банка', 'moveat' ); ?>">
						<p class="donations__info-card-text">
							<?php
							echo wp_kses(
								__( 'Если у вас <span>НЕ ПРОХОДИТ ОПЛАТА,</span> свяжитесь с нами по одному из мессенджеров и мы поможем вам с оплатой.', 'moveat' ),
								[ 'span' => [] ]
							);
							?>
						</p>
						<?php if ( count( $donations_messengers ) > 0 ) : ?>
							<div class="donations__info-messengers">
								<?php foreach ( $donations_messengers as $messenger ) : ?>
									<?php
									$parsed  = wp_parse_url( $messenger['url'] );
									$scheme  = isset( $parsed['scheme'] ) ? strtolower( (string) $parsed['scheme'] ) : '';
									$is_http = in_array( $scheme, [ 'http', 'https' ], true );
									?>
									<a
										href="<?php echo esc_url( $messenger['url'], [ 'http', 'https', 'viber', 'mailto', 'tel' ] ); ?>"
										class="donations__messenger-link donations__messenger-link--<?php echo esc_attr( $messenger['slug'] ); ?>"
										<?php echo $is_http ? 'target="_blank"' : ''; ?>
										rel="noopener noreferrer"
										aria-label="<?php echo esc_attr( $messenger['label'] ); ?>">
										<img
											src="<?php echo esc_url( $messenger['icon'] ); ?>"
											alt="<?php echo esc_attr( $messenger['label'] ); ?>"
											loading="lazy"
											decoding="async" />
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<p class="donations__privacy-note">
						<?php
						printf(
							wp_kses(
								/* translators: %s: privacy policy URL */
								__( 'Ваши личные данные будут использоваться для обработки ваших заказов, упрощения вашей работы с сайтом и для других целей, описанных в нашей <a href="%s">политике конфиденциальности</a>.', 'moveat' ),
								[ 'a' => [ 'href' => [] ] ]
							),
							esc_url( $privacy_url )
						);
						?>
					</p>

					<div class="donations__checkbox-wrapper">
						<input
							class="donations__checkbox"
							type="checkbox"
							id="donationsAgreeTerms"
							name="donationsAgreeTerms"
							data-donations-agree
							required
							aria-required="true" />
						<label class="donations__checkbox-label" for="donationsAgreeTerms">
							<?php
							printf(
								wp_kses(
									/* translators: %s: terms URL */
									__( 'Я прочитал(а) и соглашаюсь с <a href="%s">правилами сайта</a>', 'moveat' ),
									[ 'a' => [ 'href' => [] ] ]
								),
								esc_url( $terms_url )
							);
							?>
							<span>*</span>
						</label>
					</div>

					<button
						type="button"
						class="primary-button donations__submit unactive"
						data-donations-submit
						disabled>
						<div class="donations__submit_inner">
							<div class="donations__submit_text"><?php echo esc_html__( 'Отправить донат', 'moveat' ); ?></div>
							<div class="donations__selected-amount" data-donations-selected-amount>
								<span>$0</span>
							</div>
						</div>
						<div class="loader white" aria-hidden="true"></div>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="donations__qr-modal" role="dialog" aria-modal="true" aria-labelledby="donations-qr-modal-title">
		<div class="donations__qr-modal_wrapper">
			<div class="donations__qr-modal_header">
				<button
					class="donations__qr-modal_close"
					type="button"
					aria-label="<?php echo esc_attr__( 'Закрыть', 'moveat' ); ?>">
					<img src="<?php echo esc_url( $icon_cross ); ?>" alt="" loading="lazy" decoding="async" />
				</button>
				<h2 class="donations__qr-modal_title" id="donations-qr-modal-title">
					<?php echo esc_html__( 'Отсканируйте QR-код', 'moveat' ); ?>
				</h2>
			</div>
			<div class="donations__qr-modal_content">
				<div class="donations__qr-modal_qr-image">
					<?php if ( $usdt_qr_image !== '' ) : ?>
					<img
						src="<?php echo esc_url( $usdt_qr_image ); ?>"
						alt="<?php echo esc_attr__( 'QR-код кошелька USDT', 'moveat' ); ?>"
						loading="lazy"
						decoding="async" />
					<?php endif; ?>
				</div>
				<div class="donations__qr-modal_network">
					<p class="donations__qr-modal_network-title">Network (Сеть):</p>
					<p class="donations__qr-modal_network-description">
						Tether TRC20
					</p>
				</div>
				<p class="donations__qr-modal_qr-text-description">
					<?php echo esc_html__( 'Или скопируйте адрес кошелька:', 'moveat' ); ?>
				</p>
				<div class="donations__qr-modal_qr-text">
					<div class="donations__qr-modal_wallet">
						<span
							class="donations__qr-modal_wallet-code"
							data-usdt-address-display><?php echo esc_html( $usdt_address ); ?></span>
						<button
							type="button"
							class="donations__qr-modal_wallet-copy"
							data-usdt-copy
							data-usdt-address="<?php echo esc_attr( $usdt_address ); ?>"
							aria-label="<?php echo esc_attr__( 'Скопировать адрес кошелька', 'moveat' ); ?>">
							<img src="<?php echo esc_url( $icon_content_copy ); ?>" alt="<?php echo esc_attr__( 'Скопировать', 'moveat' ); ?>" loading="lazy" decoding="async" />
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
