/* 
	Файл преобразует сырые ответы WooCommerce корзины в единый формат для интерфейса. 
*/

// Безопасно приводит значение к числу с резервным значением по умолчанию.
function asNumber(value, fallback = 0) {
	const parsed = Number(value);
	return Number.isFinite(parsed) ? parsed : fallback;
}

// Нормализует блок итоговых сумм корзины.
export function normalizeCartTotals(rawTotals = {}) {
	return {
		currencyCode: rawTotals.currency_code || 'USD',
		totalPrice: asNumber(rawTotals.total_price),
		totalTax: asNumber(rawTotals.total_tax),
	};
}

// Нормализует одну позицию товара в корзине.
export function normalizeCartItem(rawItem = {}) {
	return {
		key: rawItem.key || '',
		productId: asNumber(rawItem.id),
		name: rawItem.name || '',
		quantity: asNumber(rawItem.quantity, 1),
		lineTotal: asNumber(rawItem.totals && rawItem.totals.line_total),
	};
}

// Нормализует полное состояние корзины.
export function normalizeCart(rawCart = {}) {
	const items = Array.isArray(rawCart.items) ? rawCart.items.map(normalizeCartItem) : [];
	return {
		items,
		totals: normalizeCartTotals(rawCart.totals || {}),
		count: asNumber(rawCart.items_count, items.reduce((sum, item) => sum + item.quantity, 0)),
		coupons: Array.isArray(rawCart.coupons) ? rawCart.coupons : [],
	};
}

