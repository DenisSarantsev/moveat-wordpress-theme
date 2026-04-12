/* 
	Файл содержит API checkout: чтение состояния, данные клиента, доставка и способ оплаты. 
*/

const STORE_API_BASE = "/wp-json/wc/store/v1";

// Создает API checkout с единым контрактом для интерфейса.
export function createCheckoutApi(httpClient, options = {}) {
	const basePath = options.basePath || STORE_API_BASE;

	return {
		// Получает текущее состояние checkout.
		getCheckout() {
			return httpClient.get(`${basePath}/checkout`);
		},

		// Сохраняет данные клиента в checkout.
		setCustomer(payload) {
			return httpClient.post(`${basePath}/checkout`, payload || {});
		},

		// Сохраняет данные доставки в checkout.
		setShipping(payload) {
			return httpClient.post(`${basePath}/checkout`, payload || {});
		},

		// Сохраняет выбранный способ оплаты и его параметры.
		setPaymentMethod(paymentMethod, paymentData = {}) {
			return httpClient.post(`${basePath}/checkout`, {
				payment_method: paymentMethod,
				payment_data: paymentData,
			});
		},

		// Оформляет заказ: отправляет billing_address и payment_method, возвращает order_id и redirect_url.
		placeOrder(payload = {}) {
			return httpClient.post(`${basePath}/checkout`, payload);
		},

		// Инициирует оплату существующего заказа по его ID.
		payOrder(orderId, payload = {}) {
			return httpClient.post(`${basePath}/checkout/${orderId}`, payload);
		},
	};
}
