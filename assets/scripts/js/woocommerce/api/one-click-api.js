/* 
	Файл реализует сценарий покупки в один клик: добавить товар в корзину и перейти к оформлению. 
*/

// Создает API one-click на базе корзины.
export function createOneClickApi(cartApi, options = {}) {
	const checkoutUrl = options.checkoutUrl || '/checkout/';

	return {
		// Добавляет товар в корзину и перенаправляет на страницу checkout.
		async buyNow(productId, quantity = 1) {
			await cartApi.addItem(productId, quantity);
			window.location.href = checkoutUrl;
		},
	};
}

