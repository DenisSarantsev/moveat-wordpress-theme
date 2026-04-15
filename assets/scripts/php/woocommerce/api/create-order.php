<?php

namespace Moveat\Woo\Api;

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
    register_rest_route(
        'my-api/v1',
        '/create-order',
        [
            'methods'             => 'POST',
            'callback'            => __NAMESPACE__ . '\\moveat_proxy_create_order',
            'permission_callback' => '__return_true',
        ]
    );
});

function moveat_proxy_create_order( \WP_REST_Request $request ) {

    // 🔹 1. Получаем cart (ВАЖНО: с cookies)
    $cart_response = wp_remote_get(
        site_url( '/wp-json/wc/store/v1/cart' ),
        [
            'headers' => [
                'Cookie' => $_SERVER['HTTP_COOKIE'] ?? '',
            ],
            'timeout' => 15,
        ]
    );

    if ( is_wp_error( $cart_response ) ) {
        return new \WP_REST_Response( [
            'error' => 'Cart request failed',
        ], 500 );
    }

    $cart = json_decode( wp_remote_retrieve_body( $cart_response ), true );

    if ( empty( $cart['items'] ) ) {
        return new \WP_REST_Response( [
            'error' => 'Cart is empty',
        ], 400 );
    }

    // 🔹 2. Формируем line_items
    $line_items = [];

    foreach ( $cart['items'] as $item ) {
        $line_items[] = [
            'product_id' => $item['id'],
            'quantity'   => $item['quantity'],
        ];
    }

    // 🔹 3. Берём только безопасные данные с фронта
    $body = $request->get_json_params();

    $billing  = $body['billing']  ?? [];
    $shipping = $body['shipping'] ?? [];

    // 🔹 4. Формируем order payload
    $order_data = [
        'payment_method'       => $body['payment_method'] ?? '',
        'payment_method_title' => $body['payment_method_title'] ?? '',
        'set_paid'             => false,

        'billing'  => $billing,
        'shipping' => $shipping,

        'line_items' => $line_items,
    ];

    // 🔹 5. Добавляем купоны (если есть)
		$coupon_lines = [];

		if (!empty($cart['coupons']) && is_array($cart['coupons'])) {
				foreach ($cart['coupons'] as $coupon) {
						if (!empty($coupon['code'])) {
								$coupon_lines[] = [
										'code' => $coupon['code']
								];
						}
				}
		}

		if (!empty($coupon_lines)) {
				$order_data['coupon_lines'] = $coupon_lines;
		}

    // 🔹 6. WooCommerce API ключи (лучше вынести в wp-config.php)
    $consumer_key    = defined('WC_API_KEY') ? WC_API_KEY : 'ck_xxx';
    $consumer_secret = defined('WC_API_SECRET') ? WC_API_SECRET : 'cs_xxx';

    $auth = base64_encode( $consumer_key . ':' . $consumer_secret );

    // 🔹 7. Создаём заказ
    $order_response = wp_remote_post(
        site_url( '/wp-json/wc/v3/orders' ),
        [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $order_data ),
            'timeout' => 20,
        ]
    );

    if ( is_wp_error( $order_response ) ) {
        return new \WP_REST_Response( [
            'error' => $order_response->get_error_message(),
        ], 500 );
    }

    $code = wp_remote_retrieve_response_code( $order_response );
    $data = json_decode( wp_remote_retrieve_body( $order_response ), true );

    // 🔹 8. (опционально) очищаем корзину после создания заказа
    // wp_remote_request(
    //     site_url( '/wp-json/wc/store/v1/cart/items' ),
    //     [
    //         'method'  => 'DELETE',
    //         'headers' => [
    //             'Cookie' => $_SERVER['HTTP_COOKIE'] ?? '',
    //         ],
    //     ]
    // );

    return new \WP_REST_Response( $data, $code );
}