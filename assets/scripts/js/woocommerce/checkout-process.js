import { showSystemMessage } from "../modules/system-message.js";

// ─── DOM-узлы ─────────────────────────────────────────────────────────────────

const form = document.getElementById("orderForm");
const submitBtn = document.getElementById("orderSubmit");

// ─── Поля формы ───────────────────────────────────────────────────────────────

const REQUIRED_FIELDS = ["firstName", "email", "phone"];

// ─── Валидация ────────────────────────────────────────────────────────────────

function isFormValid() {
	return REQUIRED_FIELDS.every((id) => {
		const el = document.getElementById(id);
		return el && el.value.trim() !== "";
	});
}

function updateSubmitState() {
	if (!submitBtn) return;

	if (isFormValid()) {
		submitBtn.classList.remove("unactive");
		submitBtn.disabled = false;
	} else {
		submitBtn.classList.add("unactive");
		submitBtn.disabled = true;
	}
}

// ─── Состояния кнопки ─────────────────────────────────────────────────────────

function setLoading() {
	if (!submitBtn) return;
	submitBtn.classList.add("loading");
	submitBtn.disabled = true;
}

function setIdle() {
	if (!submitBtn) return;
	submitBtn.classList.remove("loading");

	if (isFormValid()) {
		submitBtn.disabled = false;
	}
}

// ─── Отправка заказа ──────────────────────────────────────────────────────────

async function handleSubmit(e) {
	e.preventDefault();

	if (!isFormValid()) return;

	const firstName = document.getElementById("firstName").value.trim();
	const lastName = document.getElementById("lastName")?.value.trim() ?? "";
	const email = document.getElementById("email").value.trim();
	const phone = document.getElementById("phone").value.trim();

	const billingAddress = {
		first_name: firstName,
		last_name: lastName || "—",
		email: email,
		phone: phone,
		// WooCommerce требует эти поля — передаём заглушки
		address_1: "—",
		city: "—",
		postcode: "00000",
		country: "UA",
	};

	// "mono_gateway" — Monobank (Plata by Mono).
	const paymentMethod = "mono_gateway";

	setLoading();

	try {
		const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
		if (!api) throw new Error("API не инициализирован");

		const result = await api.checkout.placeOrder({
			billing_address: billingAddress,
			payment_method: paymentMethod,
		});

		console.log("[checkout-process] placeOrder result:", result);

		// Если WooCommerce вернул payment_result.redirect_url — переходим на оплату
		const redirectUrl = result?.payment_result?.redirect_url;
		if (redirectUrl) {
			window.location.href = redirectUrl;
			return;
		}

		// Monobank и похожие шлюзы не возвращают redirect_url через Store API —
		// редиректим на твою страницу оплаты с order_id и order_key в URL.
		if (result?.order_id && result?.order_key) {
			const base =
				window.MOVEAT_WOO_API_CONFIG?.baseUrl || window.location.origin;
			window.location.href = `${base}/order-pay/?order_id=${result.order_id}&order_key=${result.order_key}`;
			return;
		}

		showSystemMessage("Заказ оформлен!", "success");
	} catch (err) {
		console.error("[checkout-process] placeOrder error:", err);
		const message =
			err?.message ?? "Не удалось оформить заказ. Попробуйте позже.";
		showSystemMessage(message, "error");
	} finally {
		setIdle();
	}
}

// ─── Инициализация ────────────────────────────────────────────────────────────

export function initCheckout() {
	if (!form) return; // не страница оформления заказа

	// Обновлять состояние кнопки при вводе
	REQUIRED_FIELDS.forEach((id) => {
		const el = document.getElementById(id);
		el?.addEventListener("input", updateSubmitState);
	});

	// Начальная проверка (например, если браузер заполнил форму автоматически)
	updateSubmitState();

	form.addEventListener("submit", handleSubmit);
}
