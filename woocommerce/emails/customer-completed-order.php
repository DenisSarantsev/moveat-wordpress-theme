<?php
/**
 * Customer completed order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-completed-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


		<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
		<p>
		<?php
		if ( ! empty( $order->get_billing_first_name() ) ) {
			/* translators: %s: Customer first name */
			printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) );
		} else {
			printf( esc_html__( 'Hi,', 'woocommerce' ) );
		}
		?>
		</p>
		<p><?php esc_html_e( 'We have finished processing your order.', 'woocommerce' ); ?></p>
		<?php if ( $email_improvements_enabled ) : ?>
			<p><?php esc_html_e( 'Here’s a reminder of what you’ve ordered:', 'woocommerce' ); ?></p>
		<?php endif; ?>
		<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

		<?php
		wc_get_template(
			'emails/email-order-details.php',
			array(
				'order'         => $order,
				'sent_to_admin' => $sent_to_admin,
				'plain_text'    => $plain_text,
				'email'         => $email,
			// Пытаемся явно отключить вывод секции "Загрузки" в заказе
			'show_downloads' => false,
			// Явно отключаем показ изображений товаров в деталях заказа
			'show_image'     => false,
			)
		);

		if ( $additional_content ) {
			echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' : '';
			echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
			echo $email_improvements_enabled ? '</td></tr></table>' : '';
		}

		do_action( 'woocommerce_email_footer', $email );
