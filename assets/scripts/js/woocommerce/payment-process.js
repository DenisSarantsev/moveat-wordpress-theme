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
	paypal: "ppcp-gateway",
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
		// Записываем короткоживущий cookie с order_id + order_key при нажатии на кнопку "Оплатить".
		// Cookie используется глобальным чекером, чтобы при неудачном платеже вернуть пользователя на /pay-problem/.
		try {
			if (typeof document !== "undefined") {
				console.info("[payment-process] preparing pending order cookie", {
					orderId: orderId,
					orderKey: orderKey,
				});
				var payloadCookie = {
					order_id: parseInt(orderId, 10),
					order_key: orderKey,
				};
				console.debug("[payment-process] payloadCookie:", payloadCookie);
				document.cookie =
					"moveat_pending_order=" +
					encodeURIComponent(JSON.stringify(payloadCookie)) +
					"; path=/; max-age=" +
					10 * 60 +
					"; SameSite=Lax";
			}
		} catch (e) {
			// ignore cookie failures
		}

		const payload = {
			payment_method: gatewaySlug,
			order_key: orderKey,
		};

		// Отправляем запрос на серверный прокси через унифицированный API (createCheckoutApi.payOrder)
		// Так все вызовы проходят через httpClient (единственная точка настройки headers/credentials).
		let result = null;
		try {
			result = await api.checkout.payOrder(orderId, payload);
			console.log(result);
			console.debug("[payment-process] payOrder result:", result);
		} catch (e) {
			console.error("[payment-process] payOrder failed:", e);
			throw e;
		}

		// Сначала проверяем payment_url, который возвращает наш серверный прокси
		const paymentUrl =
			result?.payment_url ||
			result?.redirect_url ||
			result?.payment_result?.redirect_url;
		if (paymentUrl) {
			window.location.href = paymentUrl;
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
