<?php
/* 
	Файл описывает интерфейс серверных маршрутов промокодов. 
*/

namespace Moveat\Woo\Api\Contracts;

defined( 'ABSPATH' ) || exit;

interface CouponsRoutesInterface {
	// Применяет промокод к корзине.
	public function apply_coupon( \WP_REST_Request $request ): \WP_REST_Response;

	// Удаляет промокод из корзины.
	public function remove_coupon( \WP_REST_Request $request ): \WP_REST_Response;
}

