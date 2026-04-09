import { showSystemMessage } from "../modules/system-message.js";
import {
	refreshCartData,
	toggleSummaryLoader,
	toggleCheckoutButton,
} from "./common/refresh-cart-data.js";

// Возвращает объект API WooCommerce из глобального контейнера window.MOVEAT_API.
// Если API недоступен или не содержит метод setQuantity — возвращает null.
function getWooApi() {
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	if (!api || !api.cart || typeof api.cart.setQuantity !== "function") {
		return null;
	}
	return api;
}

// Блокирует или разблокирует обе кнопки количества внутри одного товара.
function lockQtyButtons(item, isLocked) {
	const buttons = item.querySelectorAll(".cart-page__qty-button");
	buttons.forEach((btn) => {
		btn.classList.toggle("unactive", isLocked);
		btn.setAttribute("aria-disabled", isLocked ? "true" : "false");
	});
}

// Блокирует или разблокирует список товаров корзины на время выполнения запроса.
function toggleListLock(isLocked) {
	const list = document.querySelector(".cart-page__list");
	if (!list) return;
	list.classList.toggle("unactive", isLocked);
}

// Читает текущее отображаемое количество товара из DOM.
function getCurrentQty(item) {
	const qtyEl = item.querySelector(".cart-page__qty-value");
	if (!qtyEl) return 1;
	const parsed = parseInt(qtyEl.textContent, 10);
	return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
}

// Обновляет отображаемое количество товара в DOM.
function setDomQty(item, qty) {
	const qtyEl = item.querySelector(".cart-page__qty-value");
	if (qtyEl) qtyEl.textContent = String(qty);
}

// Инициализирует обработчики кнопок увеличения/уменьшения количества.
// При клике блокирует элементы, отправляет запрос setQuantity, затем обновляет UI корзины.
function initUpdateQuantity() {
	const api = getWooApi();
	if (!api) return;

	const buttons = document.querySelectorAll(
		'[data-cart-action="increase"], [data-cart-action="decrease"]',
	);
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.setAttribute("aria-disabled", "false");

		// При инициализации: если это кнопка уменьшения и количество равно 1 — делаем её неактивной
		if (button.getAttribute("data-cart-action") === "decrease") {
			const item = button.closest("[data-cart-item][data-cart-item-key]");
			if (item) {
				const qty = getCurrentQty(item);
				button.classList.toggle("unactive", qty <= 1);
				button.setAttribute("aria-disabled", qty <= 1 ? "true" : "false");
			}
		}

		// Клик по кнопке изменени количества
		button.addEventListener("click", async (event) => {
			event.preventDefault();
			if (button.classList.contains("unactive")) return;

			const item = button.closest("[data-cart-item][data-cart-item-key]");
			if (!item) return;

			const cartItemKey = item.getAttribute("data-cart-item-key");
			if (!cartItemKey) return;

			const action = button.getAttribute("data-cart-action");
			const currentQty = getCurrentQty(item);
			const newQty = action === "increase" ? currentQty + 1 : currentQty - 1;

			// Не позволяем опустить количество ниже 1
			if (newQty < 1) return;

			try {
				// Блокируем кнопки количества у этого товара, чтобы исключить повторный клик
				lockQtyButtons(item, true);
				// Показываем лоадер в блоке итогов корзины
				toggleSummaryLoader(true);
				// Блокируем кнопку «Оформить заказ», чтобы нельзя было перейти к оплате до обновления цен
				toggleCheckoutButton(true);

				// Оптимистично обновляем количество в DOM, чтобы не было скачка после ответа
				setDomQty(item, newQty);

				// Отправляем запрос на изменение количества товара (не используем возвращаемый объект напрямую)
				await api.cart.setQuantity(cartItemKey, newQty);

				// Получим и применим актуальное состояние корзины внутри refreshCartData()
				await refreshCartData();
			} catch (error) {
				console.error(error);
				// Откатываем количество в DOM при ошибке
				setDomQty(item, currentQty);
				// Восстановим состояние кнопки уменьшения
				const dec = item.querySelector('[data-cart-action="decrease"]');
				if (dec) {
					dec.classList.toggle("unactive", currentQty <= 1);
					dec.setAttribute("aria-disabled", currentQty <= 1 ? "true" : "false");
				}
				showSystemMessage("error", "Не удалось обновить количество товара");
			} finally {
				// Выключаем лоадер и снимаем блокировку со списка
				toggleSummaryLoader(false);
				// Разблокируем кнопки количества
				lockQtyButtons(item, false);

				// После снятия блокировки — убедимся, что кнопка уменьшения корректно отражает текущее количество
				const finalQty = getCurrentQty(item);
				const decFinal = item.querySelector('[data-cart-action="decrease"]');
				if (decFinal) {
					decFinal.classList.toggle("unactive", finalQty <= 1);
					decFinal.setAttribute(
						"aria-disabled",
						finalQty <= 1 ? "true" : "false",
					);
				}
			}
		});
	});
}

document.addEventListener("DOMContentLoaded", initUpdateQuantity);
