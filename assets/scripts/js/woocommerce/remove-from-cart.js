import { showSystemMessage } from "../modules/system-message.js";
import {
	refreshCartData,
	toggleSummaryLoader,
	toggleCheckoutButton,
} from "./common/refresh-cart-data.js";

// Возвращает объект API WooCommerce из глобального контейнера window.MOVEAT_API.
// Если API недоступен или не содержит метод removeItem — возвращает null.
function getWooApi() {
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	if (!api || !api.cart || typeof api.cart.removeItem !== "function") {
		return null;
	}
	return api;
}

// Блокирует или разблокирует список товаров корзины на время выполнения запроса.
function toggleListLock(isLocked) {
	const list = document.querySelector(".cart-page__list");
	if (!list) return;
	list.classList.toggle("unactive", isLocked);
}

// Блокирует или разблокирует кнопку удаления: добавляет класс unactive и выставляет aria-disabled.
function lockButton(button, isLocked) {
	if (!button) return;
	button.classList.toggle("unactive", isLocked);
	button.setAttribute("aria-disabled", isLocked ? "true" : "false");
}

// Инициализирует обработчики удаления товара для всех кнопок [data-cart-action="remove"].
// При клике блокирует кнопку, отправляет запрос на удаление, затем обновляет UI корзины.
function initRemoveFromCart() {
	const api = getWooApi();
	if (!api) return;

	const buttons = document.querySelectorAll('[data-cart-action="remove"]');
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.setAttribute("aria-disabled", "false");
		button.addEventListener("click", async (event) => {
			event.preventDefault();
			if (button.classList.contains("unactive")) return;

			const item = button.closest("[data-cart-item][data-cart-item-key]");
			if (!item) return;

			const cartItemKey = item.getAttribute("data-cart-item-key");
			if (!cartItemKey) return;

			try {
				// Блокируем кнопку удаления, чтобы исключить повторный клик
				lockButton(button, true);
				// Показываем лоадер в блоке итогов корзины
				toggleSummaryLoader(true);
				// Делаем список товаров неактивным на время запроса
				toggleListLock(true);
				// Блокируем кнопку «Оформить заказ», чтобы нельзя было перейти к оплате до обновления цен
				toggleCheckoutButton(true);
				// Отправляем запрос на удаление товара из корзины
				await api.cart.removeItem(cartItemKey);
				// Удаляем элемент товара из DOM
				item.remove();
				// Обновляем данные и UI корзины
				await refreshCartData();
				// Показываем системное сообщение
				// showSystemMessage("success", "Товар удален из корзины");
			} catch (error) {
				console.error(error);
				showSystemMessage("error", "Возникла ошибка во время удаления товара");
			} finally {
				toggleSummaryLoader(false);
				toggleListLock(false);
				lockButton(button, false);
			}
		});
	});
}

document.addEventListener("DOMContentLoaded", initRemoveFromCart);
