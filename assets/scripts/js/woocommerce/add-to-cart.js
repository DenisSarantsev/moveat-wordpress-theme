/* 
	Файл реализует добавление товара в корзину для карточек каталога и страницы товара. 
*/

// Безопасно приводит значение к целому количеству товара.
function toPositiveInt(value, fallback = 1) {
	const parsed = parseInt(String(value ?? ''), 10);
	return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

// Возвращает API WooCommerce из глобального контейнера.
function getWooApi() {
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	if (!api || !api.cart || typeof api.cart.addItem !== 'function') {
		return null;
	}
	return api;
}

// Обновляет базовый индикатор корзины, если на странице есть целевой селектор.
function syncCartCount(count) {
	const badge = document.querySelector('[data-cart-count]');
	if (!badge) return;
	badge.textContent = String(count || 0);
}

// Унифицированный сценарий добавления товара в корзину.
async function addToCartUseCase(productId, quantity = 1, triggerEl = null) {
	const api = getWooApi();
	if (!api) {
		throw new Error('Woo API не инициализирован');
	}

	if (triggerEl) {
		triggerEl.setAttribute('aria-disabled', 'true');
		triggerEl.classList.add('is-loading');
	}

	try {
		const cart = await api.cart.addItem(productId, quantity);
		syncCartCount(cart && cart.count ? cart.count : 0);

		if (window.jQuery) {
			window.jQuery(document.body).trigger('added_to_cart');
		}

		return cart;
	} finally {
		if (triggerEl) {
			triggerEl.removeAttribute('aria-disabled');
			triggerEl.classList.remove('is-loading');
		}
	}
}

// Подключает обработчики добавления в корзину на карточках товаров.
function bindProductCardAddToCart() {
	const buttons = document.querySelectorAll('.add_to_cart_button[data-product_id]');
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.addEventListener('click', async (event) => {
			event.preventDefault();
			event.stopPropagation();

			const productId = toPositiveInt(button.getAttribute('data-product_id'), 0);
			const quantity = toPositiveInt(button.getAttribute('data-quantity'), 1);
			if (!productId) return;

			try {
				await addToCartUseCase(productId, quantity, button);
			} catch (error) {
				// Выводим ошибку в консоль, чтобы не прерывать работу интерфейса.
				console.error(error);
			}
		});
	});
}

// Подключает обработчики кнопок на странице одного товара.
function bindProductPageAddToCart() {
	const buttons = document.querySelectorAll('[data-product-action][data-product-id]');
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.addEventListener('click', async (event) => {
			const action = button.getAttribute('data-product-action');
			if (action !== 'add-to-cart' && action !== 'buy-now') return;

			event.preventDefault();

			const productId = toPositiveInt(button.getAttribute('data-product-id'), 0);
			const quantity = toPositiveInt(button.getAttribute('data-quantity'), 1);
			if (!productId) return;

			try {
				await addToCartUseCase(productId, quantity, button);

				if (action === 'buy-now') {
					const checkoutUrl =
						(window.MOVEAT_THEME && window.MOVEAT_THEME.checkoutUrl) ||
						(window.MOVEAT_API && window.MOVEAT_API.woocommerceCheckoutUrl) ||
						'/checkout/';
					window.location.href = checkoutUrl;
					return;
				}

				const cartUrl = (window.MOVEAT_THEME && window.MOVEAT_THEME.cartUrl) || '/cart/';
				window.location.href = cartUrl;
			} catch (error) {
				// Выводим ошибку в консоль, чтобы не прерывать работу интерфейса.
				console.error(error);
			}
		});
	});
}

// Инициализирует обработчики добавления в корзину для всех поддерживаемых контекстов.
function initAddToCartHandlers() {
	bindProductCardAddToCart();
	bindProductPageAddToCart();
}

document.addEventListener('DOMContentLoaded', initAddToCartHandlers);
