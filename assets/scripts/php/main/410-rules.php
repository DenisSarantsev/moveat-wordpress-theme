<?php
/*
	Правила для отдачи 410 ошибки для мусорных URL
*/

add_action('template_redirect', function () {

	$uri = $_SERVER['REQUEST_URI'];
	$query = urldecode($_SERVER['QUERY_STRING']);

	// паттерны для URL (путь)
	$uri_patterns = [
		'/goods/',
		'/goods'
	];

	// паттерны для query string (все что начинается с ?)
	$query_patterns = [
		'jp/',
		'shop',
		'information',
		'products',
		'o=',
	];

	// проверка URI
	foreach ($uri_patterns as $pattern) {
		if (strpos($uri, $pattern) !== false) {
			status_header(410);
			nocache_headers();
			include get_template_directory() . '/templates/410.php';
			exit;
		}
	}

	// проверка QUERY STRING
	foreach ($query_patterns as $pattern) {
		if (strpos($query, $pattern) !== false) {
			status_header(410);
			nocache_headers();
			include get_template_directory() . '/templates/410.php';
			exit;
		}
	}
});
