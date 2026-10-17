<?php
/*
	Template Name: Баннер — Вопросы
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

// Тексты по умолчанию — на языке страницы (assets/dictionaries/templates/banners).
$banner_dict = moveat_dictionary('templates/banners');

$default_title            = moveat_dictionary_text($banner_dict, 'questions_title');
$default_description      = moveat_dictionary_text($banner_dict, 'questions_description');
$default_messengers_intro = moveat_dictionary_text($banner_dict, 'questions_messengers_intro');

$questions_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_questions_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$questions_title = $t;
	}
}

$questions_description = $default_description;
if (function_exists('get_field')) {
	$t = get_field('banner_questions_description', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$questions_description = $t;
	}
}

$questions_messengers_intro = $default_messengers_intro;
if (function_exists('get_field')) {
	$t = get_field('banner_questions_messengers_intro', $banner_acf_post_id);
	if (is_string($t)) {
		$questions_messengers_intro = $t;
	}
}

$messenger_buttons = [];
if (function_exists('get_field')) {
	$pairs = [
		[
			'url_field'  => 'banner_questions_link_telegram',
			'icon_field' => 'banner_questions_icon_telegram',
			'class'      => 'telegram',
			'alt'        => 'Telegram',
		],
		[
			'url_field'  => 'banner_questions_link_whatsapp',
			'icon_field' => 'banner_questions_icon_whatsapp',
			'class'      => 'whatsapp',
			'alt'        => 'WhatsApp',
		],
		[
			'url_field'  => 'banner_questions_link_viber',
			'icon_field' => 'banner_questions_icon_viber',
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

$show_messenger_block = trim($questions_messengers_intro) !== '' || ! empty($messenger_buttons);
?>

<article class="banner banner-questions" data-banner="questions">
	<div class="banner-questions__wrapper banner__wrapper">
		<div class="banner-questions__content">
			<h3 class="banner-questions__title">
				<?php echo esc_html($questions_title); ?>
			</h3>
			<div class="banner-questions__description">
				<?php echo esc_html($questions_description); ?>
			</div>
			<?php if ($show_messenger_block) : ?>
				<div class="banner-questions__buttons banner-buttons">
					<?php if (trim($questions_messengers_intro) !== '') : ?>
						<div class="banner-buttons__title">
							<?php echo esc_html($questions_messengers_intro); ?>
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
