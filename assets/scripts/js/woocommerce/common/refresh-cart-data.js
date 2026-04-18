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

// --- Обновление блока скидки в summary ---

/*
	Обновляет блок скидки [data-discount-block] на основе данных корзины с сервера.
	Если купонов нет — скрывает блок. Если есть — показывает зачёркнутую сумму
	без скидки и чип со скидкой.	

	Для процентного купона процент вычисляется как totalDiscount / subtotal * 100,
	так как Store API не возвращает числовой процент в объекте купона.

	Для фиксированного купона показывается сумма скидки в долларах.	
	@param {object|null} cart — нормализованный объект корзины (суммы уже в долларах)
 */
function updateDiscountBlock(cart) {
	const block = document.querySelector("[data-discount-block]");
	if (!block) return;

	const priceEl = block.querySelector(
		".cart-page__summary-amount-discount_price",
	);
	const discountEl = block.querySelector(
		".cart-page__summary-amount-discount_discount",
	);

	// Нет купонов — скрываем весь блок
	const hasCoupons =
		cart && Array.isArray(cart.coupons) && cart.coupons.length > 0;
	if (!hasCoupons) {
		block.classList.add("hidden");
		return;
	}

	// Показываем блок
	block.classList.remove("hidden");

	const subtotal = Number(cart.totals && cart.totals.subtotal) || 0;
	const totalDiscount = Number(cart.totals && cart.totals.totalDiscount) || 0;

	// Зачёркнутая цена — сумма до скидки
	if (priceEl) {
		priceEl.textContent = formatUsd(subtotal);
	}

	// Чип скидки
	if (discountEl) {
		const coupon = cart.coupons[0];
		if (coupon) {
			if (coupon.discountType === "percent") {
				// Вычисляем процент из фактических сумм: (скидка / сумма до скидки) * 100
				const percent =
					subtotal > 0 ? Math.round((totalDiscount / subtotal) * 100) : 0;
				discountEl.textContent = `-${percent}%`;
			} else {
				// Фиксированная скидка — показываем сумму в долларах
				discountEl.textContent = `-${formatUsd(totalDiscount)}`;
			}
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
/*
	Обновляет весь UI корзины на основе объекта корзины, полученного с сервера.
	Показывает лоадер в summary на время обновления, затем скрывает его.
	Синхронизирует счётчик в шапке, итоги в summary и при необходимости
	рендерит состояние пустой корзины.
	@param {object|null} cart — объект корзины из API (может быть null)
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
		// Обновляем блок скидки (зачёркнутая цена + чип)
		updateDiscountBlock(cart);

		/*
			Если у нас есть объект корзины — обновляем детали из него (более надёжно),
			иначе оставляем старое поведение (подсчёт сумм по DOM).
		*/
		if (cart && Array.isArray(cart.items)) {
			cart.items.forEach((ci) => {
				const itemEl = document.querySelector(
					`[data-cart-item-key="${ci.key}"]`,
				);
				if (!itemEl) return;

				// Количество — обновляем всегда
				const qtyEl = itemEl.querySelector(".cart-page__qty-value");
				if (qtyEl) qtyEl.textContent = String(ci.quantity || 1);

				// Цена позиции (USD) — используем lineSubtotal (без скидки) для отображения в карточке
				const priceMain = itemEl.querySelector(".cart-page__item-price-main");
				if (priceMain) priceMain.textContent = formatUsd(ci.lineSubtotal || 0);

				// Цена позиции (UAH)
				if (uahRate) {
					const priceSecondary = itemEl.querySelector(
						".cart-page__item-price-secondary",
					);
					if (priceSecondary)
						priceSecondary.textContent = formatUah(
							(ci.lineSubtotal || 0) * uahRate,
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
