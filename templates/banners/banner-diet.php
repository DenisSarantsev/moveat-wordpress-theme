<?php
/*
	Template Name: Баннер — Диета
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

$default_title            = 'Расчет индивидуального сбалансированного рациона';
$default_subtitle         = 'Хотите узнать, почему диеты не работают?';
$default_description      = 'Важно учитывать все 14 параметров здоровья при расчете рациона';
$default_messengers_intro = 'Переходите в ваш любимый мессенджер';

$diet_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_diet_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$diet_title = $t;
	}
}

$diet_subtitle = $default_subtitle;
if (function_exists('get_field')) {
	$t = get_field('banner_diet_subtitle', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$diet_subtitle = $t;
	}
}

$diet_description = $default_description;
if (function_exists('get_field')) {
	$t = get_field('banner_diet_description', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$diet_description = $t;
	}
}

$diet_messengers_intro = $default_messengers_intro;
if (function_exists('get_field')) {
	$t = get_field('banner_diet_messengers_intro', $banner_acf_post_id);
	if (is_string($t)) {
		$diet_messengers_intro = $t;
	}
}

$messenger_buttons = [];
if (function_exists('get_field')) {
	$pairs = [
		[
			'url_field'  => 'banner_diet_link_telegram',
			'icon_field' => 'banner_diet_icon_telegram',
			'class'      => 'telegram',
			'alt'        => 'Telegram',
		],
		[
			'url_field'  => 'banner_diet_link_whatsapp',
			'icon_field' => 'banner_diet_icon_whatsapp',
			'class'      => 'whatsapp',
			'alt'        => 'WhatsApp',
		],
		[
			'url_field'  => 'banner_diet_link_viber',
			'icon_field' => 'banner_diet_icon_viber',
			'class'      => 'viber',
			'alt'        => 'Viber',
		],
	];
	foreach ($pairs as $pair) {
		$url  = get_field($pair['url_field'], $banner_acf_post_id);
		$icon = get_field($pair['icon_field'], $banner_acf_post_id);
		$url_ok  = is_string($url) && trim($url) !== '';
		$icon_ok = is_string($icon) && trim($icon) !== '';
		if ($url_ok && $icon_ok) {
			$messenger_buttons[] = [
				'href'  => $url,
				'icon'  => $icon,
				'class' => $pair['class'],
				'alt'   => $pair['alt'],
			];
		}
	}
}

$show_messenger_block = trim($diet_messengers_intro) !== '' || ! empty($messenger_buttons);
?>

<article class="banner banner-diet" data-banner="diet">
	<div class="banner-diet__wrapper banner__wrapper">
		<div class="banner-diet__content">
			<h3 class="banner-diet__title">
				<?php echo esc_html($diet_title); ?>
			</h3>
			<div class="banner-diet__subtitle">
				<?php echo esc_html($diet_subtitle); ?>
			</div>
			<div class="banner-diet__description">
				<?php echo esc_html($diet_description); ?>
			</div>
			<?php if ($show_messenger_block) : ?>
				<div class="banner-diet__buttons banner-buttons">
					<?php if (trim($diet_messengers_intro) !== '') : ?>
						<div class="banner-buttons__title">
							<?php echo esc_html($diet_messengers_intro); ?>
						</div>
					<?php endif; ?>
					<?php if (! empty($messenger_buttons)) : ?>
						<div class="banner-buttons__list">
							<?php foreach ($messenger_buttons as $btn) : ?>
								<a href="<?php echo esc_url($btn['href']); ?>" class="banner-buttons__button <?php echo esc_attr($btn['class']); ?>">
									<img src="<?php echo esc_url($btn['icon']); ?>" alt="<?php echo esc_attr($btn['alt']); ?>" />
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>

<?php
if (empty($moveat_banner_partial_only)) {
	get_footer();
}
