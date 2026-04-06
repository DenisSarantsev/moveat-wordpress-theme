<?php
/* 
	Файл описывает интерфейс серверных маршрутов оформления заказа (checkout). 
*/

namespace Moveat\Woo\Api\Contracts;

defined( 'ABSPATH' ) || exit;

interface CheckoutRoutesInterface {
	// Возвращает текущее состояние checkout.
	public function get_checkout( \WP_REST_Request $request ): \WP_REST_Response;

	// Обновляет данные checkout (клиент, доставка, оплата).
	public function update_checkout( \WP_REST_Request $request ): \WP_REST_Response;
}

