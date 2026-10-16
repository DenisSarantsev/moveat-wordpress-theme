<?php
/**
 * Український словник листа клієнту «Нагадування про оплату».
 *
 * Модуль: assets/scripts/php/woocommerce/emails/reminders.php
 * Вёрстка: woocommerce/emails/customer-remind-about-payment.php
 */

return [
	'strings' => [
		// Тема письма. %s — номер заказа; суффикс добавляется ко второму напоминанию
		'subject'          => 'Нагадування про оплату замовлення #%s',
		'subject_repeat'   => ' (повторне нагадування)',

		'heading'          => 'Нагадування',
		'order_number'     => 'Номер замовлення:',
		'intro'            => 'Дякуємо за ваше замовлення на нашому сайті! Ми дуже цінуємо ваш вибір.',
		'pay_intro'        => 'Ми помітили, що ваше замовлення ще не оплачене. Оплатити замовлення можна просто зараз за посиланням:',
		'pay_button'       => 'Оплатити замовлення',

		'messengers_intro' => 'Будь ласка, з усіх питань звертайтеся в чат одного з месенджерів:',

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
		'whatsapp' => 'https://380991900483.wa.pulse.is',
		'viber'    => 'https://max-pogorely.vb.pulse.is',
		'telegram' => 'https://maxpogorelybot.tg.pulse.is',
	],
];
