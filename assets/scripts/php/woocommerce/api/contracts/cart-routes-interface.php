<?php
/* 
	Файл описывает интерфейс серверных маршрутов корзины. 
*/

namespace Moveat\Woo\Api\Contracts;

defined( 'ABSPATH' ) || exit;

interface CartRoutesInterface {
	// Возвращает текущее состояние корзины.
	public function get_cart( \WP_REST_Request $request ): \WP_REST_Response;

	// Добавляет товар в корзину.
	public function add_item( \WP_REST_Request $request ): \WP_REST_Response;

	// Удаляет товар из корзины.
	public function remove_item( \WP_REST_Request $request ): \WP_REST_Response;

	// Обновляет количество товара в корзине.
	public function update_item( \WP_REST_Request $request ): \WP_REST_Response;
}

