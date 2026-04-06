/* 
	Файл содержит методы для создания заказа и запуска оплаты через серверные endpoint-ы. 
*/

// Создает API заказа: создание и оплата.
export function createOrderApi(httpClient, options = {}) {
	const createOrderPath = options.createOrderPath || '/wp-json/moveat/v1/orders';
	const payPath = options.payPath || '/wp-json/moveat/v1/orders/pay';

	return {
		// Создает заказ на сервере.
		createOrder(payload) {
			return httpClient.post(createOrderPath, payload || {});
		},

		// Запускает оплату для заказа.
		pay(payload) {
			return httpClient.post(payPath, payload || {});
		},
	};
}

