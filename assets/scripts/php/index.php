<?php
// Регистрирует ACF-поля и их настройки для темы.
require_once __DIR__ . '/acf/global-fields.php';
// Группа «Рубрики — текст кнопок» на странице глобальных настроек + хелперы подписи кнопок.
require_once __DIR__ . '/acf/category-buttons.php';
// Исключения из поисковой выдачи (блок на странице настроек + фильтр pre_get_posts).
require_once __DIR__ . '/search/search-exclusions.php';
// Хелперы тем отзывов и динамические чекбоксы ACF (словарь → отзывы).
require_once __DIR__ . '/reviews/reviews-topic-helpers.php';
require_once __DIR__ . '/reviews/acf-review-topics-choices.php';
// Подключает регистрацию и настройки типа записей "articles".
require_once __DIR__ . '/articles/post-type.php';
// Тип записей «Баннеры».
require_once __DIR__ . '/banners/post-type.php';
// Шорткоды баннеров (шаблоны в templates/banners).
require_once __DIR__ . '/banners/shortcodes.php';
// Регистрирует меню и настройки навигации темы.
require_once __DIR__ . '/main/menu.php';
// Подключает поддержку и вывод логотипа сайта.
require_once __DIR__ . '/main/logo.php';
// Включает режим технического обслуживания и исключения.
require_once __DIR__ . '/main/maintenance-mode.php';
// Правила для показа 410 ошибки
require_once __DIR__ . '/main/410-rules.php';
// Google AdSense и Google Analytics 4.
require_once __DIR__ . '/google-scripts/setup.php';

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
	// Создание заказа
	require_once __DIR__ . '/woocommerce/api/create-order.php';
	// Минимальная сумма доната (единый источник для API и шаблона)
	require_once __DIR__ . '/woocommerce/donations-config.php';
	// Создание заказа доната
	require_once __DIR__ . '/woocommerce/api/create-donation-order.php';
	// Оплата заказа
	require_once __DIR__ . '/woocommerce/api/pay-order.php';
	// Напоминание об оплате (пользователю и менеджерам)
	require_once __DIR__ . '/woocommerce/emails/reminders.php';
	// Сообщение о выполненном заказе админу
	require_once __DIR__ . '/woocommerce/emails/completed-order.php';
	// Подтверждение доната донатору
	require_once __DIR__ . '/woocommerce/emails/donation-completed-order.php';
}