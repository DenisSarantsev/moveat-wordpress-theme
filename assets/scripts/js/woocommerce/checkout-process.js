import { showSystemMessage } from "../modules/system-message.js";

// ─── DOM-узлы ─────────────────────────────────────────────────────────────────

const form = document.getElementById("orderForm");
const submitBtn = document.getElementById("orderSubmit");

// ─── Поля формы ───────────────────────────────────────────────────────────────

const REQUIRED_FIELDS = ["firstName", "email", "phone"];

// ─── Состояние ────────────────────────────────────────────────────────────────

let isSubmitting = false; // запрос на создание заказа в полёте
let isRedirecting = false; // уходим со страницы — блокировку не снимаем

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

// ─── Состояния кнопки и блокировка формы ─────────────────────────────────────

// Поля формы, которые нужно заблокировать на время запроса.
function getFormControls() {
	return form ? form.querySelectorAll("input, select, textarea") : [];
}

function setLoading() {
	if (!submitBtn) return;

	// is-loading — лоадер в кнопке (assets/styles/checkout.css),
	// is-submitting — блокировка остальной формы от правок и повторного сабмита.
	submitBtn.classList.add("is-loading");
	submitBtn.disabled = true;
	submitBtn.setAttribute("aria-busy", "true");

	if (form) {
		form.classList.add("is-submitting");
		form.setAttribute("aria-busy", "true");
	}

	getFormControls().forEach((el) => {
		el.disabled = true;
	});
}

function setIdle() {
	if (!submitBtn) return;

	submitBtn.classList.remove("is-loading");
	submitBtn.removeAttribute("aria-busy");

	if (form) {
		form.classList.remove("is-submitting");
		form.removeAttribute("aria-busy");
	}

	getFormControls().forEach((el) => {
		el.disabled = false;
	});

	if (isFormValid()) {
		submitBtn.disabled = false;
	}
}

// ─── Отправка заказа ──────────────────────────────────────────────────────────

async function handleSubmit(e) {
	e.preventDefault();

	if (!isFormValid()) return;

	// Один заказ за раз: отсекаем повторный сабмит (Enter, быстрый двойной клик).
	if (isSubmitting) return;

	const firstName = document.getElementById("firstName").value.trim();
	const lastName = document.getElementById("lastName")?.value.trim() ?? "";
	const email = document.getElementById("email").value.trim();
	// Берём полный номер с кодом страны через мост intl-tel-input (см. create-order.ts).
	// #phone.value хранит только нац. часть (separateDialCode), поэтому читаем getNumber().
	const phoneEl = document.getElementById("phone");
	const phone =
		window.MOVEAT_CHECKOUT?.getPhoneNumber?.().trim() || phoneEl.value.trim();
	// Опциональные поля: region/state и country (если на форме есть — берем их, иначе оставляем дефолты)
	const state = document.getElementById("state")?.value.trim() ?? "UA30";
	const country = document.getElementById("country")?.value.trim() ?? "UA";
	// Можно также взять реальные адресные поля, если присутствуют на форме
	const address1 =
		document.getElementById("address1")?.value.trim() ?? "Адрес не указан";
	const city =
		document.getElementById("city")?.value.trim() ?? "Город не указан";
	const postcode = document.getElementById("postcode")?.value.trim() ?? "00000";

	const billingAddress = {
		first_name: firstName,
		last_name: lastName || "Фамилия не указана",
		email: email,
		phone: phone,
		// Если в форме присутствуют реальные поля — используем их, иначе передаём безопасные заглушки
		address_1: address1,
		city: city,
		postcode: postcode,
		country: country,
		// Добавляем state — многие страны (включая UA) требуют этот параметр при валидации
		state: state,
	};

	// "cod" — заглушка для создания заказа. Реальный метод оплаты
	// пользователь выбирает на странице /order-pay/.
	const paymentMethod = "cod";

	isSubmitting = true;
	setLoading();

	try {
		const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
		if (!api) throw new Error("API не инициализирован");

		// Формируем payload в формате REST Orders API: billing, shipping, payment_method
		const payload = {
			billing: billingAddress,
			shipping: billingAddress, // если у вас отдельная shipping-форма — подставьте её
			payment_method: paymentMethod,
			set_paid: false,
		};

		const result = await api.checkout.placeOrder(payload);

		// console.log(result);

		console.log("[checkout-process] placeOrder result:", result);

		// Бесплатный заказ (итог = 0) сервер уже провёл без оплаты —
		// страницу выбора метода оплаты пропускаем и идём сразу на «Спасибо».
		if (result?.is_free && result?.redirect_url) {
			isRedirecting = true;
			window.location.href = result.redirect_url;
			return;
		}

		// Редиректим на страницу выбора метода оплаты. REST Orders API возвращает id и order_key.
		if (result?.id && result?.order_key) {
			isRedirecting = true;
			const base =
				window.MOVEAT_WOO_API_CONFIG?.baseUrl || window.location.origin;
			window.location.href = `${base}/order-pay/?order_id=${result.id}&order_key=${result.order_key}`;
			return;
		}

		showSystemMessage("Заказ оформлен!", "success");
	} catch (err) {
		console.error("[checkout-process] placeOrder error:", err);

		// Попытка аккуратно распарсить ошибку от WC Store API
		let message = "Не удалось оформить заказ. Попробуйте позже.";
		// axios-like wrapper: err.response.data
		const respData = err?.response?.data ?? err?.data ?? null;
		if (respData) {
			if (respData.message) {
				message = respData.message;
			} else if (respData.data?.errors) {
				const errors = [];
				Object.values(respData.data.errors).forEach((arr) => {
					if (Array.isArray(arr)) errors.push(...arr);
				});
				if (errors.length) message = errors.join(" ");
			}
		} else if (err?.message) {
			message = err.message;
		}

		showSystemMessage(message, "error");
	} finally {
		isSubmitting = false;

		// При редиректе форму не разблокируем: иначе она на миг оживает,
		// пока браузер грузит следующую страницу.
		if (!isRedirecting) {
			setIdle();
		}
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
