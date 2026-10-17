<?php
/*
	Template Name: Баннер — Долгая молодость
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

// Тексты по умолчанию — на языке страницы (assets/dictionaries/templates/banners).
$banner_dict = moveat_dictionary('templates/banners');

$default_eyebrow          = moveat_dictionary_text($banner_dict, 'youth_eyebrow');
$default_title            = moveat_dictionary_text($banner_dict, 'youth_title');
$default_subtitle         = moveat_dictionary_text($banner_dict, 'youth_subtitle');
$default_description      = moveat_dictionary_text($banner_dict, 'youth_description');
$default_signature        = moveat_dictionary_text($banner_dict, 'youth_signature');
$default_messengers_intro = moveat_dictionary_text($banner_dict, 'youth_messengers_intro');

$youth_eyebrow = $default_eyebrow;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_eyebrow', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$youth_eyebrow = $t;
	}
}

$youth_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$youth_title = $t;
	}
}

$youth_subtitle = $default_subtitle;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_subtitle', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$youth_subtitle = $t;
	}
}

$youth_description = $default_description;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_description', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$youth_description = $t;
	}
}

// Описание: каждая строка — отдельный абзац.
$youth_paragraphs = array_values(array_filter(array_map('trim', preg_split('/\R+/u', $youth_description)), 'strlen'));

$youth_signature = $default_signature;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_signature', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$youth_signature = $t;
	}
}

$youth_features = [];
$youth_features_max = 6;

for ($fi = 1; $fi <= $youth_features_max; $fi++) {
	$icon_raw = '';
	$label_raw = '';
	if (function_exists('get_field')) {
		$icon_raw  = get_field('banner_youth_feature_' . $fi . '_icon', $banner_acf_post_id);
		$label_raw = get_field('banner_youth_feature_' . $fi . '_label', $banner_acf_post_id);
	}
	$icon_ok = is_string($icon_raw) && trim($icon_raw) !== '';
	$lbl_ok  = is_string($label_raw) && trim($label_raw) !== '';
	if ($icon_ok && $lbl_ok) {
		$youth_features[] = [
			'icon'  => $icon_raw,
			'label' => $label_raw,
		];
	}
}

$youth_messengers_intro = $default_messengers_intro;
if (function_exists('get_field')) {
	$t = get_field('banner_youth_messengers_intro', $banner_acf_post_id);
	if (is_string($t)) {
		$youth_messengers_intro = $t;
	}
}

$messenger_buttons = [];
if (function_exists('get_field')) {
	$pairs = [
		[
			'url_field'  => 'banner_youth_link_telegram',
			'icon_field' => 'banner_youth_icon_telegram',
			'class'      => 'telegram',
			'alt'        => 'Telegram',
		],
		[
			'url_field'  => 'banner_youth_link_whatsapp',
			'icon_field' => 'banner_youth_icon_whatsapp',
			'class'      => 'whatsapp',
			'alt'        => 'WhatsApp',
		],
		[
			'url_field'  => 'banner_youth_link_viber',
			'icon_field' => 'banner_youth_icon_viber',
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

$show_messenger_block = trim($youth_messengers_intro) !== '' || ! empty($messenger_buttons);
?>

<article class="banner banner-youth" data-banner="youth">
	<div class="banner-youth__wrapper banner__wrapper">
		<div class="banner-youth__content">
			<div class="banner-youth__head">
				<?php if (trim($youth_eyebrow) !== '') : ?>
					<div class="banner-youth__eyebrow"><?php echo esc_html($youth_eyebrow); ?></div>
				<?php endif; ?>
				<div class="banner-youth__title-wrap title-decoration">
					<h3 class="banner-youth__title"><?php echo esc_html($youth_title); ?></h3>
					<div class="title-decoration_bottom" aria-hidden="true"></div>
				</div>
			</div>
			<div class="banner-youth__text-block">
				<?php if (trim($youth_subtitle) !== '') : ?>
					<p class="banner-youth__subtitle"><?php echo esc_html($youth_subtitle); ?></p>
				<?php endif; ?>
				<?php foreach ($youth_paragraphs as $paragraph) : ?>
					<p class="banner-youth__description"><?php echo esc_html($paragraph); ?></p>
				<?php endforeach; ?>
				<?php if (trim($youth_signature) !== '') : ?>
					<div class="banner-youth__signature"><?php echo esc_html($youth_signature); ?></div>
				<?php endif; ?>
			</div>
			<?php if (! empty($youth_features)) : ?>
				<ul class="banner-body__features" role="list">
					<?php foreach ($youth_features as $feat) : ?>
						<li class="banner-body__feature">
							<div class="banner-body__feature-icon-wrap" aria-hidden="true">
								<img
									class="banner-body__feature-icon"
									src="<?php echo esc_url($feat['icon']); ?>"
									alt="" />
							</div>
							<span class="banner-body__feature-label"><?php echo esc_html($feat['label']); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ($show_messenger_block) : ?>
				<div class="banner-youth__buttons banner-buttons">
					<?php if (trim($youth_messengers_intro) !== '') : ?>
						<div class="banner-buttons__title">
							<?php echo esc_html($youth_messengers_intro); ?>
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
