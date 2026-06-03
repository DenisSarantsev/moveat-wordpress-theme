/* 
	Файл реализует добавление товара в корзину для карточек каталога и страницы товара. 
*/
import { showSystemMessage } from "../modules/system-message.js";
import { syncCartCount } from "./common/cart-count.js";

// Безопасно приводит значение к целому количеству товара.
function toPositiveInt(value, fallback = 1) {
	const parsed = parseInt(String(value ?? ""), 10);
	return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

// Возвращает API WooCommerce из глобального контейнера.
function getWooApi() {
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	if (!api || !api.cart || typeof api.cart.addItem !== "function") {
		return null;
	}
	return api;
}

// Унифицированный сценарий добавления товара в корзину.
async function addToCartUseCase(productId, quantity = 1, triggerEl = null) {
	const api = getWooApi();
	if (!api) {
		throw new Error("Woo API не инициализирован");
	}

	if (triggerEl) {
		triggerEl.setAttribute("aria-disabled", "true");
		triggerEl.classList.add("is-loading");
	}

	try {
		const cart = await api.cart.addItem(productId, quantity);
		syncCartCount(cart && cart.count ? cart.count : 0);
		showSystemMessage("success", "Товар добавлен в корзину");

		if (window.jQuery) {
			window.jQuery(document.body).trigger("added_to_cart");
		}

		return cart;
	} finally {
		if (triggerEl) {
			triggerEl.removeAttribute("aria-disabled");
			triggerEl.classList.remove("is-loading");
		}
	}
}

// Переключает визуальное состояние кнопки корзины в карточке товара (показывает/скрывает лоадер)
function toggleCardButtonLoaderState(button, isLoading) {
	if (!button) return;
	const icon = button.querySelector(".product-card__button-cart-icon");
	const loader = button.querySelector(".loader");
	if (!icon || !loader) return;

	if (isLoading) {
		icon.classList.add("disabled");
		loader.classList.remove("disabled");
		return;
	}

	icon.classList.remove("disabled");
	loader.classList.add("disabled");
}

// Переключает лоадер в блоке кнопок на странице товара.
function setProductPageLoaderState(buttonsBlock, isLoading) {
	if (!buttonsBlock) return;
	const loader = buttonsBlock.querySelector(".loader");
	if (!loader) return;

	if (isLoading) {
		loader.classList.remove("disabled");
		return;
	}

	loader.classList.add("disabled");
}

function toggleProductPageButtonLoaderState(button, isLoading) {
	if (!button) return;
	setProductPageLoaderState(button.closest(".product-page__buttons"), isLoading);
}

// Подключает обработчики добавления в корзину на карточках товаров.
function bindProductCardAddToCart() {
	const buttons = document.querySelectorAll(
		".product-card__button-cart[data-product-id]",
	);
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.addEventListener("click", async (event) => {
			event.preventDefault();
			event.stopPropagation();

			const productId = toPositiveInt(
				button.getAttribute("data-product-id"),
				0,
			);
			const quantity = toPositiveInt(button.getAttribute("data-quantity"), 1);
			if (!productId) return;

			try {
				toggleCardButtonLoaderState(button, true);
				await addToCartUseCase(productId, quantity, button);
				handleProductCardAddedToCart(button);
			} catch (error) {
				// Выводим ошибку в консоль, чтобы не прерывать работу интерфейса.
				console.error(error);
			} finally {
				toggleCardButtonLoaderState(button, false);
			}
		});
	});
}

// Возвращает URI темы для построения путей к иконкам карточки товара.
function getProductCardThemeUri(button) {
	const themeUri = window.MOVEAT_THEME && window.MOVEAT_THEME.themeUri;
	if (themeUri) {
		return themeUri;
	}

	const cartIcon = button && button.querySelector(".product-card__button-cart-icon");
	if (!cartIcon || !cartIcon.src) {
		return "";
	}

	return cartIcon.src.replace(/\/assets\/images\/icons\/cart\.png(?:\?.*)?$/, "");
}

// Возвращает URL страницы корзины.
function getCartPageUrl() {
	if (window.MOVEAT_WOO_API_CONFIG && window.MOVEAT_WOO_API_CONFIG.cartUrl) {
		return window.MOVEAT_WOO_API_CONFIG.cartUrl;
	}

	return "/cart/";
}

// Заменяет кнопку добавления в корзину на индикатор «товар уже в корзине».
function handleProductCardAddedToCart(button) {
	if (!button || button.classList.contains("added-to-cart-button")) {
		return;
	}

	const themeUri = getProductCardThemeUri(button);
	if (!themeUri) {
		return;
	}

	const cartIconFilled = `${themeUri}/assets/images/icons/filled/shopping-cart-filled.png`;
	const checkMarkIcon = `${themeUri}/assets/images/icons/filled/check-mark-filled.png`;

	const addedState = document.createElement("a");
	addedState.href = getCartPageUrl();
	addedState.className =
		"product-card__button product-card__button-cart added-to-cart-button";
	addedState.setAttribute("aria-label", "Перейти в корзину");
	addedState.innerHTML = `
		<div class="product-page__added-to-cart">
			<div class="product-page__added-to-cart_title">
				<div class="product-page__added-to-cart_title-icons">
					<img src="${cartIconFilled}" alt="Товар добавлен в корзину" />
					<div class="product-page__added-to-cart_cart-icon">
						<img src="${checkMarkIcon}" alt="Галочка" />
					</div>
				</div>
			</div>
		</div>
	`;

	button.replaceWith(addedState);
}

// Возвращает URI темы для построения путей к иконкам на странице товара.
function getProductPageThemeUri() {
	if (window.MOVEAT_THEME && window.MOVEAT_THEME.themeUri) {
		return window.MOVEAT_THEME.themeUri;
	}

	return "";
}

// Заменяет кнопку «Добавить в корзину» на странице товара на индикатор «товар уже в корзине».
function handleProductPageAddedToCart(button) {
	if (
		!button ||
		button.getAttribute("data-product-action") !== "add-to-cart"
	) {
		return;
	}

	const themeUri = getProductPageThemeUri();
	if (!themeUri) {
		return;
	}

	const cartIconFilled = `${themeUri}/assets/images/icons/filled/shopping-cart-filled.png`;
	const checkMarkIcon = `${themeUri}/assets/images/icons/filled/check-mark-filled.png`;

	const addedState = document.createElement("a");
	addedState.href = getCartPageUrl();
	addedState.className = "product-page__added-to-cart proguct-page-button";
	addedState.setAttribute("aria-label", "Перейти в корзину");
	addedState.innerHTML = `
		<div class="product-page__added-to-cart_title">
			<div class="product-page__added-to-cart_title-icons">
				<img src="${cartIconFilled}" alt="Товар добавлен в корзину" />
				<div class="product-page__added-to-cart_cart-icon">
					<img src="${checkMarkIcon}" alt="Галочка" />
				</div>
			</div>
			<div class="product-page__added-to-cart_title-text">
				Товар добавлен в корзину
			</div>
		</div>
	`;

	button.replaceWith(addedState);
}

// Подключает обработчики кнопок на странице одного товара.
function bindProductPageAddToCart() {
	const buttons = document.querySelectorAll(
		".product-page__buttons [data-product-action][data-product-id]",
	);
	if (!buttons.length) return;

	buttons.forEach((button) => {
		button.addEventListener("click", async (event) => {
			const action = button.getAttribute("data-product-action");
			if (action !== "add-to-cart" && action !== "buy-now") return;
			if (action === "add-to-cart" && button.classList.contains("unactive"))
				return;

			event.preventDefault();

			const productId = toPositiveInt(
				button.getAttribute("data-product-id"),
				0,
			);
			const quantity = toPositiveInt(button.getAttribute("data-quantity"), 1);
			if (!productId) return;

			const buttonsBlock = button.closest(".product-page__buttons");

			try {
				if (action === "add-to-cart") {
					button.classList.add("unactive");
					toggleProductPageButtonLoaderState(button, true);
				}
				await addToCartUseCase(productId, quantity, button);

				if (action === "add-to-cart") {
					handleProductPageAddedToCart(button);
				} else if (action === "buy-now") {
					const checkoutUrl =
						(window.MOVEAT_THEME && window.MOVEAT_THEME.checkoutUrl) ||
						(window.MOVEAT_API && window.MOVEAT_API.woocommerceCheckoutUrl) ||
						"/oformlenie-zakaza/";
					window.location.href = checkoutUrl;
				}
			} catch (error) {
				// Выводим ошибку в консоль, чтобы не прерывать работу интерфейса.
				console.error(error);
			} finally {
				if (action === "add-to-cart") {
					if (document.body.contains(button)) {
						button.classList.remove("unactive");
					}
					setProductPageLoaderState(buttonsBlock, false);
				}
			}
		});
	});
}

// Инициализирует обработчики добавления в корзину для всех поддерживаемых контекстов.
function initAddToCartHandlers() {
	bindProductCardAddToCart();
	bindProductPageAddToCart();
}

document.addEventListener("DOMContentLoaded", initAddToCartHandlers);
