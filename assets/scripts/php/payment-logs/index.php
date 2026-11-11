<?php
/**
 * Журнал попыток оплаты (mono, PayPal, оплата частями) → assets/logs/payments.log.
 *
 * Модуль только пишет журнал и заметки в заказ, логику оплаты не меняет.
 * Точки вызова снаружи: do_action( 'moveat_payment_attempt' ) в api/pay-order.php
 * и do_action( 'moveat_mono_status_checked' ) в mono-status-reconcile.php.
 */

// Запись в файл, формат строки, счётчик попыток.
require_once __DIR__ . '/logger.php';
// Каждое нажатие «Оплатить» (наш REST pay-order).
require_once __DIR__ . '/pay-attempts.php';
// Статус инвойса mono: errCode/failureReason, заметка в заказ, защита от дублей.
require_once __DIR__ . '/mono-status.php';
// Webhook mono.
require_once __DIR__ . '/mono-webhook.php';
// Cron-сверка mono.
require_once __DIR__ . '/mono-reconcile.php';
// Переход в failed (любой шлюз) и заметки PayPal об отказах.
require_once __DIR__ . '/order-failed.php';
