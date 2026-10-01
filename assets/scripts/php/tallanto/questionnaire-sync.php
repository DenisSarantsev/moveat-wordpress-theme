<?php
/**
 * Tallanto CRM — передача результатов опросника.
 *
 * Когда человек доходит до страницы результатов, заводим в CRM карточку
 * (сущность Contact, модуль «Ученики») с его почтой, а в «Дополнительную
 * информацию» дописываем дату прохождения и ссылку на результаты.
 *
 * Вызывается из assets/scripts/php/questionnaire/results-email.php — там же,
 * где уходит письмо, и с той же защитой от повторов по идентификатору
 * прохождения.
 *
 * Ядро отправки, подписи и лога — в client.php (namespace Moveat\Tallanto),
 * поля карточки — те же, что у синхронизации заказов (woocommerce-sync.php).
 */

namespace Moveat\Tallanto\Questionnaire;

use function Moveat\Tallanto\send;

defined( 'ABSPATH' ) || exit;

const MODULE_CONTACT = 'Contact';

/** Имя карточки: человек оставил только почту, поэтому вместо ФИО — что он прошёл */
const CONTACT_NAME = 'Тест качество питания';

/** Ответственный пользователь — как и для заказов с сайта */
const ASSIGNED_USER_NAME = 'Cайт Moveat.expert';

/** Источник (комбобокс на стороне CRM) */
const SOURCE_VALUE = 'moveat.expert';

/** «Записан самостоятельно» */
const SELF_REGISTERED_VALUE = 1;

/**
 * Тип ученика — обязательное поле-комбобокс.
 * Прохождение теста это ещё не покупка, поэтому «Есть интерес».
 */
const TYPE_CLIENT_VALUE = 'Есть интерес';

/**
 * Строка для «Дополнительной информации».
 *
 * Ведущий пробел — дозапись идёт под уже имеющимся текстом через пробел,
 * так же как это сделано для деталей заказа.
 *
 * @param string $results_url Ссылка на страницу результатов с параметрами.
 * @return string
 */
function format_details( $results_url ) {
	$date = current_time( 'd.m.Y H:i' );

	if ( '' === $results_url ) {
		return sprintf( ' Прошёл тест «Качество питания» %s.', $date );
	}

	return sprintf(
		' Прошёл тест «Качество питания» %s. Результаты: %s',
		$date,
		$results_url
	);
}

/**
 * Отправляет результат прохождения в CRM.
 *
 * Если карточка с такой почтой уже есть, Tallanto найдёт её по антидублю, а
 * update_duplicate_info допишет строку в «Дополнительную информацию», не
 * затирая то, что там уже было.
 *
 * @param string $email       Почта, которую человек указал в опроснике.
 * @param string $results_url Ссылка на его результаты.
 * @return bool Удалось ли отправить.
 */
function send_result( $email, $results_url = '' ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return false;
	}

	$params = array(
		'first_name'            => CONTACT_NAME,
		'email1'                => $email,
		'assigned_user_name'    => ASSIGNED_USER_NAME,
		'source'                => SOURCE_VALUE,
		'write_yourself'        => SELF_REGISTERED_VALUE,
		'type_client_c'         => TYPE_CLIENT_VALUE,
		'description'           => format_details( $results_url ),
		// Дубли ищем по почте (она проверяется всегда) — телефона у нас нет.
		'update_duplicate_info' => array( 'description' => 'add' ),
	);

	$result = send( MODULE_CONTACT, $params );

	return (bool) ( $result && ! empty( $result['result'] ) );
}
