<?php
/*
	Template Name: Баннер — Книги
	Template Post Type: page
*/

if (empty($moveat_banner_partial_only)) {
	get_header();
}

// При выводе через шорткод доступна переменная $banner_page_id.
$banner_acf_post_id = ! empty($banner_page_id) ? (int) $banner_page_id : get_the_ID();

$default_title    = 'Книги о здоровом питании от Макса Погорелого';
$default_subtitle = 'Ценные знаний о питании и здоровье - в простой и понятной форме. Красиво оформлено, удобно читать прямо с телефона!';

$banner_books_title = $default_title;
if (function_exists('get_field')) {
	$t = get_field('banner_books_title', $banner_acf_post_id);
	if (is_string($t) && $t !== '') {
		$banner_books_title = $t;
	}
}

$banner_books_subtitle = $default_subtitle;
if (function_exists('get_field')) {
	$s = get_field('banner_books_subtitle', $banner_acf_post_id);
	if (is_string($s) && $s !== '') {
		$banner_books_subtitle = $s;
	}
}

$cta_url = '#';
$cta_txt = 'Перейти на книжную полку';
if (function_exists('get_field')) {
	$u = get_field('banner_books_cta_url', $banner_acf_post_id);
	if (is_string($u) && $u !== '') {
		$cta_url = $u;
	}
	$x = get_field('banner_books_cta_text', $banner_acf_post_id);
	if (is_string($x) && $x !== '') {
		$cta_txt = $x;
	}
}
?>

<article class="banner banner-books" data-banner="books">
	<div class="banner-books__wrapper banner__wrapper">
		<div class="banner-books__content">
			<h3 class="banner-books__title">
				<?php echo esc_html($banner_books_title); ?>
			</h3>
			<div class="banner-books__subtitle">
				<?php echo esc_html($banner_books_subtitle); ?>
			</div>
			<a href="<?php echo esc_url($cta_url ?: '#'); ?>" class="primary-button banner-books__cta"><?php echo esc_html($cta_txt); ?></a>
		</div>
	</div>
</article>

<?php
if (empty($moveat_banner_partial_only)) {
	get_footer();
}
