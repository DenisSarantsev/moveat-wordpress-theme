/* 
	Файл содержит фасад API корзины: чтение корзины, добавление, удаление и изменение количества.
*/

import { normalizeCart } from "./cart-transformers.js";

const STORE_API_BASE = "/wp-json/wc/store/v1";

// Создает набор методов для работы с корзиной через Store API.
export function createCartApi(httpClient, options = {}) {
	const basePath = options.basePath || STORE_API_BASE;

	return {
		// Получает текущее состояние корзины.
		async getCart() {
			// Логируем вызов, чтобы было видно в консоли, когда выполняется запрос за корзиной
			try {
				const cart = await httpClient.get(`${basePath}/cart`);
				return normalizeCart(cart);
			} catch (err) {
				throw err;
			}
		},

		// Добавляет товар в корзину.
		async addItem(productId, quantity = 1) {
			const cart = await httpClient.post(`${basePath}/cart/add-item`, {
				id: Number(productId),
				quantity: Number(quantity),
			});
			return normalizeCart(cart);
		},

		// Удаляет товар из корзины по ключу позиции.
		async removeItem(cartItemKey) {
			const cart = await httpClient.post(`${basePath}/cart/remove-item`, {
				key: cartItemKey,
			});
			return normalizeCart(cart);
		},

		// Изменяет количество товара в корзине по ключу позиции.
		async setQuantity(cartItemKey, quantity) {
			const cart = await httpClient.post(`${basePath}/cart/update-item`, {
				key: cartItemKey,
				quantity: Number(quantity),
			});
			return normalizeCart(cart);
		},
	};
}
