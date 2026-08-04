<?php
/**
 * Tallanto CRM — синхронизация с WooCommerce.
 *
 * Что делает:
 * 1. При создании заказа (woocommerce_new_order) заводит/обновляет карточку клиента
 *    (сущность Contact, модуль «Ученики») в Tallanto: имя, фамилия, тел., e-mail,
 *    ответственный, источник, «записан самостоятельно», тип ученика.
 * 2. При переходе заказа в статус «Выполнен» (woocommerce_order_status_completed)
 *    ДОЗАПИСЫВАЕТ в поле «Дополнительная информация» (description) детали заказа:
 *    дата, номер, наименование товара(ов), сумма — под уже имеющимся текстом, через пробел.
 *
 * Обычные заказы и донаты обрабатываются одинаково (оба — WC_Order).
 * Ядро отправки/подписи/лога — в client.php (namespace Moveat\Tallanto).
 */

namespace Moveat\Tallanto\Woo;

use function Moveat\Tallanto\send;

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Конфиг (системные имена полей подтверждены по
 * ?entryPoint=infoDataCapture&module=Contact)
 * ---------------------------------------------------------------------- */

const MODULE_CONTACT = 'Contact';

/**
 * Ответственный пользователь (поле assigned_user_name — поиск по ФИО в таблице users).
 * TODO: убедиться, что в Tallanto существует пользователь с таким именем.
 */
const ASSIGNED_USER_NAME = 'Cайт Moveat.expert';

// Источник (поле source — комбобокс; 'moveat.expert' входит в список допустимых значений).
const SOURCE_VALUE = 'moveat.expert';

// «Записан самостоятельно» (поле write_yourself — булево 0/1).
const SELF_REGISTERED_VALUE = 1;

/**
 * Тип ученика (поле type_client_c — ОБЯЗАТЕЛЬНОЕ, комбобокс).
 * Допустимые значения: «Есть интерес», «Согрев», «Первая покупка»,
 * «Постоянный клиент», «Нет интереса», «Цепочка емейл согрева», «Партнер».
 * По умолчанию для покупателя с сайта — «Первая покупка».
 */
const TYPE_CLIENT_VALUE = 'Первая покупка';

// Заглушка для обязательного first_name, если у клиента нет имени.
const FALLBACK_FIRST_NAME = 'Клиент с сайта';

// Мета-ключи заказа (служебные).
const META_CONTACT_ID = '_tallanto_contact_id';
const META_SYNCED     = '_tallanto_synced';
const META_APPENDED   = '_tallanto_appended';

/* -------------------------------------------------------------------------
 * Сборка данных из заказа
 * ---------------------------------------------------------------------- */

/**
 * Базовые поля карточки клиента из заказа.
 *
 * @param \WC_Order $order
 * @return array
 */
function build_base_contact_params( $order ) {
	$first_name = trim( (string) $order->get_billing_first_name() );

	$params = array(
		'first_name'         => '' !== $first_name ? $first_name : FALLBACK_FIRST_NAME,
		'last_name'          => trim( (string) $order->get_billing_last_name() ),
		'phone_mobile'       => trim( (string) $order->get_billing_phone() ),
		'email1'             => trim( (string) $order->get_billing_email() ),
		'assigned_user_name' => ASSIGNED_USER_NAME,
		'source'             => SOURCE_VALUE,
		'write_yourself'     => SELF_REGISTERED_VALUE,
		'type_client_c'      => TYPE_CLIENT_VALUE,
		// Антидубли: по email проверка идёт всегда, дополнительно по телефону.
		'check_duplicate_by' => array( 'phone' ),
	);

	return $params;
}

/**
 * Строка деталей заказа для дозаписи в «Дополнительную информацию».
 * Ведущий пробел: дозапись идёт под последним текстом через пробел.
 *
 * @param \WC_Order $order
 * @return string
 */
function format_order_details( $order ) {
	$date_obj = $order->get_date_paid() ?: $order->get_date_created();
	$date     = $date_obj ? $date_obj->date_i18n( 'd.m.Y' ) : '';

	$items = array();
	foreach ( $order->get_items() as $item ) {
		$items[] = $item->get_name();
	}

	$total = html_entity_decode( wp_strip_all_tags( wc_price(
		$order->get_total(),
		array( 'currency' => $order->get_currency() )
	) ) );

	return sprintf(
		' Заказ №%s от %s: %s — %s.',
		$order->get_order_number(),
		$date,
		implode( ', ', $items ),
		$total
	);
}

/* -------------------------------------------------------------------------
 * Хуки
 * ---------------------------------------------------------------------- */

/**
 * Создание заказа → заводим/обновляем карточку клиента в CRM.
 * Срабатывает и для обычных заказов, и для донатов.
 */
add_action( 'woocommerce_new_order', __NAMESPACE__ . '\\on_new_order', 10, 1 );

function on_new_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Защита от повторной отправки.
	if ( $order->get_meta( META_SYNCED ) ) {
		return;
	}

	$result = send( MODULE_CONTACT, build_base_contact_params( $order ) );

	if ( ! $result || empty( $result['result'] ) ) {
		// Ошибку уже залогировали; synced не ставим — можно повторить позже.
		return;
	}

	if ( ! empty( $result['id'] ) ) {
		$order->update_meta_data( META_CONTACT_ID, sanitize_text_field( (string) $result['id'] ) );
	}
	$order->update_meta_data( META_SYNCED, 1 );
	$order->save();
}

/**
 * Заказ «Выполнен» → дозаписываем детали в «Дополнительную информацию».
 */
add_action( 'woocommerce_order_status_completed', __NAMESPACE__ . '\\on_order_completed', 10, 1 );

function on_order_completed( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Защита от задвоения при повторном переходе в «Выполнен».
	if ( $order->get_meta( META_APPENDED ) ) {
		return;
	}

	$params = array(
		'description'           => format_order_details( $order ),
		'update_duplicate_info' => array( 'description' => 'add' ),
	);

	$contact_id = $order->get_meta( META_CONTACT_ID );

	if ( $contact_id ) {
		// Адресная дозапись по сохранённому ID карточки.
		$params['get_by_id'] = $contact_id;
	} else {
		// Фолбэк: находим карточку по контактам заказа (email — всегда, плюс телефон).
		$params['first_name']         = $order->get_billing_first_name() ?: FALLBACK_FIRST_NAME;
		$params['email1']             = $order->get_billing_email();
		$params['phone_mobile']       = $order->get_billing_phone();
		$params['check_duplicate_by'] = array( 'phone' );
	}

	$result = send( MODULE_CONTACT, $params );

	if ( ! $result || empty( $result['result'] ) ) {
		return;
	}

	$order->update_meta_data( META_APPENDED, 1 );
	$order->save();
}
