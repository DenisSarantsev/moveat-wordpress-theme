<?php
/*
	Письмо донатору после успешной оплаты доната
*/
?>

<?php
if ( ! isset( $order ) || ! is_a( $order, 'WC_Order' ) ) {
	wc_get_template(
		'emails/email-order-details.php',
		array(
			'order'          => $order,
			'sent_to_admin'  => $sent_to_admin ?? false,
			'plain_text'     => $plain_text ?? false,
			'email'          => $email ?? null,
			'show_downloads' => false,
			'show_image'     => false,
		)
	);
} else {
	$order_date    = $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '';
	$total_amount  = $order->get_total();
	$payment_label = isset( $payment_method_label ) ? $payment_method_label : '—';
	$social_items  = isset( $social_items ) && is_array( $social_items ) ? $social_items : array();

	$content_defaults = array(
		'greeting'      => 'Здравствуйте!',
		'thanks'        => 'Спасибо за вашу поддержку ❤️',
		'intro'         => '',
		'details_title' => 'Детали поддержки:',
		'label_amount'  => 'Сумма:',
		'label_date'    => 'Дата:',
		'label_payment' => 'Способ оплаты:',
		'outro_thanks'  => 'Спасибо, что вы с нами.',
		'signoff_line'  => 'С уважением,',
		'signoff_team'  => 'Команда Moveat',
		'socials_title' => 'Следите за новыми материалами:',
		'footer'        => 'Moveat',
	);

	$content = isset( $email_content ) && is_array( $email_content )
		? array_merge( $content_defaults, $email_content )
		: $content_defaults;

	$text_style = 'margin:0 0 14px;color:#333;font-size:15px;line-height:1.55;text-align:left;';
	?>

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
			">
			<tr>
				<td style="padding:40px 24px 0;text-align:center;">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/3d/heart.png' ); ?>" width="70" alt=""
						style="display:block;margin:0 auto;border:0;" />
				</td>
			</tr>
			<?php if ( $content['greeting'] !== '' ) : ?>
			<tr>
				<td style="text-align:center;padding:10px 20px;">
					<h1 style="margin:0;font-size:46px;line-height:46px;color:#000;font-weight:bold;text-align:center;"><?php echo esc_html( $content['greeting'] ); ?></h1>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td style="text-align:center;padding:0 20px 20px;">
					<?php if ( $content['thanks'] !== '' ) : ?>
						<h2 style="margin:0;font-size:24px;font-weight:bold;text-align:center;"><?php echo esc_html( $content['thanks'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $content['intro'] !== '' ) : ?>
						<p style="margin:10px 0;color:#333;font-size:14px;text-align:center;line-height:140%;"><?php echo esc_html( $content['intro'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td style="padding:0 24px 24px;">
					<table width="100%" style="border-collapse:collapse;background:#fafafa;border-radius:12px;">
						<tr>
							<td style="padding:16px 18px;">
								<?php if ( $content['details_title'] !== '' ) : ?>
									<p style="margin:0 0 12px;font-size:16px;font-weight:bold;color:#000;"><?php echo esc_html( $content['details_title'] ); ?></p>
								<?php endif; ?>
								<p style="margin:0 0 8px;font-size:14px;color:#333;line-height:1.5;">
									<?php echo esc_html( $content['label_amount'] ); ?> <strong><?php echo wp_kses_post( wc_price( $total_amount ) ); ?></strong>
								</p>
								<p style="margin:0 0 8px;font-size:14px;color:#333;line-height:1.5;">
									<?php echo esc_html( $content['label_date'] ); ?> <strong><?php echo esc_html( $order_date ); ?></strong>
								</p>
								<p style="margin:0;font-size:14px;color:#333;line-height:1.5;">
									<?php echo esc_html( $content['label_payment'] ); ?> <strong><?php echo esc_html( $payment_label ); ?></strong>
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0 24px 8px;border-top:2px solid #efefef;">
					<?php if ( $content['outro_thanks'] !== '' ) : ?>
						<p style="<?php echo esc_attr( $text_style ); ?> margin-top:20px;"><?php echo esc_html( $content['outro_thanks'] ); ?></p>
					<?php endif; ?>
					<?php if ( $content['signoff_line'] !== '' ) : ?>
						<p style="margin:0 0 6px;font-size:15px;color:#333;line-height:1.5;"><?php echo esc_html( $content['signoff_line'] ); ?></p>
					<?php endif; ?>
					<?php if ( $content['signoff_team'] !== '' ) : ?>
						<p style="margin:0 0 20px;font-size:15px;color:#333;font-weight:bold;line-height:1.5;"><?php echo esc_html( $content['signoff_team'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<?php if ( ! empty( $social_items ) ) : ?>
			<tr>
				<td style="padding:0 24px 28px;text-align:center;">
					<?php if ( $content['socials_title'] !== '' ) : ?>
						<p style="margin:0 0 14px;font-size:14px;color:#666;"><?php echo esc_html( $content['socials_title'] ); ?></p>
					<?php endif; ?>
					<table role="presentation" align="center" style="margin:0 auto;border-collapse:collapse;">
						<tr>
							<?php foreach ( $social_items as $social_item ) : ?>
								<?php
								$social_url      = isset( $social_item['url'] ) ? $social_item['url'] : '';
								$social_icon     = isset( $social_item['icon_url'] ) ? $social_item['icon_url'] : '';
								$social_bg       = isset( $social_item['bg_color'] ) ? $social_item['bg_color'] : '#ff7f13';
								$social_label    = isset( $social_item['label'] ) ? $social_item['label'] : '';
								$social_bg_style = 'display:inline-block;width:54px;height:54px;background:' . esc_attr( $social_bg ) . ';border-radius:50%;text-decoration:none;';
								?>
								<td style="padding:0 8px;text-align:center;vertical-align:top;">
									<a href="<?php echo esc_url( $social_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $social_label ); ?>" style="<?php echo esc_attr( $social_bg_style ); ?>">
										<img src="<?php echo esc_url( $social_icon ); ?>" width="28" height="28" alt="<?php echo esc_attr( $social_label ); ?>" style="display:block;margin:13px auto;border:0;" />
									</a>
								</td>
							<?php endforeach; ?>
						</tr>
					</table>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<td style="padding:16px 24px 24px;text-align:center;font-size:12px;color:#999;border-top:2px solid #efefef;">
					<?php echo esc_html( $content['footer'] ); ?>
				</td>
			</tr>
		</table>
	</div>

	<?php
}
