<?php
/*
	Template Name: Оплата не прошла
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

get_header();

$theme_uri = get_template_directory_uri();

// Если шлюз перенаправил с параметрами заказа, используем их для повторной оплаты
$pay_order_id  = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$pay_order_key = isset( $_GET['order_key'] ) ? sanitize_text_field( $_GET['order_key'] ) : '';
?>

<main class="problem-pay-page">
	<div class="problem-pay-page__container">
		<div class="problem-pay-page__card">

			<!-- Icon -->
			<div class="problem-pay-page__icon-wrapper">
				<svg
					class="problem-pay-page__icon"
					xmlns="http://www.w3.org/2000/svg"
					viewBox="0 0 24 24"
					aria-hidden="true"
					fill="none"
					stroke="currentColor"
					stroke-width="2">
					<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
					<line x1="12" y1="8" x2="12" y2="13"></line>
					<circle cx="12" cy="17" r="1"></circle>
				</svg>
			</div>

			<!-- Heading -->
			<div class="problem-pay-page__heading">
				<h1 class="problem-pay-page__title">Оплата не прошла</h1>
				<p class="problem-pay-page__subtitle">К сожалению, произошла ошибка при проведении платежа.</p>
			</div>

			<div class="problem-pay-page__divider"></div>

			<!-- Messengers (no email) -->
			<div class="problem-pay-page__messengers">
				<p class="problem-pay-page__messengers-title">Не получается оплатить? Напишите нам - мы поможем решить эту проблему:</p>
				<div class="problem-pay-page__messengers-list">
					<a href="#" class="problem-pay-page__messenger-link problem-pay-page__messenger-link--telegram" aria-label="Telegram">
						<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/telegram.png' ); ?>" alt="Telegram">
					</a>
					<a href="#" class="problem-pay-page__messenger-link problem-pay-page__messenger-link--whatsapp" aria-label="WhatsApp">
						<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/whatsapp.png' ); ?>" alt="WhatsApp">
					</a>
					<a href="#" class="problem-pay-page__messenger-link problem-pay-page__messenger-link--viber" aria-label="Viber">
						<img src="<?php echo esc_url( $theme_uri . '/assets/images/icons/viber.png' ); ?>" alt="Viber">
					</a>
				</div>
			</div>

			<div class="problem-pay-page__divider"></div>

			<!-- Action -->
			<div class="problem-pay-page__actions">
				<?php
				// Если знаем order_id и order_key — вернём пользователя на страницу выбора платежного метода для этого заказа
				if ( $pay_order_id ) {
					$retry_url = add_query_arg(
						array(
							'order_id' => $pay_order_id,
							'order_key' => $pay_order_key,
						),
						home_url( '/order-pay/' )
					);
				} else {
					$retry_url = wc_get_checkout_url();
				}
				?>
				<a href="<?php echo esc_url( $retry_url ); ?>" class="primary-button problem-pay-page__retry-button">Вернуться на страницу оплаты</a>
			</div>

		</div>
	</div>
</main>

<?php get_footer();

