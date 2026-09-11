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

/** Тема письма по умолчанию — перекрывается ACF-полем quest_email_subject */
const MOVEAT_QUESTIONNAIRE_EMAIL_SUBJECT = 'Ваши результаты по 8 индикаторам здоровья';

/** Сколько храним идентификаторы прохождений, чтобы не слать письмо дважды */
const MOVEAT_QUESTIONNAIRE_EMAIL_QID_TTL = YEAR_IN_SECONDS;

/*
	Блоки письма в том же порядке, в каком они идут на странице.

	param   — имя GET-параметра с баллом (номер варианта текста лежит в
	          параметре с суффиксом _text);
	prefix  — префикс ACF-полей блока;
	title   — заголовок блока (на странице он захардкожен в разметке);
	max     — верх шкалы; null у общих выводов, там шкалы нет.
*/
function moveat_questionnaire_email_blocks() {
	return array(
		array(
			'param'  => 'average_score',
			'prefix' => 'general-conclusions-',
			'title'  => 'Общие выводы',
			'max'    => null,
		),
		array(
			'param'  => 'metab_syndrome',
			'prefix' => 'metabolic-disorder-',
			'title'  => 'Риск метаболического расстройства',
			'max'    => 6.1,
		),
		array(
			'param'  => 'inflamation',
			'prefix' => 'systemic-inflammation-',
			'title'  => 'Системное воспаление',
			'max'    => 6.5,
		),
		array(
			'param'  => 'acidification',
			'prefix' => 'acidification-level-',
			'title'  => 'Уровень закисления',
			'max'    => 3.4,
		),
		array(
			'param'  => 'glycemic_level',
			'prefix' => 'glycemic-level-',
			'title'  => 'Гликемичность рациона',
			'max'    => 5.2,
		),
		array(
			'param'  => 'risk_of_aging',
			'prefix' => 'accelerated-aging-',
			'title'  => 'Риск ускоренного старения',
			'max'    => 6,
		),
		array(
			'param'  => 'cleanability',
			'prefix' => 'self-cleaning-',
			'title'  => 'Нарушение способности организма к самоочищению',
			'max'    => 5.1,
		),
		array(
			'param'  => 'cancer_risk',
			'prefix' => 'cancer-risk-',
			'title'  => 'Риск раковых заболеваний',
			'max'    => 5.6,
		),
		array(
			'param'  => 'empty_calories',
			'prefix' => 'quantity-calories-',
			'title'  => 'Количество пустых калорий в пище',
			'max'    => 4.8,
		),
	);
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
function moveat_questionnaire_email_build_data( $post_id, $payload ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$blocks   = array();
	$has_text = false;

	foreach ( moveat_questionnaire_email_blocks() as $block ) {
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
	Тема письма. Берём из ACF-поля страницы, если оно заведено, иначе —
	значение по умолчанию: так тему можно будет менять из админки, не трогая код.
*/
function moveat_questionnaire_email_subject( $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		$subject = trim( (string) get_field( 'quest_email_subject', $post_id ) );
		if ( '' !== $subject ) {
			return $subject;
		}
	}

	return MOVEAT_QUESTIONNAIRE_EMAIL_SUBJECT;
}

/*
	Рендерит HTML письма. Шаблон презентационный: получает готовые блоки и
	ничего не знает ни про ACF, ни про параметры адреса.
*/
function moveat_questionnaire_email_render( $blocks, $post_id ) {
	$template = get_template_directory() . '/template-parts/emails/questionnaire-results.php';
	if ( ! file_exists( $template ) ) {
		return '';
	}

	// Переменные видны внутри include — на них и рассчитан шаблон
	$email_blocks   = $blocks;
	$email_page_id  = $post_id;
	$email_bar      = MOVEAT_QUESTIONNAIRE_EMAIL_BAR_WIDTH;

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

	$transient_key = 'moveat_quest_email_' . $payload['qid'];

	// Предпросмотр для администратора: показывает письмо прямо в браузере,
	// ничего не отправляя и не отмечая прохождение как обработанное.
	$is_preview = isset( $_GET['preview_email'] ) && current_user_can( 'manage_options' );

	if ( ! $is_preview && false !== get_transient( $transient_key ) ) {
		return;
	}

	$blocks = moveat_questionnaire_email_build_data( $post_id, $payload );
	if ( ! $blocks ) {
		moveat_questionnaire_email_log(
			sprintf( 'qid %s: тексты на странице %d не заполнены, письмо не отправлено', $payload['qid'], $post_id )
		);
		return;
	}

	$html = moveat_questionnaire_email_render( $blocks, $post_id );
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
		moveat_questionnaire_email_subject( $post_id ),
		$html,
		$headers
	);

	if ( $sent ) {
		// Отмечаем прохождение только после удачной отправки: иначе сбой
		// транспорта навсегда лишил бы человека письма.
		set_transient( $transient_key, time(), MOVEAT_QUESTIONNAIRE_EMAIL_QID_TTL );
		moveat_questionnaire_email_log( sprintf( 'qid %s: письмо отправлено', $payload['qid'] ) );
	} else {
		moveat_questionnaire_email_log( sprintf( 'qid %s: wp_mail вернул false', $payload['qid'] ) );
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
