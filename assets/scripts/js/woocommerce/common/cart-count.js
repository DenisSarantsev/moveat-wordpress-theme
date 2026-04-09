// Обновляет индикатор количества товаров в корзине в шапке.
export function syncCartCount(count) {
	const badge = document.querySelector('[data-cart-count]');
	if (!badge) return;

	const parsed = parseInt(String(count ?? ''), 10);
	const nextCount = Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
	badge.textContent = String(nextCount);

	if (nextCount > 0) {
		badge.removeAttribute('hidden');
		return;
	}
	badge.setAttribute('hidden', 'hidden');
}

