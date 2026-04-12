/*
	Модуль страницы оплаты заказа (/order-pay/).
	Отвечает за:
	  - переключение методов оплаты (PayPal / Банковская карта);
	  - активацию кнопки «Оплатить» при выборе метода + согласии с условиями;
	  - инициацию оплаты через WooCommerce Store API и редирект на платёжную систему.
*/

import { showSystemMessage } from "../modules/system-message.js";

// ─── DOM-узлы ─────────────────────────────────────────────────────────────────

const methodButtons = document.querySelectorAll(".payment-page__method-button");
const agreeCheckbox = document.getElementById("agreeTerms");
const submitButton = document.getElementById("paymentSubmit");

// ─── Маппинг data-method → WooCommerce payment gateway slug ──────────────────

const METHOD_SLUG_MAP = {
	card: "mono_gateway",
	paypal: "paypal",
};

// ─── Состояние ────────────────────────────────────────────────────────────────

let selectedMethod = null; // значение data-method выбранной кнопки

// ─── Параметры заказа из URL ──────────────────────────────────────────────────

function getOrderParams() {
	const params = new URLSearchParams(window.location.search);
	const orderId = params.get("order_id");
	const orderKey = params.get("order_key");
	return { orderId, orderKey };
}

// ─── Состояние кнопки «Оплатить» ─────────────────────────────────────────────

function updateSubmitState() {
	if (!submitButton) return;
	const canSubmit = selectedMethod !== null && agreeCheckbox?.checked;
	submitButton.disabled = !canSubmit;
	submitButton.classList.toggle("unactive", !canSubmit);
}

// ─── Состояние загрузки ───────────────────────────────────────────────────────

function setLoading(isLoading) {
	if (!submitButton) return;
	submitButton.classList.toggle("loading", isLoading);
	submitButton.disabled = isLoading;
}

// ─── Переключение метода оплаты ───────────────────────────────────────────────

function initMethodSelection() {
	methodButtons.forEach((button) => {
		button.addEventListener("click", () => {
			methodButtons.forEach((btn) => {
				btn.classList.remove("is-selected");
				btn.setAttribute("aria-pressed", "false");
			});
			button.classList.add("is-selected");
			button.setAttribute("aria-pressed", "true");
			selectedMethod = button.getAttribute("data-method");
			updateSubmitState();
		});
	});
}

// ─── Оплата ───────────────────────────────────────────────────────────────────

async function handlePay() {
	if (!selectedMethod || !agreeCheckbox?.checked) return;

	const { orderId, orderKey } = getOrderParams();
	if (!orderId || !orderKey) {
		showSystemMessage(
			"Не удалось определить заказ. Проверьте ссылку.",
			"error",
		);
		return;
	}

	const gatewaySlug = METHOD_SLUG_MAP[selectedMethod] ?? selectedMethod;

	const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
	if (!api) {
		showSystemMessage("API не инициализирован.", "error");
		return;
	}

	setLoading(true);

	try {
		// Инициируем оплату существующего заказа через /checkout/{order_id}
		const result = await api.checkout.payOrder(orderId, {
			payment_method: gatewaySlug,
			key: orderKey,
		});

		console.log("[payment-process] pay result:", result);

		// Monobank и другие шлюзы возвращают redirect_url
		const redirectUrl = result?.payment_result?.redirect_url;
		if (redirectUrl) {
			window.location.href = redirectUrl;
			return;
		}

		showSystemMessage(
			"Не удалось получить ссылку на оплату. Попробуйте позже.",
			"error",
		);
	} catch (err) {
		console.error("[payment-process] pay error:", err);
		showSystemMessage(err?.message ?? "Ошибка при инициации оплаты.", "error");
	} finally {
		setLoading(false);
		updateSubmitState();
	}
}

// ─── Инициализация ────────────────────────────────────────────────────────────

export function initPaymentProcess() {
	if (!submitButton) return; // не страница оплаты

	initMethodSelection();

	agreeCheckbox?.addEventListener("change", updateSubmitState);
	submitButton.addEventListener("click", handlePay);
}
