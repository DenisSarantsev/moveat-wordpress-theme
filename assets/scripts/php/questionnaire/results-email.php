<?php
/*
	Письмо с результатами опросника.

	Человек проходит опросник и попадает на страницу результатов по ссылке с
	баллами, почтой и идентификатором прохождения (qid). При заходе на эту
	страницу отправляем ему письмо с теми же выводами, что показаны на сайте.

	Тексты берём из тех же ACF-полей, что выводит шаблон страницы, — правка
	полей в админке меняет и страницу, и письмо.

	К WooCommerce модуль отношения не имеет, поэтому подключается безусловно и
	не использует wc_get_template.
*/

defined( 'ABSPATH' ) || exit;

/** Шаблон, на котором живёт страница результатов */
const MOVEAT_QUESTIONNAIRE_RESULTS_TEMPLATE = 'templates/questionnaire-results-with-modal.php';

/** Ширина цветной полосы шкалы в письме, px */
const MOVEAT_QUESTIONNAIRE_EMAIL_BAR_WIDTH = 520;

/** Отправитель письма */
const MOVEAT_QUESTIONNAIRE_EMAIL_FROM = 'Moveat <moveat.expert@gmail.com>';

/*
	Тема письма на самый крайний случай: обычно она приходит из словаря
	(assets/dictionaries/emails/questionnaire-results), а поверх него — из
	ACF-поля quest_email_subject страницы.
*/
const MOVEAT_QUESTIONNAIRE_EMAIL_SUBJECT = 'Ваши результаты по 8 индикаторам здоровья';

/**
 * Префикс записи об обработанном прохождении.
 *
 * Каждый идентификатор — отдельная опция с autoload = no: у wp_options есть
 * уникальный индекс по option_name, поэтому MySQL находит запись по дереву,
 * не читая остальные. Держать все идентификаторы одним массивом и искать в нём
 * было бы медленнее: массив пришлось бы целиком читать и разбирать на каждом
 * заходе, а так на запрос поднимается ровно одна строка.
 *
 * Срока жизни нет — записи весят десятки байт, а повторное письмо человеку
 * неприятнее, чем лишние строки в таблице.
 */
const MOVEAT_QUESTIONNAIRE_QID_PREFIX = 'moveat_quest_qid_';

/*
	Блоки письма в том же порядке, в каком они идут на странице.

	param   — имя GET-параметра с баллом (номер варианта текста лежит в
	          параметре с суффиксом _text);
	prefix  — префикс ACF-полей блока;
	max     — верх шкалы; null у общих выводов, там шкалы нет.

	Заголовок подставляется из словаря письма по префиксу без дефиса на конце
	(metabolic-disorder- → metabolic-disorder), поэтому здесь его нет. Словарь
	передаётся аргументом: там, где нужны только номера параметров, его можно
	не передавать.
*/
function moveat_questionnaire_email_blocks( $dictionary = null ) {
	$titles = isset( $dictionary['blocks'] ) ? (array) $dictionary['blocks'] : array();

	$blocks = array(
		array(
			'param'  => 'average_score',
			'prefix' => 'general-conclusions-',
			'max'    => null,
		),
		array(
			'param'  => 'metab_syndrome',
			'prefix' => 'metabolic-disorder-',
			'max'    => 6.1,
		),
		array(
			'param'  => 'inflamation',
			'prefix' => 'systemic-inflammation-',
			'max'    => 6.5,
		),
		array(
			'param'  => 'acidification',
			'prefix' => 'acidification-level-',
			'max'    => 3.4,
		),
		array(
			'param'  => 'glycemic_level',
			'prefix' => 'glycemic-level-',
			'max'    => 5.2,
		),
		array(
			'param'  => 'risk_of_aging',
			'prefix' => 'accelerated-aging-',
			'max'    => 6,
		),
		array(
			'param'  => 'cleanability',
			'prefix' => 'self-cleaning-',
			'max'    => 5.1,
		),
		array(
			'param'  => 'cancer_risk',
			'prefix' => 'cancer-risk-',
			'max'    => 5.6,
		),
		array(
			'param'  => 'empty_calories',
			'prefix' => 'quantity-calories-',
			'max'    => 4.8,
		),
	);

	foreach ( $blocks as &$block ) {
		$key            = rtrim( $block['prefix'], '-' );
		$block['title'] = isset( $titles[ $key ] ) ? (string) $titles[ $key ] : '';
	}
	unset( $block );

	return $blocks;
}

/*
	Разбирает и проверяет параметры адреса.

	Возвращает массив с почтой, идентификатором прохождения и баллами либо
	null, если чего-то не хватает. Работаем по принципу «всё или ничего»:
	половинчатое письмо с прочерками хуже, чем его отсутствие.
*/
function moveat_questionnaire_email_payload() {
	$email = isset( $_GET['email'] )
		? sanitize_email( wp_unslash( $_GET['email'] ) )
		: '';

	if ( ! is_email( $email ) ) {
		return null;
	}

	$qid = isset( $_GET['qid'] ) ? wp_unslash( $_GET['qid'] ) : '';
	if ( ! preg_match( '/^\d{10}$/', $qid ) ) {
		return null;
	}

	$scores = array();

	foreach ( moveat_questionnaire_email_blocks() as $block ) {
		$param      = $block['param'];
		$text_param = $param . '_text';

		if ( ! isset( $_GET[ $param ], $_GET[ $text_param ] ) ) {
			return null;
		}

		$raw_score   = wp_unslash( $_GET[ $param ] );
		$raw_variant = wp_unslash( $_GET[ $text_param ] );

		if ( ! preg_match( '/^\d+$/', (string) $raw_score ) ) {
			return null;
		}

		if ( ! in_array( (string) $raw_variant, array( '1', '2', '3' ), true ) ) {
			return null;
		}

		$scores[ $param ] = array(
			'score'   => absint( $raw_score ),
			'variant' => absint( $raw_variant ),
		);
	}

	return array(
		'email'  => $email,
		'qid'    => $qid,
		'scores' => $scores,
	);
}

/*
	Готовит один блок письма: описание, нужный вариант текста, ссылки и
	положение отметки на шкале.

	Балл приходит умноженным на десять — делим обратно, как это делает скрипт
	страницы результатов, и так же ограничиваем его верхом шкалы.
*/
function moveat_questionnaire_email_build_block( $post_id, $block, $score, $variant ) {
	$prefix = $block['prefix'];
	$max    = $block['max'];

	// Тексты заведены как wysiwyg: ACF отдаёт готовую разметку,
	// поэтому в письме её только чистим, не прогоняя через the_content —
	// ровно так же, как выводит страница результатов.
	$text = get_field( $prefix . 'text' . $variant, $post_id );

	// А описание страница пропускает через the_content — повторяем.
	$description = trim( (string) get_field( $prefix . 'description', $post_id ) );
	if ( '' !== $description ) {
		$description = apply_filters( 'the_content', $description );
	}

	$links = array();
	for ( $i = 1; $i <= 5; $i++ ) {
		$url   = get_field( $prefix . 'link-' . $variant . '_' . $i, $post_id );
		$label = get_field( $prefix . 'text-' . $variant . '_' . $i, $post_id );

		if ( ! empty( $url ) && ! empty( $label ) ) {
			$links[] = array(
				'url'   => $url,
				'label' => $label,
			);
		}
	}

	$scale = null;
	if ( null !== $max ) {
		$actual  = $score / 10;
		$percent = min( 100, (int) round( ( $actual / $max ) * 100 ) );
		$value   = $score > $max * 10 ? $max : $actual;

		$scale = array(
			'percent' => $percent,
			'value'   => moveat_questionnaire_email_format_number( $value ),
			'max'     => moveat_questionnaire_email_format_number( $max ),
		);
	}

	return array(
		'title'       => $block['title'],
		'description' => $description,
		'text'        => $text,
		'links'       => $links,
		'scale'       => $scale,
	);
}

/*
	Число для подписи шкалы: целые показываем без дробной части,
	остальные — с одним знаком, как на странице.
*/
function moveat_questionnaire_email_format_number( $value ) {
	$value = round( (float) $value, 1 );

	return floor( $value ) === $value
		? (string) (int) $value
		: number_format( $value, 1, ',', '' );
}

/*
	Собирает все блоки письма. Возвращает null, если на странице не заполнен
	ни один текст — отправлять пустое письмо незачем.
*/
function moveat_questionnaire_email_build_data( $post_id, $payload, $dictionary = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$blocks   = array();
	$has_text = false;

	foreach ( moveat_questionnaire_email_blocks( $dictionary ) as $block ) {
		$entry = $payload['scores'][ $block['param'] ];

		$built = moveat_questionnaire_email_build_block(
			$post_id,
			$block,
			$entry['score'],
			$entry['variant']
		);

		if ( '' !== trim( wp_strip_all_tags( (string) $built['text'] ) ) ) {
			$has_text = true;
		}

		$blocks[] = $built;
	}

	return $has_text ? $blocks : null;
}

/*
	Тема письма. Сначала смотрим ACF-поле страницы — так тему можно менять из
	админки, и у каждой языковой версии она своя. Если поле пустое, берём
	строку из словаря письма, а уже за ней — константу выше.
*/
function moveat_questionnaire_email_subject( $post_id, $dictionary = null ) {
	if ( function_exists( 'get_field' ) ) {
		$subject = trim( (string) get_field( 'quest_email_subject', $post_id ) );
		if ( '' !== $subject ) {
			return $subject;
		}
	}

	return moveat_dictionary_text(
		(array) $dictionary,
		'subject',
		MOVEAT_QUESTIONNAIRE_EMAIL_SUBJECT
	);
}

/*
	Рендерит HTML письма. Шаблон презентационный: получает готовые блоки и
	ничего не знает ни про ACF, ни про параметры адреса.
*/
function moveat_questionnaire_email_render( $blocks, $post_id, $dictionary = null ) {
	$template = get_template_directory() . '/template-parts/emails/questionnaire-results.php';
	if ( ! file_exists( $template ) ) {
		return '';
	}

	// Переменные видны внутри include — на них и рассчитан шаблон
	$email_blocks   = $blocks;
	$email_page_id  = $post_id;
	$email_bar      = MOVEAT_QUESTIONNAIRE_EMAIL_BAR_WIDTH;
	$email_strings  = isset( $dictionary['strings'] ) ? (array) $dictionary['strings'] : array();

	ob_start();
	include $template;

	return (string) ob_get_clean();
}

/*
	Короткий лог модуля: отправлено, пропущено или не ушло.

	Отдельный файл, потому что в общий лог PHP на хостинге попасть сложнее.
	Папку закрываем от посторонних так же, как это сделано для лога Tallanto.
*/
function moveat_questionnaire_email_log( $message ) {
	$dir = get_template_directory() . '/assets/logs';

	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		@file_put_contents( $dir . '/.htaccess', "Require all denied\n" );
		@file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden\n" );
	}

	$file = $dir . '/questionnaire-email.log';

	// Простая ротация, чтобы файл не рос бесконечно
	if ( file_exists( $file ) && filesize( $file ) > 2 * MB_IN_BYTES ) {
		@rename( $file, $file . '.old' );
	}

	@error_log(
		sprintf( "[%s] %s\n", current_time( 'mysql' ), $message ),
		3,
		$file
	);
}

/*
	Что уже сделано по этому прохождению: массив вида
	array( 'email' => '2026-09-11 12:00:00', 'crm' => ... ) или пустой массив.
*/
function moveat_questionnaire_qid_state( $qid ) {
	$state = get_option( MOVEAT_QUESTIONNAIRE_QID_PREFIX . $qid, array() );

	return is_array( $state ) ? $state : array();
}

/*
	Отмечает выполненный шаг по этому прохождению.

	Шаги пишем по отдельности: если письмо не ушло из-за сбоя почты, отправка в
	CRM всё равно останется отмеченной и не продублируется при следующем заходе.
*/
function moveat_questionnaire_qid_mark( $qid, $step ) {
	$key   = MOVEAT_QUESTIONNAIRE_QID_PREFIX . $qid;
	$state = moveat_questionnaire_qid_state( $qid );

	$state[ $step ] = current_time( 'mysql' );

	// autoload = no: записей со временем станет много, и грузить их
	// в память на каждом запросе незачем.
	update_option( $key, $state, false );
}

/*
	Адрес страницы результатов вместе с параметрами — его кладём в карточку CRM,
	чтобы результаты можно было открыть прямо оттуда.
*/
function moveat_questionnaire_results_url() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}

	return esc_url_raw( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
}

/*
	Отправляет результаты на почту при заходе на страницу результатов.

	Каждая проверка — тихий выход: если параметров нет или они битые, страница
	просто рендерится как обычно.
*/
function moveat_maybe_send_questionnaire_results_email() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( ! is_page_template( MOVEAT_QUESTIONNAIRE_RESULTS_TEMPLATE ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$payload = moveat_questionnaire_email_payload();
	if ( ! $payload ) {
		return;
	}

	$qid   = $payload['qid'];
	$state = moveat_questionnaire_qid_state( $qid );

	// Предпросмотр для администратора: показывает письмо прямо в браузере,
	// ничего не отправляя и не отмечая прохождение как обработанное.
	$is_preview = isset( $_GET['preview_email'] ) && current_user_can( 'manage_options' );

	$needs_email = $is_preview || empty( $state['email'] );
	$needs_crm   = ! $is_preview && empty( $state['crm'] );

	if ( ! $needs_email && ! $needs_crm ) {
		return;
	}

	// Карточку в CRM заводим независимо от письма: даже если почта не уйдёт,
	// контакт в CRM останется, и наоборот.
	if ( $needs_crm && function_exists( '\\Moveat\\Tallanto\\Questionnaire\\send_result' ) ) {
		$crm_sent = \Moveat\Tallanto\Questionnaire\send_result(
			$payload['email'],
			moveat_questionnaire_results_url()
		);

		if ( $crm_sent ) {
			moveat_questionnaire_qid_mark( $qid, 'crm' );
			moveat_questionnaire_email_log( sprintf( 'qid %s: результат передан в Tallanto', $qid ) );
		} else {
			// Причину уже записал клиент Tallanto в свой лог
			moveat_questionnaire_email_log( sprintf( 'qid %s: Tallanto не принял результат', $qid ) );
		}
	}

	if ( ! $needs_email ) {
		return;
	}

	/*
		Язык письма — язык той страницы, на которую пришёл человек: с /uk/
		уходит украинское письмо. Берём его по ID страницы, а не по текущему
		языку запроса, чтобы письмо не зависело от того, что Polylang считает
		текущим в этот момент.
	*/
	$dictionary = moveat_dictionary( 'emails/questionnaire-results', moveat_post_lang( $post_id ) );

	$blocks = moveat_questionnaire_email_build_data( $post_id, $payload, $dictionary );
	if ( ! $blocks ) {
		moveat_questionnaire_email_log(
			sprintf( 'qid %s: тексты на странице %d не заполнены, письмо не отправлено', $qid, $post_id )
		);
		return;
	}

	$html = moveat_questionnaire_email_render( $blocks, $post_id, $dictionary );
	if ( '' === $html ) {
		moveat_questionnaire_email_log( 'шаблон письма не найден' );
		return;
	}

	if ( $is_preview ) {
		header( 'Content-Type: text/html; charset=utf-8' );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . MOVEAT_QUESTIONNAIRE_EMAIL_FROM,
	);

	$sent = wp_mail(
		$payload['email'],
		moveat_questionnaire_email_subject( $post_id, $dictionary ),
		$html,
		$headers
	);

	if ( $sent ) {
		// Отмечаем шаг только после удачной отправки: иначе сбой транспорта
		// навсегда лишил бы человека письма.
		moveat_questionnaire_qid_mark( $qid, 'email' );
		moveat_questionnaire_email_log( sprintf( 'qid %s: письмо отправлено', $qid ) );
	} else {
		moveat_questionnaire_email_log( sprintf( 'qid %s: wp_mail вернул false', $qid ) );
	}
}
add_action( 'template_redirect', 'moveat_maybe_send_questionnaire_results_email' );

/*
	Причина отказа почтового транспорта — чтобы не гадать, почему письма не идут.
*/
add_action(
	'wp_mail_failed',
	function ( $error ) {
		if ( is_wp_error( $error ) ) {
			moveat_questionnaire_email_log( 'ошибка отправки: ' . $error->get_error_message() );
		}
	}
);
