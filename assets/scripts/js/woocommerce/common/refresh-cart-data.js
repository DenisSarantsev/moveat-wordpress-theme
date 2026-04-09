/*
	Общий модуль обновления UI корзины.
	Используется после любой мутации корзины (удаление, добавление, изменение количества),
	чтобы не дублировать одну и ту же логику в каждом обработчике.
*/
import { syncCartCount } from "./cart-count.js";

// --- Управление лоадером в блоке summary ---
// Показывает или скрывает лоадер в блоке summary корзины.
export function toggleSummaryLoader(isLoading) {
	const loader = document.querySelector(".cart-page__summary-loader");
	if (!loader) return;
	loader.classList.toggle("disabled", !isLoading);
}

// Блокирует или разблокирует кнопку «Оформить заказ» на время обновления корзины.
export function toggleCheckoutButton(isLocked) {
	const button = document.querySelector(
		".cart-page__summary-actions .primary-button",
	);
	if (!button) return;
	button.classList.toggle("unactive", isLocked);
}

// --- Вспомогательные функции форматирования ---
function parsePriceValue(text) {
	const normalized = String(text || "")
		.replace(",", ".")
		.replace(/[^0-9.]/g, "");
	const value = Number(normalized);
	return Number.isFinite(value) ? value : 0;
}

function formatUsd(value) {
	return `$${value.toFixed(2)}`;
}

function formatUah(value) {
	return `${Math.round(value)} грн`;
}

// --- Обновление итогов в блоке summary ---
function updateSummaryFromDom(cartCount = null) {
	const amountMain = document.querySelector(".cart-page__summary-amount");
	const amountSecondary = document.querySelector(
		".cart-page__summary-amount-secondary",
	);
	const countEl = document.querySelector(".cart-page__summary-count");
	const checkoutButton = document.querySelector(
		".cart-page__summary-actions .primary-button",
	);
	const items = document.querySelectorAll("[data-cart-item]");
	if (!amountMain || !countEl) return;

	let totalUsd = 0;
	let totalUah = 0;
	items.forEach((item) => {
		const usdEl = item.querySelector(".cart-page__item-price-main");
		const uahEl = item.querySelector(".cart-page__item-price-secondary");
		totalUsd += parsePriceValue(usdEl ? usdEl.textContent : "");
		totalUah += parsePriceValue(uahEl ? uahEl.textContent : "");
	});

	amountMain.textContent = formatUsd(totalUsd);

	if (amountSecondary) {
		amountSecondary.textContent = formatUah(totalUah);
	}

	const count = Number.isFinite(Number(cartCount))
		? Number(cartCount)
		: items.length;
	countEl.textContent = `${count} товара в корзине`;

	if (checkoutButton) {
		if (count > 0) {
			checkoutButton.removeAttribute("aria-disabled");
		} else {
			checkoutButton.setAttribute("aria-disabled", "true");
		}
	}
}

// --- Отображение пустой корзины ---
function renderEmptyCartState() {
	const list = document.querySelector(".cart-page__list");
	const content = document.querySelector(".cart-page__content");
	if (!content) return;

	if (list) {
		list.remove();
	}

	if (content.querySelector(".cart-page__empty")) {
		return;
	}

	const continueShoppingLink = document.querySelector(
		".cart-page__summary-actions .secondary-button",
	);
	const shopLink = continueShoppingLink
		? continueShoppingLink.getAttribute("href") || "/"
		: "/";
	const empty = document.createElement("div");
	empty.className = "cart-page__empty";
	empty.innerHTML = `
		<h3 class="cart-page__empty-title">Корзина пуста</h3>
		<p class="cart-page__empty-text">Вы ещё не добавили ни одного товара. Перейдите в каталог, чтобы выбрать продукты.</p>
		<a href="${shopLink}" class="primary-button cart-page__empty-action">Перейти в каталог</a>
	`;
	content.appendChild(empty);
}

// --- Публичная функция ---

/**
 * Обновляет весь UI корзины на основе объекта корзины, полученного с сервера.
 * Показывает лоадер в summary на время обновления, затем скрывает его.
 * Синхронизирует счётчик в шапке, итоги в summary и при необходимости
 * рендерит состояние пустой корзины.
 *
 * @param {object|null} cart — объект корзины из API (может быть null)
 */
export async function refreshCartData(cart) {
	// Если cart не передан — пытаемся получить его сами через глобальный API.
	let didFetch = false;
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;

	if (!cart) {
		if (api && api.cart && typeof api.cart.getCart === "function") {
			try {
				didFetch = true;
				toggleSummaryLoader(true);
				cart = await api.cart.getCart();
			} catch (err) {
				cart = null;
			} finally {
				toggleSummaryLoader(false);
			}
		} else {
			cart = null;
		}
	}

	const count = cart && typeof cart.count !== "undefined" ? cart.count : 0;

	// Если API вернул суммы в другом масштабе (например, в центах),
	// попытаемся определить масштаб и привести lineTotal/totalPrice к 'долларному' виду.
	let moneyScale = 1;
	if (cart && Array.isArray(cart.items) && cart.items.length) {
		const sumLineRaw = cart.items.reduce(
			(s, it) => s + (Number(it.lineTotal) || 0),
			0,
		);
		const totalsPriceRaw =
			cart.totals && typeof cart.totals.totalPrice === "number"
				? cart.totals.totalPrice
				: null;

		if (
			totalsPriceRaw &&
			totalsPriceRaw > 0 &&
			sumLineRaw > totalsPriceRaw * 10
		) {
			// возможный коэффициент — округлённое отношение сумм (обычно 100)
			const approx = Math.round(sumLineRaw / totalsPriceRaw);
			if (approx >= 2 && approx <= 1000) {
				moneyScale = approx;
			}
		} else {
			// fallback: если есть позиция с аккуратным целым, кратным 100 и большим 1000 — предполагаем центы
			const anyLarge = cart.items.some((it) => {
				const v = Number(it.lineTotal);
				return (
					Number.isFinite(v) &&
					v >= 1000 &&
					Number.isInteger(v) &&
					v % 100 === 0
				);
			});
			if (anyLarge) moneyScale = 100;
		}

		// Если мы определили масштаб, нормализуем значения внутри cart (для дальнейшей логики)
		if (moneyScale !== 1) {
			cart.items = cart.items.map((it) => ({
				...it,
				lineTotal: (Number(it.lineTotal) || 0) / moneyScale,
			}));
			if (cart.totals && typeof cart.totals.totalPrice === "number") {
				cart.totals.totalPrice = cart.totals.totalPrice / moneyScale;
			}
		}
	}

	// Попробуем определить курс UAH к USD для обновления вторичных цен (грн).
	// Возможные источники: глобальная переменная, summary на странице или существующие цены в карточках.
	let uahRate = null;
	if (
		typeof window.MOVEAT_UAH_RATE !== "undefined" &&
		Number.isFinite(Number(window.MOVEAT_UAH_RATE)) &&
		Number(window.MOVEAT_UAH_RATE) > 0
	) {
		uahRate = Number(window.MOVEAT_UAH_RATE);
	} else {
		const sumUsdEl = document.querySelector(".cart-page__summary-amount");
		const sumUahEl = document.querySelector(
			".cart-page__summary-amount-secondary",
		);
		if (sumUsdEl && sumUahEl) {
			const usd = parsePriceValue(sumUsdEl.textContent);
			const uah = parsePriceValue(sumUahEl.textContent);
			if (usd > 0 && uah > 0) {
				uahRate = uah / usd;
			}
		}

		// fallback: взять первую карточку с обеими ценами
		if (!uahRate) {
			const itemWithBoth = document.querySelector("[data-cart-item]");
			if (itemWithBoth) {
				const usdEl = itemWithBoth.querySelector(".cart-page__item-price-main");
				const uahEl = itemWithBoth.querySelector(
					".cart-page__item-price-secondary",
				);
				if (usdEl && uahEl) {
					const usd = parsePriceValue(usdEl.textContent);
					const uah = parsePriceValue(uahEl.textContent);
					if (usd > 0 && uah > 0) uahRate = uah / usd;
				}
			}
		}
	}

	try {
		// Синхронизируем счётчик в шапке
		syncCartCount(count);

		// Если у нас есть объект корзины — обновляем детали из него (более надёжно),
		// иначе оставляем старое поведение (подсчёт сумм по DOM).
		if (cart && Array.isArray(cart.items)) {
			// Обновляем позиции в DOM: количество и цена (USD)
			cart.items.forEach((ci) => {
				const itemEl = document.querySelector(
					`[data-cart-item-key="${ci.key}"]`,
				);
				if (!itemEl) return;

				// Количество
				const qtyEl = itemEl.querySelector(".cart-page__qty-value");
				if (qtyEl) qtyEl.textContent = String(ci.quantity || 1);

				// Цена в USD
				const priceMain = itemEl.querySelector(".cart-page__item-price-main");
				if (priceMain) priceMain.textContent = formatUsd(ci.lineTotal || 0);

				// Цена в UAH (если известен курс)
				if (uahRate) {
					const priceSecondary = itemEl.querySelector(
						".cart-page__item-price-secondary",
					);
					if (priceSecondary)
						priceSecondary.textContent = formatUah(
							(ci.lineTotal || 0) * uahRate,
						);
				}
			});

			// Обновляем итоговую сумму в summary, если она присутствует в ответе
			if (cart.totals && typeof cart.totals.totalPrice !== "undefined") {
				const amountMain = document.querySelector(".cart-page__summary-amount");
				if (amountMain)
					amountMain.textContent = formatUsd(cart.totals.totalPrice);
				if (uahRate) {
					const amountSecondary = document.querySelector(
						".cart-page__summary-amount-secondary",
					);
					if (amountSecondary)
						amountSecondary.textContent = formatUah(
							cart.totals.totalPrice * uahRate,
						);
				}
			}

			// Обновляем количество товаров в summary
			const countEl = document.querySelector(".cart-page__summary-count");
			if (countEl) countEl.textContent = `${count} товара в корзине`;
		} else {
			// fallback — старое поведение: пересчитать суммы из DOM
			updateSummaryFromDom(count || null);
		}

		if (!cart || !cart.count) {
			renderEmptyCartState();
		}
	} finally {
		// toggleSummaryLoader должен был быть включён только в локальном fetch-case;
		// здесь просто выставляем состояние кнопки оформления в зависимости от count.
		toggleCheckoutButton(!count);
		// Если мы сами делали fetch — лоадер уже выключили выше.
	}
}
