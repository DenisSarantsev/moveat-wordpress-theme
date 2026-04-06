<?php
/* 
	Файл описывает интерфейс серверных маршрутов создания заказа и запуска оплаты. 
*/

namespace Moveat\Woo\Api\Contracts;

defined( 'ABSPATH' ) || exit;

interface OrderRoutesInterface {
	// Создает заказ на сервере.
	public function create_order( \WP_REST_Request $request ): \WP_REST_Response;

	// Запускает оплату заказа.
	public function pay_order( \WP_REST_Request $request ): \WP_REST_Response;
}

