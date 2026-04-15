<?php
// Регистрирует ACF-поля и их настройки для темы.
require_once __DIR__ . '/acf/global-fields.php';
// Подключает регистрацию и настройки типа записей "articles".
require_once __DIR__ . '/articles/post-type.php';
// Регистрирует меню и настройки навигации темы.
require_once __DIR__ . '/main/menu.php';
// Подключает поддержку и вывод логотипа сайта.
require_once __DIR__ . '/main/logo.php';
// Включает режим технического обслуживания и исключения.
require_once __DIR__ . '/main/maintenance-mode.php';

// -------------- WooCommerce modules
// Передает конфиг Woo Store API на фронтенд (baseUrl и nonce).
require_once __DIR__ . '/woocommerce/woo-api-config.php';
if ( class_exists( 'WooCommerce' ) ) {
	// Подключает кастомизацию карточек и шаблонов WooCommerce.
	require_once __DIR__ . '/woocommerce/product-card/setup.php';
	// Подключает серверный API-слой WooCommerce и маршруты.
	require_once __DIR__ . '/woocommerce/api/setup.php';
	// Хуки для управления статусами заказов и редиректами после оплаты.
	require_once __DIR__ . '/woocommerce/order-hooks.php';
	// Отправка клиентских писем (шаблоны в templates/emails)
	require_once __DIR__ . '/woocommerce/emails.php';
	// Создание заказа
	require_once __DIR__ . '/woocommerce/api/create-order.php';
	// Оплата заказа
	require_once __DIR__ . '/woocommerce/api/pay-order.php';
}