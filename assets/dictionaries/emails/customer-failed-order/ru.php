<?php
/**
 * Русский словарь письма клиенту «Ошибка оплаты».
 *
 * Язык письма — язык заказа (см. moveat_order_lang()).
 *
 * Модуль: assets/scripts/php/woocommerce/emails/email-language.php
 * Вёрстка: woocommerce/emails/customer-failed-order.php
 */

return [
	'strings' => [
		// Тема письма. Пусто — берётся тема из настроек WooCommerce
		// («WooCommerce → Настройки → Email»). Можно {order_number}.
		'subject'          => '',

		// Шапка
		'heading'          => 'Ошибка оплаты',
		'order_number'     => 'Номер заказа:',
		'intro'            => 'К сожалению, ваш заказ не был выполнен.',

		// Мессенджеры: текст до и после иконок
		'messengers_intro' => 'Для решения этой проблемы перейдите в чат одного из мессенджеров:',
		'messengers_note'  => 'В первом сообщении укажите ваши имя и фамилию, или ваш имейл, и сообщите менеджеру, что ваш заказ не удался. И мы исправим эту оплошность. Там вам помогут оформить заказ и еще вас ждет приятный бонус.',
		'signoff'          => 'С уважением, Макс Погорелый и команда Moveat',

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
