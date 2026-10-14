/*
	Модалка оплаты частями на странице товара.

	Открывает/закрывает модалку main.js (.js-modal-open → .js-modal), здесь —
	только данные: сумма заказа = корзина + текущий товар (если его ещё нет
	в корзине), по ней выбирается состояние модалки. Страница на проде
	кэшируется, поэтому корзину читаем из Store API: при загрузке, после
	добавления товара в корзину и при каждом открытии модалки.

	«Оформить» кладёт товар в корзину (если его там нет) и ведёт на оформление
	с ?payment=parts — дальше этот параметр доходит до страницы оплаты
	и там сразу выбирается «Оплата частями».
*/

const BELOW_CLASS = "product-modal--below-limit";
const ABOVE_CLASS = "product-modal--above-limit";

function getCartApi() {
	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	return api && api.cart ? api.cart : null;
}

function asNumber(value) {
	const parsed = Number(value);
	return Number.isFinite(parsed) ? parsed : 0;
}

// «3 000 ₴» — как number_format( $x, 0, '.', ' ' ) в PHP.
function formatUah(amount) {
	return `${String(Math.round(amount)).replace(/\B(?=(\d{3})+(?!\d))/g, " ")} ₴`;
}

function readConfig(modal) {
	const { dataset } = modal;
	return {
		productId: asNumber(dataset.productId),
		priceUsd: asNumber(dataset.priceUsd),
		rate: asNumber(dataset.uahRate),
		minUah: asNumber(dataset.minUah),
		maxUah: asNumber(dataset.maxUah),
		checkoutUrl: dataset.checkoutUrl || "",
	};
}

function isProductInCart(cart, productId) {
	return cart.items.some(
		(item) => item.productId === productId && item.quantity > 0,
	);
}

// Сумма будущего заказа в гривнах: корзина + текущий товар, если его там нет.
function calcTotalUah(cart, config) {
	const cartUsd = cart.totals.totalPrice;
	const productUsd = isProductInCart(cart, config.productId) ? 0 : config.priceUsd;
	// Округление — как в moveat_instalments_amount_fits().
	return Math.round((cartUsd + productUsd) * config.rate * 100) / 100;
}

function getState(totalUah, config) {
	if (config.minUah > 0 && totalUah < config.minUah) return "below";
	if (config.maxUah > 0 && totalUah > config.maxUah) return "above";
	return "ok";
}

function render(modal, config, totalUah) {
	const state = getState(totalUah, config);
	modal.classList.toggle(BELOW_CLASS, state === "below");
	modal.classList.toggle(ABOVE_CLASS, state === "above");

	const progress = modal.querySelector("[data-instalments-progress]");
	const total = modal.querySelector("[data-instalments-total]");
	const missing = modal.querySelector("[data-instalments-missing]");

	if (progress && config.minUah > 0) {
		const percent = Math.min(100, (totalUah / config.minUah) * 100);
		progress.style.width = `${Math.round(percent * 100) / 100}%`;
	}
	if (total) total.textContent = formatUah(totalUah);
	if (missing) {
		missing.textContent = formatUah(Math.max(0, config.minUah - totalUah));
	}

	return state;
}

export function initInstalmentsModal() {
	const modal = document.querySelector("[data-instalments-modal]");
	if (!modal) return;

	const config = readConfig(modal);
	const submit = modal.querySelector("[data-instalments-submit]");
	let state = getState(config.priceUsd * config.rate, config);
	let requestId = 0;
	let isSubmitting = false;

	// Последний ответ побеждает: старый запрос не перетрёт свежее состояние.
	async function refresh() {
		const cartApi = getCartApi();
		if (!cartApi) return null;

		const current = ++requestId;
		try {
			const cart = await cartApi.getCart();
			if (current === requestId) {
				state = render(modal, config, calcTotalUah(cart, config));
			}
			return cart;
		} catch (error) {
			console.error("[instalments-modal] Не удалось получить корзину", error);
			return null;
		}
	}

	async function handleSubmit(event) {
		event.preventDefault();
		if (isSubmitting || state !== "ok") return;

		const cartApi = getCartApi();
		if (!cartApi || !config.checkoutUrl) return;

		isSubmitting = true;
		submit.setAttribute("aria-disabled", "true");
		submit.classList.add("is-loading");

		try {
			// Корзину проверяем заново: её могли изменить в другой вкладке.
			const cart = await cartApi.getCart();
			state = render(modal, config, calcTotalUah(cart, config));
			if (state !== "ok") return;

			if (!isProductInCart(cart, config.productId)) {
				await cartApi.addItem(config.productId, 1);
			}
			window.location.href = config.checkoutUrl;
		} catch (error) {
			console.error("[instalments-modal] Не удалось оформить", error);
		} finally {
			isSubmitting = false;
			submit.removeAttribute("aria-disabled");
			submit.classList.remove("is-loading");
		}
	}

	submit?.addEventListener("click", handleSubmit);

	document.addEventListener("click", (event) => {
		const opener = event.target instanceof Element
			? event.target.closest('.js-modal-open[data-modal="mono-installments"]')
			: null;
		if (opener) refresh();
	});

	// Товар добавили в корзину кнопками страницы (add-to-cart.js шлёт это событие).
	if (window.jQuery) {
		window.jQuery(document.body).on("added_to_cart", refresh);
	}

	refresh();
}
