/* 
	Файл содержит API checkout: чтение состояния, данные клиента, доставка и способ оплаты. 
*/

// Создает API checkout с единым контрактом для интерфейса.
export function createCheckoutApi(httpClient = {}) {
	return {
		// Создаёт заказ через серверный прокси темы (moveat) — он форвардит запрос к wc/v3/orders
		// Это позволяет безопасно использовать consumer key/secret на сервере и не раскрывать их в браузере.
		placeOrder(payload = {}) {
			return httpClient.post(`/wp-json/my-api/v1/create-order`, payload);
		},

		// Обновляет существующий заказ (например, добавить payment_method и billing) через REST Orders API.
		// Используем PUT для частичного/полного обновления заказа.
		payOrder(orderId, payload = {}) {
			return httpClient.post(
				`/wp-json/my-api/v1/pay-order/${orderId}`,
				payload,
			);
		},
	};
}
