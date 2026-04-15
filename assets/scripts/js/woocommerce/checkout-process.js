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

	setLoading();

	try {
		const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
		if (!api) throw new Error("API не инициализирован");

		const result = await api.checkout.placeOrder({
			billing_address: billingAddress,
			payment_method: paymentMethod,
		});

		console.log("[checkout-process] placeOrder result:", result);

		// Редиректим на страницу выбора метода оплаты
		if (result?.order_id && result?.order_key) {
			const base =
				window.MOVEAT_WOO_API_CONFIG?.baseUrl || window.location.origin;
			window.location.href = `${base}/order-pay/?order_id=${result.order_id}&order_key=${result.order_key}`;
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
