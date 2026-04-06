<?php
/* 
	Файл хранит карту REST-маршрутов серверного слоя WooCommerce. 
*/

namespace Moveat\Woo\Api;

defined( 'ABSPATH' ) || exit;

const MOVEAT_WOO_API_NAMESPACE = 'moveat/v1';

// Возвращает список маршрутов, которые фронт вызывает через серверный слой.
function get_routes_map(): array {
	return [
		'cart_get'         => [ 'method' => 'GET',  'path' => '/cart' ],
		'cart_add_item'    => [ 'method' => 'POST', 'path' => '/cart/add-item' ],
		'cart_remove_item' => [ 'method' => 'POST', 'path' => '/cart/remove-item' ],
		'cart_update_item' => [ 'method' => 'POST', 'path' => '/cart/update-item' ],
		'coupon_apply'     => [ 'method' => 'POST', 'path' => '/cart/apply-coupon' ],
		'coupon_remove'    => [ 'method' => 'POST', 'path' => '/cart/remove-coupon' ],
		'checkout_get'     => [ 'method' => 'GET',  'path' => '/checkout' ],
		'checkout_update'  => [ 'method' => 'POST', 'path' => '/checkout' ],
		'order_create'     => [ 'method' => 'POST', 'path' => '/orders' ],
		'order_pay'        => [ 'method' => 'POST', 'path' => '/orders/pay' ],
	];
}

