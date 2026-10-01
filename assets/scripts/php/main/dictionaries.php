<?php
/**
 * Словари переводов темы.
 *
 * Тексты лежат в assets/dictionaries/<категория>/<раздел>/<язык>.php — каждый
 * файл просто возвращает массив и ничего не выводит. Категория говорит, где
 * эти строки показываются: templates — страницы сайта, emails — письма.
 * Шаблон берёт словарь нужного языка и собирает по нему разметку, поэтому на
 * оба языка хватает одного шаблона.
 *
 * Языки и адреса (/uk/…) ведёт Polylang: он создаёт отдельную запись-перевод
 * страницы, которой назначается тот же шаблон темы. Отсюда словарям нужен
 * только код текущего языка.
 */

/**
 * Языки сайта. Первый — язык по умолчанию: его словарь подставляется, когда
 * перевода ещё нет.
 */
function moveat_langs() {
	return [ 'ru', 'uk' ];
}

/**
 * Приводит код языка к тому, которым названы папки словарей.
 *
 * Украинский в Polylang заводят то со слагом «uk», то «ua», а в локалях он
 * приходит как «uk_UA» — словарь у нас один, поэтому сводим всё к «uk».
 * Незнакомый язык — это язык по умолчанию: пусть лучше покажется русский
 * текст, чем пустая страница.
 */
function moveat_normalize_lang( $lang ) {
	$lang = sanitize_key( substr( (string) $lang, 0, 2 ) );

	if ( 'ua' === $lang ) {
		$lang = 'uk';
	}

	return in_array( $lang, moveat_langs(), true ) ? $lang : 'ru';
}

/**
 * Код текущего языка: 'ru' | 'uk'.
 *
 * Спрашиваем Polylang. Плагин стоит только на проде, поэтому локально
 * (и если его вдруг отключат) остаётся язык по умолчанию — чтобы сайт не
 * падал из-за отсутствующей функции. Параметр ?lang= поддержан тем же
 * фолбэком: он позволяет посмотреть украинскую вёрстку без плагина.
 */
function moveat_current_lang() {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();

		if ( $lang ) {
			return moveat_normalize_lang( $lang );
		}
	}

	$requested = isset( $_GET['lang'] ) ? wp_unslash( $_GET['lang'] ) : '';

	return moveat_normalize_lang( $requested );
}

/**
 * Язык конкретной записи.
 *
 * Письмо собирается на странице результатов и должно уйти на языке этой
 * страницы, а не на том, что Polylang считает текущим в момент запроса, —
 * поэтому язык спрашиваем по её ID. Без плагина остаётся текущий язык.
 */
function moveat_post_lang( $post_id ) {
	if ( function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( (int) $post_id );

		if ( $lang ) {
			return moveat_normalize_lang( $lang );
		}
	}

	return moveat_current_lang();
}

/**
 * Словарь раздела: moveat_dictionary( 'templates/questionnaire' ) отдаёт тексты
 * на языке текущей страницы.
 *
 * Если словаря нужного языка ещё нет, возвращаем язык по умолчанию — страница
 * должна открыться с русским текстом, а не упасть на полпути.
 *
 * @param string      $section Путь внутри assets/dictionaries — категория и
 *                             раздел, например 'emails/questionnaire-results'.
 * @param string|null $lang    Язык; по умолчанию — язык текущей страницы.
 * @return array
 */
function moveat_dictionary( $section, $lang = null ) {
	static $cache = [];

	/*
		Раздел приходит из кода темы, но путь всё равно чистим: из каждого
		сегмента выкидываем всё, кроме букв, цифр, дефиса и подчёркивания —
		так «..» и прочие попытки выйти из папки словарей ничего не дают.
	*/
	$section = implode( '/', array_filter( array_map(
		function ( $part ) {
			return preg_replace( '/[^a-z0-9_-]/', '', $part );
		},
		explode( '/', (string) $section )
	) ) );
	$lang    = null === $lang ? moveat_current_lang() : moveat_normalize_lang( $lang );
	$key     = $section . '/' . $lang;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$dir  = get_template_directory() . '/assets/dictionaries/' . $section . '/';
	$file = $dir . $lang . '.php';

	if ( ! file_exists( $file ) ) {
		$file = $dir . 'ru.php';
	}

	$cache[ $key ] = file_exists( $file ) ? (array) require $file : [];

	return $cache[ $key ];
}

/**
 * Строка из словаря с запасным значением.
 *
 * @param array  $dictionary Словарь целиком (результат moveat_dictionary()).
 * @param string $key        Ключ внутри секции 'strings'.
 * @param string $fallback   Что показать, если строки нет.
 * @return string
 */
function moveat_dictionary_text( $dictionary, $key, $fallback = '' ) {
	return isset( $dictionary['strings'][ $key ] ) && '' !== $dictionary['strings'][ $key ]
		? (string) $dictionary['strings'][ $key ]
		: $fallback;
}
