<?php
/**
 * Русский словарь письма клиенту «Заказ выполнен».
 *
 * Язык письма — язык заказа (см. moveat_order_lang()).
 *
 * Модуль: assets/scripts/php/woocommerce/emails/email-language.php
 * Вёрстка: woocommerce/emails/customer-completed-order.php
 */

return [
	'strings' => [
		// Тема письма. Пусто — берётся тема из настроек WooCommerce
		// («WooCommerce → Настройки → Email»). Можно {order_number}.
		'subject'          => '',

		// Шапка
		'heading'          => 'Заказ выполнен',
		'order_number'     => 'Номер заказа:',
		'intro'            => 'Ваш заказ в Школе здорового питания Максима Погорелого отмечен у нас как "выполненный".',

		// Мессенджеры: текст до и после иконок
		'messengers_intro' => 'Для удобства давайте перейдем в чат одного из мессенджеров:',
		'messengers_note'  => 'Выбирайте удобный вам мессенджер для общения. Напишите в первом сообщении ваши Фамилию и Имя, а также email.',

		// Файлы для скачивания
		'downloads_title'  => 'Ваши файлы для скачивания',

		// Товары и суммы
		'qty_unit'         => 'шт',
		'subtotal'         => 'Сумма:',
		'discount'         => 'Скидка:',
		'total'            => 'К оплате:',

		// Контакты покупателя
		'phone'            => 'Телефон:',
		'email'            => 'Email:',

		// Подвал. Между этими строками стоит ссылка на почту
		'contact_before'   => 'Есть вопросы, или возникли проблемы с оплатой заказа? Пишите нам на почту',
		'contact_after'    => 'или в мессенджеры.',
		'footer'           => 'Moveat Expert',
	],

	// Ссылки на мессенджеры в письме
	'messengers' => [
		'whatsapp' => 'https://380991900483.wa.pulse.is',
		'viber'    => 'https://max-pogorely.vb.pulse.is',
		'telegram' => 'https://maxpogorelybot.tg.pulse.is',
	],
];
