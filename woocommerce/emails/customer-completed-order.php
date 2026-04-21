<?php
/*
	Письмо клиенту о выполненном заказе
				Для удобства давайте
				перейдем в чат одного из мессенджеров
				(WhatsApp, Viber, Telegram). Переходите по этой
				ссылке: https://moveat.expert/contact-messengers/
if ( ! defined( 'ABSPATH' ) ) {

			<!-- Иконки мессенджеров -->
			<div style="text-align:center;margin:12px 0 0;">
				<a href="https://moveat.expert/contact-messengers/" style="display:inline-block;margin:0 8px;" target="_blank" rel="noopener">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/whatsapp.png' ); ?>" width="36" alt="WhatsApp" style="border:0;display:block;" />
				</a>
				<a href="https://moveat.expert/contact-messengers/" style="display:inline-block;margin:0 8px;" target="_blank" rel="noopener">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/viber.png' ); ?>" width="36" alt="Viber" style="border:0;display:block;" />
				</a>
				<a href="https://moveat.expert/contact-messengers/" style="display:inline-block;margin:0 8px;" target="_blank" rel="noopener">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/telegram.png' ); ?>" width="36" alt="Telegram" style="border:0;display:block;" />
				</a>
			</div>
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );

/*
 * @hooked WC_Emails::email_header() Вывод шапки email
 */
// do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


	

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
					padding:30px 20px 10px 20px;
					text-align:center;
				">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/green-check-mark.png' ); ?>" width="80" alt="Logo" 
						style="
							display:block;
							margin:0 auto;
							border:0;
						" />
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" width="80" alt="Logo" 
						style="
							display:block;
							margin:0 auto;
							border:0;
							position:absolute;
							top: 20px;
							left:20px;
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
					Заказ выполнен
				</h1>
				</td>
			</tr>
			<tr>
				<td style="
					text-align:center;
					padding:0 20px 20px;
				">
					<h2 style="margin:0;font-size:24px;font-weight:bold;text-align:center;">Номер заказа: <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" style="color:#ff7f13;text-decoration:underline;font-size:20px;font-weight:600;">#<?php echo esc_html( $order_number ); ?></a></h2>
					<p style="margin:5px 0;color:#666;font-size:14px;text-align:center;"><?php echo esc_html( $order_date ); ?></p>
					<p style="margin:10px 0;color:#333;font-size:14px;text-align:center;line-height:140%;">
						Ваш заказ в Школе здорового питания
						Максима Погорелого отмечен у нас как
						"выполненный".
					</p>
					<p style="margin:10px 0;color:#333;font-size:14px;text-align:center;line-height:140%;">
						Для удобства давайте перейдем в чат одного из мессенджеров:
					</p>
					<!-- Иконки мессенджеров -->
					<div style="
							text-align:center;
							margin:12px 0 0;
							display:flex;
							flex-direction:center;
							justify-content:center;
							align-items: center;
							gap: 16px;
						">
						<a href="https://moveat.expert/contact-messengers/" 
							style="
								display:flex;
								justify-content:center;
								align-items:center;
								margin:0px;
								width:54px;
								height:54px;
								background:#25d366;
								border-radius:50%;
							" 
							target="_blank" rel="noopener">
							<img 
								src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/whatsapp.png' ); ?>" 
								width="32" 
								alt="WhatsApp" 
								style="
									border:0;
									display:block;
									margin:0px;
									filter: invert(99%) sepia(99%) saturate(0%) hue-rotate(283deg) brightness(105%) contrast(101%);
								"/>
						</a>
						<a href="https://moveat.expert/contact-messengers/" 
							style="
								display:flex;
								justify-content:center;
								align-items:center;
								margin:0px;
								width:54px;
								height:54px;
								background:#7360f2;
								border-radius:50%;
							" 
							target="_blank" rel="noopener">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/viber.png' ); ?>" width="26" alt="Viber" 
								style="
									border:0;
									display:block;
									margin:0px;
									filter: invert(99%) sepia(99%) saturate(0%) hue-rotate(283deg) brightness(105%) contrast(101%);
								" 
							/>
						</a>
						<a href="https://moveat.expert/contact-messengers/" 
							style="
								display:flex;
								justify-content:center;
								align-items:center;
								margin:0px;
								width:54px;
								height:54px;
								background:#24A1DE;
								border-radius:50%;
							"  
							target="_blank" rel="noopener">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/telegram.png' ); ?>" width="28" alt="Telegram" 
								style="
									border:0;
									display:block;
									margin:0px;
									filter: invert(99%) sepia(99%) saturate(0%) hue-rotate(283deg) brightness(105%) contrast(101%);
								" 
							/>
						</a>
					</div>
					<p style="margin:10px 0;color:#333;font-size:14px;text-align:center;line-height:140%;">
						Выбирайте удобный вам мессенджер для
						общения. Напишите в первом сообщении ваши Фамилию и Имя, а также email.
					</p>
					<?php if ( $needs_payment && $payment_url ) : ?>
						<p style="margin:10px 0;">
							<a href="<?php echo esc_url( $payment_url ); ?>" 
								style="
									background:#ff7f13;
									color:#fff;
									padding:10px 30px;
									border-radius:6px;
									text-decoration:none;
									display:inline-block;
									font-weight:bold;
									">
									ОПЛАТИТЬ ЗАКАЗ
							</a>
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
				<td style="padding:15px 20px;text-align:center;">
					<p style="
							margin:0;
							font-size:14px;
							color:#333;
						">
							Есть вопросы, или возникли проблемы с оплатой заказа? Пишите нам на почту 
						<a href="mailto:moveat.expert@gmail.com" 
							style="color:#ff7f13;text-decoration:underline;font-weight:600;">moveat.expert@gmail.com
						</a> или в мессенджеры.
					</p>
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
	// Дополнительный контент, если он передан
	// if ( $additional_content ) {
	// 	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	// }
}

// do_action( 'woocommerce_email_footer', $email );
