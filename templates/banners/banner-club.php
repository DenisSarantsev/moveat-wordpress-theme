<?php
/*
	Template Name: Баннер — Клуб
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

$default_img_alt       = 'Клуб МАКСимального здоровья';
$default_title         = 'Клуб МАКСимального здоровья';
$default_subtitle      = 'Есть вопросы о питании лично к Максу?';
$default_description   = 'Обсуждения, рецепты, личные рекомендации, поддержка единомышленников. Мы ждем вас в клубе!';
$default_messengers_intro = 'Переходите в удобный мессенджер, где мы расскажем про клуб подробнее (стоимость, условия)';

$club_main_image = '';
if (function_exists('get_field')) {
	$img = get_field('banner_club_main_image', $banner_acf_post_id);
	if (is_string($img) && trim($img) !== '') {
		$club_main_image = $img;
	}
}

$club_main_image_alt = $default_img_alt;
if (function_exists('get_field')) {
	$a = get_field('banner_club_main_image_alt', $banner_acf_post_id);
	if (is_string($a) && trim($a) !== '') {
		$club_main_image_alt = $a;
	}
}

$club_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_club_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$club_title = $t;
	}
}

$club_subtitle = $default_subtitle;
if (function_exists('get_field')) {
	$t = get_field('banner_club_subtitle', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$club_subtitle = $t;
	}
}

$club_description = $default_description;
if (function_exists('get_field')) {
	$t = get_field('banner_club_description', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$club_description = $t;
	}
}

$club_messengers_intro = $default_messengers_intro;
if (function_exists('get_field')) {
	$t = get_field('banner_club_messengers_intro', $banner_acf_post_id);
	if (is_string($t)) {
		$club_messengers_intro = $t;
	}
}

$messenger_buttons = [];
if (function_exists('get_field')) {
	$pairs = [
		[
			'url_field' => 'banner_club_link_telegram',
			'icon_field' => 'banner_club_icon_telegram',
			'class' => 'telegram',
			'alt' => 'Telegram',
		],
		[
			'url_field' => 'banner_club_link_whatsapp',
			'icon_field' => 'banner_club_icon_whatsapp',
			'class' => 'whatsapp',
			'alt' => 'WhatsApp',
		],
		[
			'url_field' => 'banner_club_link_viber',
			'icon_field' => 'banner_club_icon_viber',
			'class' => 'viber',
			'alt' => 'Viber',
		],
	];
	foreach ($pairs as $pair) {
		$url  = get_field($pair['url_field'], $banner_acf_post_id);
		$icon = get_field($pair['icon_field'], $banner_acf_post_id);
		$url_ok  = is_string($url) && trim($url) !== '';
		$icon_ok = is_string($icon) && trim($icon) !== '';
		if ($url_ok && $icon_ok) {
			$messenger_buttons[] = [
				'href' => $url,
				'icon' => $icon,
				'class' => $pair['class'],
				'alt' => $pair['alt'],
			];
		}
	}
}

$show_messenger_block = trim($club_messengers_intro) !== '' || ! empty($messenger_buttons);
?>

<article class="banner banner-club" data-banner="club">
	<div class="banner-club__wrapper banner__wrapper">
		<?php if ($club_main_image !== '') : ?>
			<div class="banner-club__image-wrapper">
				<img
					class="banner-club__image"
					src="<?php echo esc_url($club_main_image); ?>"
					alt="<?php echo esc_attr($club_main_image_alt); ?>" />
			</div>
		<?php endif; ?>
		<div class="banner-club__content">
			<h3 class="banner-club__title"><?php echo esc_html($club_title); ?></h3>
			<div class="banner-club__subtitle">
				<?php echo esc_html($club_subtitle); ?>
			</div>
			<div class="banner-club__description">
				<?php echo esc_html($club_description); ?>
			</div>
			<?php if ($club_main_image !== '') : ?>
				<img
					class="banner-club__image-mobile"
					src="<?php echo esc_url($club_main_image); ?>"
					alt="<?php echo esc_attr($club_main_image_alt); ?>" />
			<?php endif; ?>
			<?php if ($show_messenger_block) : ?>
				<div class="banner-club__buttons banner-buttons">
					<?php if (trim($club_messengers_intro) !== '') : ?>
						<div class="banner-buttons__title">
							<?php echo esc_html($club_messengers_intro); ?>
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
