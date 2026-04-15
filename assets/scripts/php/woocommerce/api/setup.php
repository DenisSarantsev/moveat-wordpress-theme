<?php
/* 
	Файл подключает серверный API-слой WooCommerce и его интерфейсы. 
*/

namespace Moveat\Woo\Api;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/contracts/router-interface.php';
require_once __DIR__ . '/contracts/cart-routes-interface.php';
require_once __DIR__ . '/contracts/coupons-routes-interface.php';
require_once __DIR__ . '/contracts/checkout-routes-interface.php';
require_once __DIR__ . '/contracts/order-routes-interface.php';
require_once __DIR__ . '/routes-map.php';
// Подключаем реализацию серверных маршрутов (create-order и другие)
require_once __DIR__ . '/create-order.php';

