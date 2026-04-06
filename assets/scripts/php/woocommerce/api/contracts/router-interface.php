<?php
/* 
	Файл описывает общий интерфейс регистратора серверных API-маршрутов WooCommerce. 
*/

namespace Moveat\Woo\Api\Contracts;

defined( 'ABSPATH' ) || exit;

interface RouterInterface {
	// Регистрирует REST-маршруты в WordPress.
	public function register_routes(): void;
}

