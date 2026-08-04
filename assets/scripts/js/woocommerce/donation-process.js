/*
	Модуль страницы донатов (тема WordPress).
	Создаёт заказ с fee-позицией, инициирует оплату через pay-order и редирект на шлюз.

	UI формы и прелоадер кнопки — в бандле moveat-new (donations.ts),
	через window.MOVEAT_DONATIONS.setSubmitLoading.
*/

import { showSystemMessage } from "../modules/system-message.js";

const roundDonationAmount = (value) => {
	if (!Number.isFinite(value) || value <= 0) return 0;
	return Math.round(value * 100) / 100;
};

const getMinDonationAmount = () => {
	const root = document.querySelector("[data-donations-min-amount]");
	const parsed = Number(root?.getAttribute("data-donations-min-amount"));
	return Number.isFinite(parsed) && parsed > 0
		? roundDonationAmount(parsed)
		: 5;
};

const DONATION_GATEWAY_MAP = {
	paypal: "ppcp-gateway",
	mono_gateway: "mono_gateway",
};

let isSubmitting = false; // защита от двойного сабмита (вкладки, ретраи, быстрый клик)

const submitButton = document.querySelector("[data-donations-submit]");
const methodsContainer = document.querySelector("[data-donations-methods]");
const customAmountInput = document.querySelector(
	"[data-donations-custom-amount]",
);
const firstNameInput = document.querySelector("[data-donations-firstname]");
const lastNameInput = document.querySelector("[data-donations-lastname]");
const phoneInput = document.querySelector("[data-donations-phone]");
const emailInput = document.querySelector("[data-donations-email]");
const agreeCheckbox = document.querySelector("[data-donations-agree]");

const resolveDonationGateway = (method) =>
	DONATION_GATEWAY_MAP[method] ?? method;

const setPendingOrderCookie = (orderId, orderKey) => {
	try {
		if (typeof document === "undefined") return;

		const payloadCookie = {
			order_id: parseInt(orderId, 10),
			order_key: orderKey,
		};

		document.cookie =
			"moveat_pending_order=" +
			encodeURIComponent(JSON.stringify(payloadCookie)) +
			"; path=/; max-age=" +
			10 * 60 +
			"; SameSite=Lax";
	} catch {
		// ignore cookie failures
	}
};

const getPaymentUrl = (result) =>
	result?.payment_url ||
	result?.redirect_url ||
	result?.payment_result?.redirect_url ||
	null;

const getSelectedAmount = () => {
	const selectedButton = document.querySelector(
		"[data-donations-amounts] [data-amount].is-selected",
	);

	if (selectedButton) {
		return roundDonationAmount(Number(selectedButton.dataset.amount));
	}

	const customValue = customAmountInput?.value.trim() ?? "";
	if (!customValue) return 0;

	return roundDonationAmount(Number(customValue));
};

const getSelectedMethod = () => {
	const selectedButton = methodsContainer?.querySelector(
		"[data-method].is-selected",
	);

	return selectedButton?.dataset.method ?? null;
};

const setLoading = (isLoading) => {
	if (window.MOVEAT_DONATIONS?.setSubmitLoading) {
		window.MOVEAT_DONATIONS.setSubmitLoading(isLoading);
		return;
	}

	if (!submitButton) return;

	submitButton.classList.toggle("is-loading", isLoading);
	submitButton.disabled = isLoading;
	submitButton.classList.toggle("unactive", isLoading);
	submitButton.setAttribute("aria-busy", isLoading ? "true" : "false");
};

const getPhoneForOrder = () => {
	if (window.MOVEAT_DONATIONS?.getPhoneNumber) {
		return window.MOVEAT_DONATIONS.getPhoneNumber();
	}

	return phoneInput?.value.trim() ?? "";
};

const handleDonationSubmit = async () => {
	if (!submitButton || submitButton.disabled) return;

	if (isSubmitting) return; // один платёж за раз
	if (window.MOVEAT_DONATIONS?.getIsSubmitting?.()) return;

	const method = getSelectedMethod();
	const amount = getSelectedAmount();
	const minDonationAmount = getMinDonationAmount();
	const firstName = firstNameInput?.value.trim() ?? "";
	const lastName = lastNameInput?.value.trim() ?? "";
	const phone = getPhoneForOrder();
	const email = emailInput?.value.trim() ?? "";
	const gatewaySlug = resolveDonationGateway(method);

	if (
		!method ||
		amount < minDonationAmount ||
		firstName.length < 2 ||
		!phone ||
		!email ||
		!agreeCheckbox?.checked
	) {
		return;
	}

	const api = window.MOVEAT_API?.woocommerce;
	if (!api?.httpClient?.post || !api?.checkout?.payOrder) {
		showSystemMessage("error", "API не инициализирован.");
		return;
	}

	isSubmitting = true;
	setLoading(true);

	try {
		const orderResult = await api.httpClient.post(
			"/wp-json/my-api/v1/create-donation-order",
			{
				amount,
				first_name: firstName,
				last_name: lastName,
				phone,
				email,
				payment_method: gatewaySlug,
			},
		);

		if (!orderResult?.id || !orderResult?.order_key) {
			showSystemMessage(
				"error",
				"Не удалось создать заказ доната. Попробуйте позже.",
			);
			return;
		}

		setPendingOrderCookie(orderResult.id, orderResult.order_key);

		const payResult = await api.checkout.payOrder(orderResult.id, {
			payment_method: gatewaySlug,
			order_key: orderResult.order_key,
		});

		const paymentUrl = getPaymentUrl(payResult);
		if (paymentUrl) {
			window.location.href = paymentUrl;
			return;
		}

		showSystemMessage(
			"error",
			"Не удалось получить ссылку на оплату. Попробуйте позже.",
		);
	} catch (err) {
		console.error("[donation-process] submit error:", err);
		showSystemMessage(
			"error",
			err?.message ?? "Ошибка при инициации оплаты доната.",
		);
	} finally {
		isSubmitting = false;
		setLoading(false);
	}
};

export const initDonationProcess = () => {
	if (!submitButton) return;

	submitButton.addEventListener("click", handleDonationSubmit);
};
