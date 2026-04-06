/* 
	Файл собирает и экспортирует единый API-слой WooCommerce для модулей интерфейса. 
*/

import { createWooHttpClient } from './http-client.js';
import { createCartApi } from './cart-api.js';
import { createCouponsApi } from './coupons-api.js';
import { createCheckoutApi } from './checkout-api.js';
import { createOrderApi } from './order-api.js';
import { createOneClickApi } from './one-click-api.js';

export { WooApiError } from './http-client.js';
export { normalizeCart, normalizeCartItem, normalizeCartTotals } from './cart-transformers.js';
export { createCartApi, createCouponsApi, createCheckoutApi, createOrderApi, createOneClickApi };

// Создает единый объект API для работы с корзиной, купонами, checkout и заказами.
export function createWooApiLayer(config = {}) {
	const httpClient = createWooHttpClient(config.http || {});
	const cart = createCartApi(httpClient, config.cart || {});
	const coupons = createCouponsApi(httpClient, config.coupons || {});
	const checkout = createCheckoutApi(httpClient, config.checkout || {});
	const order = createOrderApi(httpClient, config.order || {});
	const oneClick = createOneClickApi(cart, {
		checkoutUrl: config.checkoutUrl || '/checkout/',
	});

	return {
		httpClient,
		cart,
		coupons,
		checkout,
		order,
		oneClick,
	};
}

