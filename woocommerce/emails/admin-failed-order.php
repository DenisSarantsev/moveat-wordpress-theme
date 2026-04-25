<?php
/*
	Письмо админу о новом заказе
*/
?>

<?php
// Рендерим HTML-письмо по предоставленной структуре и подставляем динамические данные заказа.
if ( ! isset( $order ) || ! is_a( $order, 'WC_Order' ) ) {
	// Резервный вариант: выводим стандартный шаблон деталей заказа, если объект $order отсутствует.
	wc_get_template(
		'emails/email-order-details.php',
		array(
			'order'         => $order,
			'sent_to_admin' => $sent_to_admin,
			'plain_text'    => $plain_text,
			'email'         => $email,
			'show_downloads' => false,
			'show_image'     => false,
		)
	);
} else {
	$order_number    = $order->get_order_number();
	$order_date      = $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '';
	$needs_payment   = method_exists( $order, 'needs_payment' ) ? $order->needs_payment() : false;
	$payment_url     = $needs_payment && method_exists( $order, 'get_checkout_payment_url' ) ? $order->get_checkout_payment_url() : '';
	$subtotal_amount = $order->get_subtotal();
	$discount_total  = $order->get_discount_total();
	$total_amount    = $order->get_total();
	?>

	<!-- Пользовательское HTML-письмо (основано на New Message.html) -->
	<div style="
		background-color:#FAFAFA;
		font-family:Arial,Helvetica,sans-serif;
		padding:20px;
	">
		<table width="470" align="center" 
			style="
				background:#ffffff;
				margin:0 auto;
				border-collapse:collapse;
				border-radius:20px;
				box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
				position:relative;
			">
			<tr>
				<td style="
					padding:50px 20px 0px 20px;
					text-align:center;
				">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/error.png' ); ?>" width="70" alt="Logo" 
						style="
							display:block;
							margin:0 auto;
							border:0;
						" />
				</td>
			</tr>
			<tr>
				<td style="
					text-align:center;
					padding:10px 20px;
				">
					<h1 style="
						margin:0;
						font-size:46px;
						line-height:46px;
						color:#000;
						font-weight:bold;
						text-align:center;
					">
					Ошибка оплаты
				</h1>
				</td>
			</tr>
			<tr>
				<td style="
					text-align:center;
					padding:0 20px 20px;
				">
					<h2 style="margin:0;font-size:24px;font-weight:bold;text-align:center;">Номер заказа: <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" style="color:#ff7f13;text-decoration:underline;font-size:20px;font-weight:600;">#<?php echo esc_html( $order_number ); ?></a></h2>
					<p style="margin:5px 0;color:#666;font-size:14px;text-align:center;padding:4px 0px;"><?php echo esc_html( $order_date ); ?></p>
				</td>
			</tr>

			<tr>
				<td style="
					text-align:center;
					padding:0 20px 20px;
				">
					<p style="
						margin:5px 0;
						color:#666;
						font-size:14px;
						text-align:center;
						padding:4px 0px;
					">
						У клиента возникла проблема в процессе оплаты заказа.
					</p>
				</td>
			</tr>

			<tr>
				<td style="padding:10px 20px;border-top:2px solid #efefef;border-bottom:2px solid #efefef;">
					<?php
					// Контактные данные покупателя
					$billing_first = $order->get_billing_first_name();
					$billing_last  = $order->get_billing_last_name();
					$billing_phone = $order->get_billing_phone();
					$billing_email = $order->get_billing_email();
					?>
					<?php if ( $billing_first || $billing_last || $billing_phone || $billing_email ) : ?>
						<p style="margin:8px 0;color:#333;font-size:14px;text-align:center;line-height:1.4;">
							<?php if ( $billing_first || $billing_last ) : ?>
								<strong><?php echo esc_html( trim( $billing_first . ' ' . $billing_last ) ); ?></strong><br />
							<?php endif; ?>
							<?php if ( $billing_phone ) : ?>
								Телефон: <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $billing_phone ) ); ?>" style="color:#000;text-decoration:underline;"><?php echo esc_html( $billing_phone ); ?></a><br />
							<?php endif; ?>
							<?php if ( $billing_email ) : ?>
								Email: <a href="mailto:<?php echo esc_attr( $billing_email ); ?>" style="color:#000;text-decoration:underline;"><?php echo esc_html( $billing_email ); ?></a>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<td style="padding:10px 20px;">
					<!-- Товары в заказе -->
					<?php foreach ( $order->get_items() as $item_id => $item ) :
						$product_name = $item->get_name();
						$qty = $item->get_quantity();
						$line_subtotal = $order->get_formatted_line_subtotal( $item );
						?>
						<table width="100%" style="border-collapse:collapse;margin-bottom:8px;">
							<tr>
								<td style="width:60%;padding:6px 0;text-align:left;"><strong><?php echo wp_kses_post( $product_name ); ?></strong></td>
								<td style="width:15%;padding:6px 6px;text-align:left;"><?php echo esc_html( $qty ); ?> шт</td>
								<td style="width:25%;padding:6px 0;text-align:right;"><?php echo wp_kses_post( $line_subtotal ); ?></td>
							</tr>
						</table>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<td style="padding:0 20px;border-top:2px solid #efefef;border-bottom:2px solid #efefef;">
					<p style="text-align:right;padding-top:14px;margin:0;font-size:14px;">Сумма: <strong><?php echo wp_kses_post( wc_price( $subtotal_amount ) ); ?></strong></p>
					<?php if ( (float) $discount_total > 0 ) : ?>
						<p style="text-align:right;margin:0 0 0;font-size:14px;margin-top:4px;">Скидка: <strong><?php echo wp_kses_post( wc_price( $discount_total ) ); ?></strong></p>
					<?php endif; ?>
					<p style="text-align:right;margin-top:20px;font-size:16px;">К оплате: <strong><?php echo wp_kses_post( wc_price( $total_amount ) ); ?></strong></p>
				</td>
			</tr>

			<tr>
				<td style="padding:20px;text-align:center;font-size:12px;color:#999;">
					Moveat Expert
				</td>
			</tr>
		</table>
	</div>

	<?php
}

