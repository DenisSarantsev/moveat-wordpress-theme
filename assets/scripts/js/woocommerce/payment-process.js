/*
	Модуль страницы оплаты заказа (/order-pay/).
	Отвечает за:
	  - переключение методов оплаты (PayPal / Банковская карта / Оплата частями monobank);
	  - выбор количества платежей для оплаты частями;
	  - модалки оплаты частями (ожидание / ошибка / успех) и опрос статуса заявки;
	  - активацию кнопки «Оплатить» при выборе метода + согласии с условиями;
	  - инициацию оплаты через WooCommerce Store API и редирект на платёжную систему.
*/

import { showSystemMessage } from "../modules/system-message.js";

// ─── DOM-узлы ─────────────────────────────────────────────────────────────────

const methodButtons = document.querySelectorAll(".payment-page__method-button");
const agreeCheckbox = document.getElementById("agreeTerms");
const submitButton = document.getElementById("paymentSubmit");
const installmentsButton = document.querySelector("[data-installments]");
const termChips = installmentsButton
	? Array.from(installmentsButton.querySelectorAll("[data-installment-term]"))
	: [];
// Сроки и подпись под ними видны, только пока выбрана оплата частями.
const installmentsPanels = installmentsButton
	? Array.from(
			installmentsButton.querySelectorAll(
				".payment-page__method-terms, .payment-page__method-terms-note",
			),
		)
	: [];

// Модалки оплаты частями (templates/order-pay.php): видна та, что в data-checkout-state.
const checkout = document.querySelector("[data-checkout]");
const checkoutErrorText = document.getElementById("checkoutErrorText");
const checkoutErrorCodeRow = checkout?.querySelector("[data-checkout-error-code-row]");
const checkoutErrorCode = checkout?.querySelector("[data-checkout-error-code]");
// Текст из вёрстки — на случай, если банк/сервер не прислали причину.
const CHECKOUT_ERROR_DEFAULT = checkoutErrorText?.textContent.trim() ?? "";

// ─── Анимации ─────────────────────────────────────────────────────────────────
// GSAP берём из бандла вёрстки (main.js выставляет window.gsap). Нет GSAP или
// пользователь просит меньше движения — всё открывается/закрывается мгновенно.

const reducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)");

function getGsap() {
	if (reducedMotion?.matches) return null;
	return window.gsap ?? null;
}

const PANEL_OPEN_DURATION = 0.4;
const PANEL_CLOSE_DURATION = 0.25;
const MODAL_OPEN_DURATION = 0.6;
const MODAL_CLOSE_DURATION = 0.3;
const MODAL_OFFSET_Y = 40; // модалка выезжает снизу на столько пикселей

let installmentsOpen = false;
let checkoutCloseTween = null;

// Опрос статуса заявки: у клиента 15 минут на подтверждение в приложении.
// Срок считаем по часам, а не числом опросов: каждый опрос ещё ходит в банк,
// а на телефоне таймеры замирают, пока клиент в приложении monobank.
const POLL_INTERVAL = 5000;
const INSTALMENTS_TIMEOUT_MS = 15 * 60 * 1000;
const SUCCESS_REDIRECT_DELAY = 1500;
const INSTALMENTS_TIMEOUT_TEXT =
	"Мы не дождались ответа банка. Если вы подтвердили покупку в приложении monobank — напишите нам, заказ уже создан.";

// ─── Маппинг data-method → WooCommerce payment gateway slug ──────────────────

const METHOD_SLUG_MAP = {
	card: "mono_gateway",
	paypal: "ppcp-gateway",
	parts: "cc_payparts", // CatCode «Оплата частинами», банк monobank
};

// ─── Состояние ────────────────────────────────────────────────────────────────

let selectedMethod = null; // значение data-method выбранной кнопки
let isSubmitting = false; // защита от двойного сабмита (вкладки, ретраи, быстрый клик)

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

function selectMethod(button) {
	methodButtons.forEach((btn) => {
		btn.classList.remove("is-selected");
		btn.setAttribute("aria-pressed", "false");
	});
	button.classList.add("is-selected");
	button.setAttribute("aria-pressed", "true");
	selectedMethod = button.getAttribute("data-method");
	toggleInstallments(button === installmentsButton);
	updateSubmitState();
}

/*
	Сроки и подпись — отдельные дети кнопки-колонки с gap между ними. Поэтому
	плавно ведём не только высоту и отступ, но и marginTop от -gap до 0, иначе
	при раскрытии пустой gap появлялся бы рывком.
*/
function toggleInstallments(isOpen) {
	if (!installmentsButton || isOpen === installmentsOpen) return;
	installmentsOpen = isOpen;
	installmentsButton.setAttribute("aria-expanded", String(isOpen));

	const gsap = getGsap();
	if (!gsap) {
		installmentsPanels.forEach((panel) => {
			panel.hidden = !isOpen;
		});
		return;
	}

	const gap = parseFloat(getComputedStyle(installmentsButton).rowGap) || 0;
	gsap.killTweensOf(installmentsPanels);

	installmentsPanels.forEach((panel) => {
		if (isOpen) {
			// Сбрасываем то, что оставило прерванное сворачивание, иначе from()
			// возьмёт за конечную высоту 0 и блок не раскроется.
			gsap.set(panel, { clearProps: "height,paddingTop,marginTop,opacity,overflow" });
			panel.hidden = false;
			gsap.from(panel, {
				height: 0,
				paddingTop: 0,
				marginTop: -gap,
				opacity: 0,
				overflow: "hidden",
				duration: PANEL_OPEN_DURATION,
				ease: "power2.out",
				clearProps: "height,paddingTop,marginTop,opacity,overflow",
			});
			return;
		}

		gsap.to(panel, {
			height: 0,
			paddingTop: 0,
			marginTop: -gap,
			opacity: 0,
			overflow: "hidden",
			duration: PANEL_CLOSE_DURATION,
			ease: "power2.in",
			onComplete: () => {
				panel.hidden = true;
				gsap.set(panel, {
					clearProps: "height,paddingTop,marginTop,opacity,overflow",
				});
			},
		});
	});
}

function initMethodSelection() {
	methodButtons.forEach((button) => {
		button.addEventListener("click", () => selectMethod(button));
	});

	// Чипсы срока лежат внутри кнопки рассрочки: клик по сроку и выбирает
	// оплату частями, и задаёт количество платежей.
	termChips.forEach((chip) => {
		chip.addEventListener("click", (event) => {
			event.stopPropagation();
			selectMethod(installmentsButton);
			selectTerm(chip);
		});
	});

	// По умолчанию выбран первый способ в списке.
	if (methodButtons[0]) selectMethod(methodButtons[0]);
}

// ─── Срок оплаты частями ──────────────────────────────────────────────────────
// Варианты и платёж в месяц (в гривнах) рендерит PHP из настроек плагина CatCode.

function selectTerm(current) {
	termChips.forEach((chip) => {
		const isCurrent = chip === current;
		chip.classList.toggle("is-active", isCurrent);
		chip.setAttribute("aria-checked", String(isCurrent));
	});
}

// Клавиатура: пока фокус на кнопке рассрочки, срок переключается стрелками.
function initTermKeyboard() {
	if (!installmentsButton || termChips.length < 2) return;

	installmentsButton.addEventListener("keydown", (event) => {
		const step =
			event.key === "ArrowRight" || event.key === "ArrowDown"
				? 1
				: event.key === "ArrowLeft" || event.key === "ArrowUp"
					? -1
					: 0;
		if (step === 0) return;

		event.preventDefault();
		const activeIndex = termChips.findIndex((chip) =>
			chip.classList.contains("is-active"),
		);
		const nextIndex = (activeIndex + step + termChips.length) % termChips.length;

		selectMethod(installmentsButton);
		selectTerm(termChips[nextIndex]);
	});
}

// ─── Оплата ───────────────────────────────────────────────────────────────────

async function handlePay() {
	if (!selectedMethod || !agreeCheckbox?.checked) return;

	// Один платёж за раз: отсекаем повторный вызов до завершения текущего.
	if (isSubmitting) return;

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

	// Оплата частями: ссылки на банк нет, клиент остаётся на странице и
	// подтверждает покупку в приложении — отдельный поток с модалками.
	if (selectedMethod === "parts") {
		payInstalments(api, orderId, orderKey);
		return;
	}

	isSubmitting = true;
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
		isSubmitting = false;
		setLoading(false);
		updateSubmitState();
	}
}

// ─── Оплата частями: модалки ──────────────────────────────────────────────────

const getCheckoutOverlay = () => checkout?.querySelector("[data-checkout-overlay]");
const getCheckoutModal = (state) =>
	checkout?.querySelector(`[data-checkout-modal="${state}"]`);

/*
	Показывает модалку нужного состояния. Если слой был скрыт — проявляем фон;
	сама модалка выезжает снизу с отпружиниванием (back.out) и при открытии,
	и при смене состояния (ожидание → ошибка/успех).
*/
function setCheckoutState(state) {
	if (!checkout) return;

	const gsap = getGsap();
	const wasHidden = checkout.hidden;

	// Открываем, пока доигрывает закрытие (например, «Попробовать снова»):
	// отменяем его, иначе в конце оно спрятало бы новое окно.
	if (checkoutCloseTween) {
		checkoutCloseTween.kill();
		checkoutCloseTween = null;
	}

	checkout.setAttribute("data-checkout-state", state);
	checkout.hidden = false;
	document.body.style.overflow = "hidden";

	if (!gsap) return;

	const overlay = getCheckoutOverlay();
	const modal = getCheckoutModal(state);

	if (overlay && wasHidden) {
		gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: "power1.out" });
	}
	if (modal) {
		gsap.killTweensOf(modal);
		gsap.fromTo(
			modal,
			{ y: MODAL_OFFSET_Y, opacity: 0 },
			{
				y: 0,
				opacity: 1,
				duration: MODAL_OPEN_DURATION,
				ease: "back.out(1.7)",
				clearProps: "transform,opacity",
			},
		);
	}
}

// Закрытие быстрее открытия: модалка уходит вниз и гаснет вместе с фоном.
function closeCheckout() {
	if (!checkout || checkout.hidden || checkoutCloseTween) return Promise.resolve();

	isSubmitting = false;
	setLoading(false);
	updateSubmitState();

	const finish = () => {
		checkoutCloseTween = null;
		checkout.hidden = true;
		document.body.style.overflow = "";
	};

	const gsap = getGsap();
	if (!gsap) {
		finish();
		return Promise.resolve();
	}

	const state = checkout.getAttribute("data-checkout-state");
	const targets = [getCheckoutModal(state), getCheckoutOverlay()].filter(Boolean);

	return new Promise((resolve) => {
		checkoutCloseTween = gsap
			.timeline({
				onComplete: () => {
					gsap.set(targets, { clearProps: "transform,opacity" });
					finish();
					resolve();
				},
				// Закрытие прервали новым открытием — отпускаем ожидающих.
				onInterrupt: resolve,
			})
			.to(getCheckoutModal(state), {
				y: MODAL_OFFSET_Y / 2,
				opacity: 0,
				duration: MODAL_CLOSE_DURATION,
				ease: "power2.in",
			})
			.to(getCheckoutOverlay(), { opacity: 0, duration: MODAL_CLOSE_DURATION }, 0);
	});
}

function showCheckoutError(text, code = "") {
	if (checkoutErrorText) checkoutErrorText.textContent = text || CHECKOUT_ERROR_DEFAULT;
	if (checkoutErrorCode) checkoutErrorCode.textContent = code;
	if (checkoutErrorCodeRow) checkoutErrorCodeRow.hidden = !code;
	setCheckoutState("error");
}

// Закрыть можно только окно ошибки: пока ждём банк или уже уходим на «Спасибо» — нельзя.
function initCheckoutControls() {
	if (!checkout) return;

	const closeIfError = () => {
		if (checkout.getAttribute("data-checkout-state") === "error") closeCheckout();
	};

	checkout
		.querySelectorAll("[data-checkout-close], [data-checkout-overlay]")
		.forEach((el) => el.addEventListener("click", closeIfError));

	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape" && !checkout.hidden) closeIfError();
	});

	checkout.querySelector("[data-checkout-retry]")?.addEventListener("click", () => {
		closeCheckout().then(handlePay);
	});
}

// ─── Оплата частями: заявка и ожидание ответа банка ───────────────────────────

function getSelectedTerm() {
	const active = termChips.find((chip) => chip.classList.contains("is-active"));
	return Number(active?.getAttribute("data-installment-term")) || 0;
}

/*
	Ждёт ms миллисекунд, но просыпается раньше, когда клиент возвращается на
	вкладку: пока он подтверждал покупку в приложении, браузер стоял в фоне,
	и ответ банка нужно узнать сразу, а не через очередной интервал.
*/
function waitOrVisible(ms) {
	return new Promise((resolve) => {
		const done = () => {
			window.clearTimeout(timer);
			document.removeEventListener("visibilitychange", onVisible);
			resolve();
		};
		const onVisible = () => {
			if (document.visibilityState === "visible") done();
		};
		const timer = window.setTimeout(done, ms);
		document.addEventListener("visibilitychange", onVisible);
	});
}

/*
	Опрашивает эндпоинт плагина CatCode, который сверяет заявку с банком.
	Ответы: { done: false } — ждём; { done: true, ok: true, redirect } — оплачено;
	{ done: true, ok: false, message } — отказ. Сбой сети — не отказ, ждём дальше.
	После дедлайна делаем ещё один опрос: клиент мог подтвердить покупку, пока
	страница стояла в фоне, — тогда покажем успех, а не таймаут.
*/
async function pollInstalment(pollUrl) {
	const deadline = Date.now() + INSTALMENTS_TIMEOUT_MS;

	for (;;) {
		await waitOrVisible(Math.max(0, Math.min(POLL_INTERVAL, deadline - Date.now())));

		let data = null;
		try {
			const response = await fetch(pollUrl, { credentials: "same-origin" });
			data = await response.json();
		} catch (e) {
			data = null;
		}

		if (data?.done) return data;
		if (Date.now() >= deadline) break;
	}

	return { done: true, ok: false, message: INSTALMENTS_TIMEOUT_TEXT };
}

async function payInstalments(api, orderId, orderKey) {
	isSubmitting = true;
	setLoading(true);
	setCheckoutState("waiting");

	// Cookie moveat_pending_order здесь не ставим: клиент не уходит со страницы,
	// а глобальный чекер по этой cookie увёл бы его на /pay-problem/.
	let result = null;
	try {
		result = await api.checkout.payOrder(orderId, {
			payment_method: METHOD_SLUG_MAP.parts,
			order_key: orderKey,
			instalments: { parts: getSelectedTerm() },
		});
	} catch (err) {
		console.error("[payment-process] instalments request failed:", err);
		showCheckoutError(err?.message);
		return;
	}

	if (!result?.wait || !result?.poll_url) {
		showCheckoutError(result?.error);
		return;
	}

	const answer = await pollInstalment(result.poll_url);

	if (answer.ok && answer.redirect) {
		setCheckoutState("success");
		window.setTimeout(() => {
			window.location.href = answer.redirect;
		}, SUCCESS_REDIRECT_DELAY);
		return;
	}

	showCheckoutError(answer.message);
}

// ─── Инициализация ────────────────────────────────────────────────────────────

export function initPaymentProcess() {
	if (!submitButton) return; // не страница оплаты

	initMethodSelection();
	initTermKeyboard();
	initCheckoutControls();

	agreeCheckbox?.addEventListener("change", updateSubmitState);
	submitButton.addEventListener("click", handlePay);
}
