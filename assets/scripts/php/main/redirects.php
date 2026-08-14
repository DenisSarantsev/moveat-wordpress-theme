<?php
/*
	Редиректы со старых (удалённых) URL на актуальные страницы.

	Источник — строка пути, а не запись в БД: страниц уже нет, сопоставлять не с чем,
	WP на таком адресе отдал бы 404. Хук template_redirect срабатывает после
	определения 404, но до рендера, поэтому посетитель ошибку не увидит.

	Приоритет 1 — раньше правил 410 (410-rules.php, приоритет по умолчанию 10),
	чтобы удалённый URL, попадающий под мусорный паттерн, уезжал на новую
	страницу, а не отдавал 410.
*/
defined( 'ABSPATH' ) || exit;

/*
	Код ответа. Пока правила обкатываются — 302: браузер кэширует 301 намертво,
	и опечатка в карте будет преследовать вас ещё долго после исправления.
	После проверки на проде поменять на 301.
*/
const MOVEAT_REDIRECT_STATUS = 302;

/*
	Переносить ли query string исходного запроса на цель.

	По умолчанию нет, и это не вкусовщина: 410-rules.php отдаёт 410 по паттернам
	в query (`o=`, `shop`, `products`, `information`), поэтому перенос мусорного
	хвоста со старого URL превратил бы живую целевую страницу в 410.
*/
const MOVEAT_REDIRECT_KEEP_QUERY = false;

/*
	Карта редиректов.

	Ключ — путь источника: без слешей по краям, в нижнем регистре, без query
	       и без префикса локали (префикс отрезается до поиска, см. ниже).
	Цель — одно из:
	         'catalog'                → путь на этом же сайте
	         'https://example.com/…'  → абсолютный URL
	         128                      → ID записи; при подключении Polylang/WPML
	                                    подставится перевод на язык запроса,
	                                    карту править не придётся
*/
function moveat_redirect_rules() {
	return [
		'product-category/consultation' => 'catalog',
		'product/komfort-paket'         => 'product/rashet-raziona',
		'product/rasshirennyj-paket'    => 'product/raschet-ratsiona-s-rassmotreniem-analizov',
		'kursy'                         => 'catalog',
	];
}

/* Путь, по которому установлен WordPress: '' на проде, 'moveat' на локальном MAMP. */
function moveat_redirect_home_path() {
	$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	return strtolower( trim( (string) $path, '/' ) );
}

/*
	Приводит произвольный путь к виду ключа карты: без query и фрагмента,
	без подкаталога установки, без слешей по краям, в нижнем регистре.
*/
function moveat_redirect_normalize_path( $path ) {
	$path = (string) $path;
	$path = explode( '?', $path, 2 )[0];
	$path = explode( '#', $path, 2 )[0];
	$path = strtolower( trim( rawurldecode( $path ), '/' ) );

	// Срезаем подкаталог установки, чтобы одна карта работала и локально, и на проде.
	$home = moveat_redirect_home_path();
	if ( '' !== $home && 0 === strpos( $path . '/', $home . '/' ) ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}

	return $path;
}

/*
	Префиксы локалей в URL. Сейчас плагина мультиязычности нет — вернётся пустой
	массив, и вся логика локали становится no-op. С Polylang/WPML заполнится сам.
*/
function moveat_redirect_locale_slugs() {
	if ( function_exists( 'pll_languages_list' ) ) {
		return (array) pll_languages_list( [ 'fields' => 'slug' ] );
	}

	if ( has_filter( 'wpml_active_languages' ) ) {
		$languages = apply_filters( 'wpml_active_languages', null, [ 'skip_missing' => 0 ] );

		return is_array( $languages ) ? array_keys( $languages ) : [];
	}

	return [];
}

/* Отделяет префикс локали от пути: 'en/kursy' → [ 'en', 'kursy' ]. */
function moveat_redirect_split_locale( $path ) {
	$slugs = moveat_redirect_locale_slugs();

	if ( ! $slugs || '' === $path ) {
		return [ '', $path ];
	}

	$segments = explode( '/', $path );
	$first    = $segments[0];

	if ( ! in_array( $first, array_map( 'strtolower', $slugs ), true ) ) {
		return [ '', $path ];
	}

	array_shift( $segments );

	return [ $first, implode( '/', $segments ) ];
}

/* Отдаёт ID перевода записи на нужный язык; без плагина или без перевода — исходный ID. */
function moveat_redirect_translate_post( $post_id, $locale ) {
	if ( '' === $locale ) {
		return $post_id;
	}

	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $post_id, $locale );

		return $translated ? (int) $translated : $post_id;
	}

	if ( has_filter( 'wpml_object_id' ) ) {
		$translated = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true, $locale );

		return $translated ? (int) $translated : $post_id;
	}

	return $post_id;
}

/*
	Превращает значение карты в URL.

	Строковые цели намеренно НЕ получают префикс локали: у переведённой страницы
	другой слаг, и приклеенный '/en/' дал бы 404. Для мультиязычных целей
	используйте ID записи — он резолвится в перевод автоматически.
*/
function moveat_redirect_resolve_target( $target, $locale ) {
	if ( is_int( $target ) ) {
		$url = get_permalink( moveat_redirect_translate_post( $target, $locale ) );

		return $url ? $url : '';
	}

	$target = (string) $target;

	if ( preg_match( '#^https?://#i', $target ) ) {
		return $target;
	}

	return home_url( '/' . trim( $target, '/' ) . '/' );
}

/* Перехватывает запрос к удалённому URL и уводит на актуальную страницу. */
function moveat_handle_legacy_redirects() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$request_uri  = wp_unslash( $_SERVER['REQUEST_URI'] );
	$request_path = moveat_redirect_normalize_path( $request_uri );

	list( $locale, $path ) = moveat_redirect_split_locale( $request_path );

	$rules = moveat_redirect_rules();
	if ( ! isset( $rules[ $path ] ) ) {
		return;
	}

	$url = moveat_redirect_resolve_target( $rules[ $path ], $locale );
	if ( '' === $url ) {
		return;
	}

	// Защита от петли: цель совпала с текущим запросом.
	if ( moveat_redirect_normalize_path( wp_parse_url( $url, PHP_URL_PATH ) ) === $request_path ) {
		return;
	}

	if ( MOVEAT_REDIRECT_KEEP_QUERY && ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$separator = false === strpos( $url, '?' ) ? '?' : '&';
		$url      .= $separator . wp_unslash( $_SERVER['QUERY_STRING'] );
	}

	wp_safe_redirect( $url, MOVEAT_REDIRECT_STATUS );
	exit;
}
add_action( 'template_redirect', 'moveat_handle_legacy_redirects', 1 );
