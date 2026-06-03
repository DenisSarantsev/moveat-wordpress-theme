<?php
/*
	Template Name: Баннер — Строим здоровое тело вместе
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

$default_title       = 'Программа «Строим здоровое тело вместе»';
$default_description = 'Мы поможем вам перейти на здоровое питание, ответим на волнующие вопросы и подберем персональный рацион. Вместе с командой врачей мы приведем вас к результату! Начните путь к здоровью - записывайтесь сейчас!';
$default_signature   = 'Максим Погорелый';

$banner_body_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_build_body_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$banner_body_title = $t;
	}
}

$banner_body_description = $default_description;
if (function_exists('get_field')) {
	$d = get_field('banner_build_body_description', $banner_acf_post_id);
	if (is_string($d) && $d !== '') {
		$banner_body_description = $d;
	}
}

$banner_body_signature = $default_signature;
if (function_exists('get_field')) {
	$s = get_field('banner_build_body_signature', $banner_acf_post_id);
	if (is_string($s) && $s !== '') {
		$banner_body_signature = $s;
	}
}

$banner_body_features = [];
$banner_body_features_max = 10;

for ($fi = 1; $fi <= $banner_body_features_max; $fi++) {
	$icon_raw = '';
	$label_raw = '';
	if (function_exists('get_field')) {
		$icon_raw  = get_field('banner_build_body_feature_' . $fi . '_icon', $banner_acf_post_id);
		$label_raw = get_field('banner_build_body_feature_' . $fi . '_label', $banner_acf_post_id);
	}
	$icon_ok = is_string($icon_raw) && trim($icon_raw) !== '';
	$lbl_ok  = is_string($label_raw) && trim($label_raw) !== '';
	if ($icon_ok && $lbl_ok) {
		$banner_body_features[] = [
			'icon'  => $icon_raw,
			'label' => $label_raw,
		];
	}
}

$cta_url = '#';
$cta_txt = 'Узнать больше';
if (function_exists('get_field')) {
	$u = get_field('banner_build_body_cta_url', $banner_acf_post_id);
	if (is_string($u) && $u !== '') {
		$cta_url = $u;
	}
	$x = get_field('banner_build_body_cta_text', $banner_acf_post_id);
	if (is_string($x) && $x !== '') {
		$cta_txt = $x;
	}
}
?>

<article class="banner banner-body" data-banner="body">
	<div class="banner-body__wrapper banner__wrapper">
		<div class="banner-body__content">
			<h3 class="banner-body__title">
				<?php echo esc_html($banner_body_title); ?>
			</h3>
			<div class="banner-body__text-block">
				<div class="banner-body__description">
					<?php echo esc_html($banner_body_description); ?>
				</div>
				<div class="banner-body__signature"><?php echo esc_html($banner_body_signature); ?></div>
			</div>
			<?php if (! empty($banner_body_features)) : ?>
				<ul class="banner-body__features" role="list">
					<?php foreach ($banner_body_features as $feat) : ?>
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
			<a href="<?php echo esc_url($cta_url ?: '#'); ?>" class="primary-button banner-body__cta"><?php echo esc_html($cta_txt); ?></a>
		</div>
	</div>
</article>

<?php
if (empty($moveat_banner_partial_only)) {
	get_footer();
}
