<?php
/**
 * Український словник листа клієнту «Замовлення виконано».
 *
 * Модуль: assets/scripts/php/woocommerce/emails/email-language.php
 * Вёрстка: woocommerce/emails/customer-completed-order.php
 */

return [
	'strings' => [
		// Тема письма. {order_number} — номер заказа
		'subject'          => 'Ваше замовлення #{order_number} виконано',

		'heading'          => 'Замовлення виконано',
		'order_number'     => 'Номер замовлення:',
		'intro'            => 'Ваше замовлення у Школі здорового харчування Максима Погорілого позначене у нас як «виконане».',

		'messengers_intro' => 'Для зручності перейдімо в чат одного з месенджерів:',
		'messengers_note'  => 'Обирайте зручний для вас месенджер для спілкування. Напишіть у першому повідомленні ваші прізвище та ім’я, а також email.',

		'downloads_title'  => 'Ваші файли для завантаження',

		'qty_unit'         => 'шт',
		'subtotal'         => 'Сума:',
		'discount'         => 'Знижка:',
		'total'            => 'До сплати:',

		'phone'            => 'Телефон:',
		'email'            => 'Email:',

		'contact_before'   => 'Маєте запитання або виникли проблеми з оплатою замовлення? Пишіть нам на пошту',
		'contact_after'    => 'або в месенджери.',
		'footer'           => 'Moveat Expert',
	],

	// Ссылки на мессенджеры в письме
	'messengers' => [
		'telegram' => 'https://t.me/MoveatExpertUaBot',
	],
];
