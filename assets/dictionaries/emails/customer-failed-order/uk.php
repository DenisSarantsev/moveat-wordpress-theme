<?php
/**
 * Український словник листа клієнту «Помилка оплати».
 *
 * Модуль: assets/scripts/php/woocommerce/emails/email-language.php
 * Вёрстка: woocommerce/emails/customer-failed-order.php
 */

return [
	'strings' => [
		// Тема письма. {order_number} — номер заказа
		'subject'          => 'Не вдалося оплатити замовлення #{order_number}',

		'heading'          => 'Помилка оплати',
		'order_number'     => 'Номер замовлення:',
		'intro'            => 'На жаль, ваше замовлення не було виконано.',

		'messengers_intro' => 'Щоб розв’язати цю проблему, перейдіть у чат одного з месенджерів:',
		'messengers_note'  => 'У першому повідомленні вкажіть ваші ім’я та прізвище або ваш email і повідомте менеджеру, що ваше замовлення не вдалося. І ми виправимо цю прикрість. Там вам допоможуть оформити замовлення, а ще на вас чекає приємний бонус.',
		'signoff'          => 'З повагою, Макс Погорілий і команда Moveat',

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
