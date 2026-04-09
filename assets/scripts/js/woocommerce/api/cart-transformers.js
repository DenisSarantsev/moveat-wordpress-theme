/* 
	Файл преобразует сырые ответы WooCommerce корзины в единый формат для интерфейса. 
*/

// Безопасно приводит значение к числу с резервным значением по умолчанию.
function asNumber(value, fallback = 0) {
	const parsed = Number(value);
	return Number.isFinite(parsed) ? parsed : fallback;
}

/**
 * Определяет коэффициент масштаба валюты на основе поля currency_minor_unit.
 * WooCommerce Store API всегда возвращает суммы в минорных единицах (центах),
 * а currency_minor_unit указывает количество знаков после запятой (обычно 2 → делитель 100).
 */
function detectMoneyScale(rawTotals = {}) {
	const minorUnit = Number(rawTotals.currency_minor_unit);
	if (Number.isFinite(minorUnit) && minorUnit >= 0) {
		return Math.pow(10, minorUnit); // 2 → 100, 0 → 1, 3 → 1000
	}
	return 100; // безопасный fallback
}

// Нормализует блок итоговых сумм корзины. Все суммы приводятся к «долларному» виду.
export function normalizeCartTotals(rawTotals = {}, scale = 1) {
	return {
		currencyCode: rawTotals.currency_code || "USD",
		totalPrice: asNumber(rawTotals.total_price) / scale,
		totalTax: asNumber(rawTotals.total_tax) / scale,
		// Сумма позиций до применения скидок
		subtotal: asNumber(rawTotals.total_items) / scale,
		// Итоговая скидка по всем купонам (всегда положительное число)
		totalDiscount: asNumber(rawTotals.total_discount) / scale,
	};
}

// Нормализует один применённый купон.
function normalizeCoupon(rawCoupon = {}) {
	return {
		code: String(rawCoupon.code || "").toLowerCase(),
		// Тип скидки: 'percent' (процент) или 'fixed' (фиксированная сумма)
		// Store API не возвращает числовой процент — он вычисляется в refresh-cart-data
		// на основе totalDiscount / subtotal из totals корзины
		discountType: rawCoupon.discount_type === "percent" ? "percent" : "fixed",
	};
}

// Нормализует одну позицию товара в корзине.
export function normalizeCartItem(rawItem = {}, scale = 1) {
	return {
		key: rawItem.key || "",
		productId: asNumber(rawItem.id),
		name: rawItem.name || "",
		quantity: asNumber(rawItem.quantity, 1),
		// Цена позиции БЕЗ скидки (для отображения в карточке товара)
		lineSubtotal:
			asNumber(rawItem.totals && rawItem.totals.line_subtotal) / scale,
		// Цена позиции ПОСЛЕ скидки (для внутренних расчётов)
		lineTotal: asNumber(rawItem.totals && rawItem.totals.line_total) / scale,
	};
}

// Нормализует полное состояние корзины.
// Автоматически определяет масштаб валюты и приводит все суммы к «долларному» виду.
export function normalizeCart(rawCart = {}) {
	const rawItems = Array.isArray(rawCart.items) ? rawCart.items : [];
	const scale = detectMoneyScale(rawCart.totals || {});

	const items = rawItems.map((it) => normalizeCartItem(it, scale));
	return {
		items,
		totals: normalizeCartTotals(rawCart.totals || {}, scale),
		count: asNumber(
			rawCart.items_count,
			items.reduce((sum, item) => sum + item.quantity, 0),
		),
		coupons: Array.isArray(rawCart.coupons)
			? rawCart.coupons.map(normalizeCoupon)
			: [],
	};
}
