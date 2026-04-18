// Обновляет индикатор количества товаров в корзине в шапке.
export function syncCartCount(count) {
	const badge = document.querySelector("[data-cart-count]");
	if (!badge) return;

	const parsed = parseInt(String(count ?? ""), 10);
	const nextCount = Number.isFinite(parsed) && parsed >= 0 ? parsed : 0;
	badge.textContent = String(nextCount);

	if (nextCount > 0) {
		badge.removeAttribute("hidden");
		return;
	}
	badge.setAttribute("hidden", "hidden");
}

/* 
	Обновление индикатори корзины в шапке при каждой загрузке
	Попытка получить корзину через глобальный api.cart.getCart() (если есть),
	иначе фолбек на Store API `/wp-json/wc/store/v1/cart`.
*/
export async function refreshCartCount() {
	try {
		let cart = null;

		if (
			typeof window !== "undefined" &&
			window.api &&
			window.api.cart &&
			typeof window.api.cart.getCart === "function"
		) {
			cart = await window.api.cart.getCart();
			console.log(cart);
		} else {
			const res = await fetch("/wp-json/wc/store/v1/cart", {
				method: "GET",
				credentials: "same-origin",
				headers: { Accept: "application/json" },
			});
			if (res.ok) cart = await res.json();
		}

		const count =
			cart?.items_count ??
			(Array.isArray(cart?.items) ? cart.items.length : 0) ??
			0;
		syncCartCount(count);
		return cart;
	} catch (e) {
		// не фатальная ошибка — просто логируем в devtools
		try {
			console.debug("[cart-count] refreshCartCount failed", e);
		} catch (e) {}
		return null;
	}
}

// Запускаем при загрузке страницы, чтобы всегда держать иконку корзины актуальной
if (typeof window !== "undefined") {
	window.addEventListener("DOMContentLoaded", () => {
		refreshCartCount().catch(() => {});
	});
}
