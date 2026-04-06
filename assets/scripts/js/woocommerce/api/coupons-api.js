/* 
	Файл содержит методы для применения и удаления промокодов в корзине. 
*/

import { normalizeCart } from './cart-transformers.js';

const STORE_API_BASE = '/wp-json/wc/store/v1';

// Создает API-методы для работы с промокодами через Store API.
export function createCouponsApi(httpClient, options = {}) {
	const basePath = options.basePath || STORE_API_BASE;

	return {
		// Применяет промокод к текущей корзине.
		async applyCoupon(code) {
			const cart = await httpClient.post(`${basePath}/cart/apply-coupon`, { code: String(code || '').trim() });
			return normalizeCart(cart);
		},

		// Удаляет промокод из текущей корзины.
		async removeCoupon(code) {
			const cart = await httpClient.post(`${basePath}/cart/remove-coupon`, { code: String(code || '').trim() });
			return normalizeCart(cart);
		},
	};
}

