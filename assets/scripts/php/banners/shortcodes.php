<?php

if (! function_exists('moveat_banner_render_partial')) {
	/**
	 * Подключает файл баннера как частичный шаблон (без header/footer) или как полную страницу.
	 *
	 * @param string $relative Путь относительно темы, например templates/banners/banner-diet.php
	 * @param int    $page_id  ID страницы с этим шаблоном (permalink и т.д.).
	 */
	function moveat_banner_render_partial($relative, $page_id = 0) {
		$path = locate_template($relative);
		if (! $path) {
			return '';
		}
		$banner_page_id             = (int) $page_id;
		$moveat_banner_partial_only = true;
		ob_start();
		include $path;
		return ob_get_clean();
	}
}

// ID страниц (Страницы → нужная страница → в адресной строке post=123): после назначения
// соответствующего шаблона баннера подставьте числовой ID вместо 0 в переменных ниже.

/** Шаблон «Баннер — Диета» → ID страницы. */
$moveat_banner_diet_page_id = 3467;

/** Шаблон «Баннер — Книги» → ID страницы. */
$moveat_banner_books_page_id = 3461;

/** Шаблон «Баннер — Тело» → ID страницы. */
$moveat_banner_build_body_page_id = 3459;

/** Шаблон «Баннер — Вопросы» → ID страницы. */
$moveat_banner_questions_page_id = 3463;

/** Шаблон «Баннер — Клуб» → ID страницы. */
$moveat_banner_club_page_id = 3465;

add_shortcode(
	'moveat_banner_diet',
	function () {
		global $moveat_banner_diet_page_id;
		return moveat_banner_render_partial('templates/banners/banner-diet.php', $moveat_banner_diet_page_id);
	}
);

add_shortcode(
	'moveat_banner_books',
	function () {
		global $moveat_banner_books_page_id;
		return moveat_banner_render_partial('templates/banners/banner-books.php', $moveat_banner_books_page_id);
	}
);

add_shortcode(
	'moveat_banner_build_body',
	function () {
		global $moveat_banner_build_body_page_id;
		return moveat_banner_render_partial('templates/banners/banner-build-body.php', $moveat_banner_build_body_page_id);
	}
);

add_shortcode(
	'moveat_banner_questions',
	function () {
		global $moveat_banner_questions_page_id;
		return moveat_banner_render_partial('templates/banners/banner-questions.php', $moveat_banner_questions_page_id);
	}
);

add_shortcode(
	'moveat_banner_club',
	function () {
		global $moveat_banner_club_page_id;
		return moveat_banner_render_partial('templates/banners/banner-club.php', $moveat_banner_club_page_id);
	}
);
