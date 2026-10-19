<?php
/**
 * Русский словарь страницы для донатов.
 *
 * Заголовки, тексты и подписи площадок — запасные на случай пустых ACF-полей.
 * Тексты письма донатору — в emails/customer-donation-completed-order.
 * %s — сумма или ссылка. Ссылки на политику и правила тоже здесь.
 *
 * Шаблон: templates/donations-page.php
 */

return [
	'strings' => [
		// Шапка и блоки
		'title'                      => 'Поддержать проект Moveat',
		'subtitle'                   => 'Выберите удобный способ регулярной поддержки или отправьте разовый донат. Каждый вклад помогает создавать полезный контент о здоровом питании.',
		'onetime_title'              => 'Единоразовый платеж',
		'onetime_button'             => 'Поддержать',
		'subscriptions_title'        => 'Подписки и спонсорство',

		// Разовый платёж
		'card'                       => 'Банковская карта',
		'card_or_paypal'             => 'Банковская карта или PayPal',
		'card_or_paypal_description' => 'Разовый платеж через PayPal или банковскую карту',

		// Площадки подписок
		'youtube_name'               => 'Спонсорство на YouTube',
		'youtube_description'        => 'Ежемесячная поддержка через YouTube Membership',
		'patreon_description'        => 'Регулярная подписка и эксклюзивные материалы',
		'paypal_description'         => 'Подписка $10 / месяц',
		'lava_top_description'       => 'Разовая/регулярная поддержка через Lava/top',
		'boosty_description'         => 'Разовая/регулярная поддержка через Boosty',
		'go'                         => 'Перейти',

		// USDT
		'usdt_description'           => 'Криптовалютный перевод на кошелёк TRC20',
		'copy_address'               => 'Скопировать адрес',
		'copy'                       => 'Скопировать',
		'show_qr'                    => 'Показать QR-код',
		'qr_title'                   => 'Отсканируйте QR-код',
		'qr_alt'                     => 'QR-код кошелька USDT',
		'qr_network'                 => 'Network (Сеть):',
		'qr_or_copy'                 => 'Или скопируйте адрес кошелька:',
		'copy_wallet'                => 'Скопировать адрес кошелька',
		'close'                      => 'Закрыть',

		// Форма доната
		'methods_title'              => 'Способ оплаты',
		'amount_title'               => 'Сумма доната',
		'amount_custom'              => 'Другая сумма, $',
		'amount_min'                 => 'Минимальная сумма доната - %s$',
		'first_name'                 => 'Имя',
		'first_name_placeholder'     => 'Иван',
		'first_name_error'           => 'Введите имя',
		'last_name'                  => 'Фамилия',
		'last_name_placeholder'      => 'Иванов',
		'phone'                      => 'Номер телефона',
		'phone_error'                => 'Введите корректный номер телефона',
		'email_error'                => 'Введите корректный email',
		'help_label'                 => 'Информация об оплате картой российского банка',
		'help_text'                  => 'Если у вас <span>НЕ ПРОХОДИТ ОПЛАТА,</span> свяжитесь с нами по одному из мессенджеров и мы поможем вам с оплатой.',
		'privacy_text'               => 'Ваши личные данные будут использоваться для обработки ваших заказов, упрощения вашей работы с сайтом и для других целей, описанных в нашей <a href="%s">политике конфиденциальности</a>.',
		'privacy_url'                => 'https://moveat.expert/politika-konfidentsialnosti/',
		'agree_text'                 => 'Я прочитал(а) и соглашаюсь с <a href="%s">правилами сайта</a>',
		'agree_url'                  => 'https://moveat.expert/public-contract/',
		'submit'                     => 'Отправить донат',
	],
];
