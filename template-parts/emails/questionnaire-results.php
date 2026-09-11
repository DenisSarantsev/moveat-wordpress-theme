<?php
/*
	Template part: письмо с результатами опросника

	Презентационный шаблон: получает готовые блоки и раскладывает их по
	таблицам. К ACF и параметрам адреса не обращается — это забота модуля
	assets/scripts/php/questionnaire/results-email.php.

	Ожидает переменные:
	$email_blocks  — массив блоков (title, description, text, links, scale)
	$email_page_id — ID страницы результатов
	$email_bar     — ширина цветной полосы шкалы, px

	Вёрстка таблицами с inline-стилями: почтовые клиенты не понимают ни flex,
	ни position, а Outlook вдобавок теряет проценты во вложенных таблицах —
	поэтому все ширины посчитаны в пикселях.
*/

defined( 'ABSPATH' ) || exit;

if ( empty( $email_blocks ) ) {
	return;
}

$bar_width = isset( $email_bar ) ? (int) $email_bar : 520;

// Три равные зоны шкалы — как на странице результатов
$segment       = (int) round( $bar_width / 3 );
$segment_last  = $bar_width - $segment * 2;

$marker_width = 44;
$icon_url     = get_template_directory_uri() . '/assets/images/icons/3d/info.png';
$site_url     = home_url( '/' );
?>
<div style="background-color:#fafafa;font-family:Arial,Helvetica,sans-serif;padding:20px 10px;">
	<table width="600" align="center" cellpadding="0" cellspacing="0" border="0" role="presentation"
		style="background:#ffffff;margin:0 auto;border-collapse:collapse;border-radius:20px;box-shadow:rgba(100,100,111,0.2) 0px 7px 29px 0px;max-width:600px;">

		<!-- Шапка -->
		<tr>
			<td align="center" style="padding:40px 30px 0 30px;">
				<img src="<?php echo esc_url( $icon_url ); ?>" width="70" alt=""
					style="display:block;border:0;margin:0 auto 20px auto;" />
				<h1 style="margin:0 0 12px 0;font-size:24px;line-height:1.3;color:#111111;font-weight:700;">
					Ваши результаты по 8 индикаторам здоровья
				</h1>
				<p style="margin:0;font-size:15px;line-height:1.5;color:#5f6368;">
					Сравнили содержимое вашей тарелки с самочувствием — и собрали выводы,
					которые можно проверить и изменить уже сейчас.
				</p>
			</td>
		</tr>

		<?php foreach ( $email_blocks as $index => $block ) : ?>
			<?php
			$is_first = 0 === $index;
			$scale    = isset( $block['scale'] ) ? $block['scale'] : null;

			// Отметка ставится по центру своей позиции на полосе
			if ( $scale ) {
				$position = (int) round( $bar_width * $scale['percent'] / 100 );
				$spacer   = max( 0, min( $bar_width - $marker_width, $position - (int) round( $marker_width / 2 ) ) );
				$tail     = max( 0, $bar_width - $spacer - $marker_width );
			}
			?>

			<?php if ( 1 === $index ) : ?>
				<!-- Заголовок раздела с разбором по категориям -->
				<tr>
					<td style="padding:34px 30px 0 30px;">
						<div style="border-top:1px solid #e8e8e8;padding-top:28px;">
							<h2 style="margin:0 0 8px 0;font-size:20px;line-height:1.3;color:#111111;">
								Результаты
							</h2>
							<p style="margin:0;font-size:14px;line-height:1.5;color:#5f6368;">
								Подробнее о том, какие нарушения питания и состояния здоровья
								мы можем заподозрить после анализа ваших ответов.
							</p>
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<td style="padding:<?php echo $is_first ? '28px 30px 0 30px' : '26px 30px 0 30px'; ?>;">
					<?php if ( $is_first ) : ?>
						<div style="border-top:1px solid #e8e8e8;padding-top:28px;"></div>
					<?php endif; ?>

					<h<?php echo $is_first ? '2' : '3'; ?> style="margin:0 0 <?php echo $scale ? '16' : '12'; ?>px 0;font-size:<?php echo $is_first ? '20' : '17'; ?>px;line-height:1.35;color:#111111;">
						<?php echo esc_html( $block['title'] ); ?>
					</h<?php echo $is_first ? '2' : '3'; ?>>

					<?php if ( ! empty( $block['description'] ) ) : ?>
						<div style="margin:0 0 16px 0;font-size:14px;line-height:1.6;color:#5f6368;">
							<?php echo wp_kses_post( $block['description'] ); ?>
						</div>
					<?php endif; ?>
				</td>
			</tr>

			<?php if ( $scale ) : ?>
				<!-- Шкала: отметка, цветные зоны и подписи краёв -->
				<tr>
					<td align="center" style="padding:0 30px 4px 30px;">
						<table width="<?php echo esc_attr( $bar_width ); ?>" cellpadding="0" cellspacing="0" border="0" role="presentation"
							style="border-collapse:collapse;">
							<tr>
								<td width="<?php echo esc_attr( $spacer ); ?>" style="font-size:1px;line-height:1px;">&nbsp;</td>
								<td width="<?php echo esc_attr( $marker_width ); ?>" align="center"
									style="font-size:18px;line-height:1.1;font-weight:700;color:#111111;padding-bottom:2px;">
									<?php echo esc_html( $scale['value'] ); ?>
								</td>
								<td width="<?php echo esc_attr( $tail ); ?>" style="font-size:1px;line-height:1px;">&nbsp;</td>
							</tr>
							<tr>
								<td width="<?php echo esc_attr( $spacer ); ?>" style="font-size:1px;line-height:1px;">&nbsp;</td>
								<td width="<?php echo esc_attr( $marker_width ); ?>" align="center" style="font-size:1px;line-height:1px;">
									<!-- Треугольник на границах: переживает даже Outlook -->
									<div style="width:0;height:0;margin:0 auto;border-left:7px solid transparent;border-right:7px solid transparent;border-top:10px solid #333333;font-size:0;line-height:0;"></div>
								</td>
								<td width="<?php echo esc_attr( $tail ); ?>" style="font-size:1px;line-height:1px;">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="padding:0 30px;">
						<table width="<?php echo esc_attr( $bar_width ); ?>" cellpadding="0" cellspacing="0" border="0" role="presentation"
							style="border-collapse:collapse;border-radius:12px;overflow:hidden;">
							<tr>
								<td width="<?php echo esc_attr( $segment ); ?>" bgcolor="#57bd18"
									style="background-color:#57bd18;height:22px;font-size:1px;line-height:22px;">&nbsp;</td>
								<td width="<?php echo esc_attr( $segment ); ?>" bgcolor="#f6b119"
									style="background-color:#f6b119;height:22px;font-size:1px;line-height:22px;">&nbsp;</td>
								<td width="<?php echo esc_attr( $segment_last ); ?>" bgcolor="#f04a28"
									style="background-color:#f04a28;height:22px;font-size:1px;line-height:22px;">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" style="padding:6px 30px 16px 30px;">
						<table width="<?php echo esc_attr( $bar_width ); ?>" cellpadding="0" cellspacing="0" border="0" role="presentation"
							style="border-collapse:collapse;">
							<tr>
								<td align="left" style="font-size:12px;line-height:1.3;color:#8a8a8a;">
									0 &middot; Низкий
								</td>
								<td align="right" style="font-size:12px;line-height:1.3;color:#8a8a8a;">
									Высокий &middot; <?php echo esc_html( $scale['max'] ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			<?php endif; ?>

			<!-- Вывод по блоку -->
			<tr>
				<td style="padding:0 30px;">
					<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
						style="border-collapse:collapse;background:#fafafa;border-radius:12px;">
						<tr>
							<td style="padding:18px 20px;font-size:14px;line-height:1.6;color:#333333;">
								<?php echo wp_kses_post( $block['text'] ); ?>

								<?php if ( ! empty( $block['links'] ) ) : ?>
									<?php foreach ( $block['links'] as $link ) : ?>
										<a href="<?php echo esc_url( $link['url'] ); ?>"
											style="display:block;margin-top:10px;color:#ff7f13;font-weight:600;text-decoration:underline;">
											<?php echo esc_html( $link['label'] ); ?>
										</a>
									<?php endforeach; ?>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php endforeach; ?>

		<!-- Подвал -->
		<tr>
			<td align="center" style="padding:30px 30px 0 30px;">
				<div style="border-top:1px solid #e8e8e8;padding-top:24px;">
					<p style="margin:0 0 14px 0;font-size:14px;line-height:1.5;color:#333333;">
						Остались вопросы по результатам? Напишите нам на
						<a href="mailto:moveat.expert@gmail.com"
							style="color:#ff7f13;text-decoration:underline;font-weight:600;">moveat.expert@gmail.com</a>
						или в мессенджеры — разберём вашу ситуацию подробнее.
					</p>
					<a href="<?php echo esc_url( $site_url ); ?>"
						style="color:#ff7f13;text-decoration:underline;font-size:14px;font-weight:600;">
						Перейти на сайт Moveat
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td align="center" style="padding:20px 30px 30px 30px;font-size:12px;line-height:1.4;color:#999999;">
				Moveat Expert<br />
				Письмо отправлено, потому что вы прошли опросник о качестве питания.
			</td>
		</tr>
	</table>
</div>
