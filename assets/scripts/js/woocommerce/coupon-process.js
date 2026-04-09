/*
	Модуль управления промокодами на странице корзины.
	Отвечает за:
	  - выставление дефолтного состояния блока промокода при загрузке страницы;
	  - валидацию поля ввода (активация/деактивация кнопки «Применить»);
	  - применение промокода через API и обновление UI;
	  - удаление промокода через API и обновление UI.
*/

import { showSystemMessage } from "../modules/system-message.js";
import {
	refreshCartData,
	toggleSummaryLoader,
	toggleCheckoutButton,
} from "./common/refresh-cart-data.js";

// ─── Селекторы DOM-элементов блока промокода ──────────────────────────────────

const SEL_PROMO_CONTROL = ".cart-page__promo-control";
const SEL_PROMO_INPUT = ".cart-page__promo-input";
const SEL_PROMO_APPLY_BTN =
	".cart-page__promo-control .cart-page__promo-button";
const SEL_PROMO_MSG_WRAPPER = ".cart-page__promo-message-wrapper";
const SEL_PROMO_MSG_SUCCESS = ".cart-page__promo-message--success";
const SEL_PROMO_MSG_ERROR = ".cart-page__promo-message--error";
const SEL_PROMO_DELETE_BTN = ".cart-page__promo-button_delete";

// ─── Вспомогательные функции управления видимостью ───────────────────────────

// Скрывает DOM-элемент, добавляя класс «hidden».
function hide(el) {
	if (el) el.classList.add("hidden");
}

// Показывает DOM-элемент, убирая класс «hidden».
function show(el) {
	if (el) el.classList.remove("hidden");
}

// ─── Функции управления состояниями промо-блока ───────────────────────────────

/**
 * Состояние 1: промокод не применён.
 * Показывает поле ввода, скрывает блок с сообщениями.
 */
function setStateNoCoupon() {
	const control = document.querySelector(SEL_PROMO_CONTROL);
	const msgWrapper = document.querySelector(SEL_PROMO_MSG_WRAPPER);
	const msgSuccess = document.querySelector(SEL_PROMO_MSG_SUCCESS);
	const msgError = document.querySelector(SEL_PROMO_MSG_ERROR);
	const deleteBtn = document.querySelector(SEL_PROMO_DELETE_BTN);

	show(control);
	hide(msgWrapper);
	hide(msgSuccess);
	hide(msgError);
	hide(deleteBtn);
}

/**
 * Состояние 2: промокод успешно применён.
 * Скрывает поле ввода, показывает сообщение об успехе и кнопку удаления.
 *
 * @param {string} [couponCode] — код применённого промокода (для отображения в блоке).
 */
function setStateCouponApplied(couponCode) {
	const control = document.querySelector(SEL_PROMO_CONTROL);
	const msgWrapper = document.querySelector(SEL_PROMO_MSG_WRAPPER);
	const msgSuccess = document.querySelector(SEL_PROMO_MSG_SUCCESS);
	const msgError = document.querySelector(SEL_PROMO_MSG_ERROR);
	const deleteBtn = document.querySelector(SEL_PROMO_DELETE_BTN);

	// Обновляем отображаемый код купона в блоке успеха (второй дочерний div)
	if (couponCode && msgSuccess) {
		const codeEl = msgSuccess.querySelectorAll("div")[1];
		if (codeEl) codeEl.textContent = couponCode.toUpperCase();
	}

	hide(control);
	show(msgWrapper);
	show(msgSuccess);
	hide(msgError);
	show(deleteBtn);
	// Снимаем блокировку кнопки удаления — она могла остаться после предыдущего запроса
	if (deleteBtn) deleteBtn.classList.remove("unactive");
}

/**
 * Состояние 3: ошибка при применении промокода.
 * Показывает поле ввода, скрывает все сообщения в сайдбаре —
 * ошибка выводится через системное сообщение (showSystemMessage).
 */
function setStateCouponError() {
	const control = document.querySelector(SEL_PROMO_CONTROL);
	const msgWrapper = document.querySelector(SEL_PROMO_MSG_WRAPPER);
	const msgSuccess = document.querySelector(SEL_PROMO_MSG_SUCCESS);
	const msgError = document.querySelector(SEL_PROMO_MSG_ERROR);
	const deleteBtn = document.querySelector(SEL_PROMO_DELETE_BTN);

	show(control);
	hide(msgWrapper);
	hide(msgError);
	hide(msgSuccess);
	hide(deleteBtn);
}

// ─── Инициализация начального состояния при загрузке страницы ────────────────

/**
 * Определяет начальное состояние блока промокода по DOM, отрисованному сервером (PHP).
 * Дополнительный запрос к API не делается — PHP уже проставил нужные классы hidden.
 * Если блок сообщений об успехе видим (нет класса hidden) — значит купон применён,
 * и состояние уже корректное. Если нет — убеждаемся, что показано поле ввода.
 *
 * Такой подход исключает «моргание» UI при загрузке страницы.
 */
function initCouponState() {
	const msgSuccess = document.querySelector(SEL_PROMO_MSG_SUCCESS);

	// Если сервер отрисовал блок успеха без hidden — купон уже применён, всё ок.
	// Дополнительно синхронизируем состояние остальных элементов на случай расхождения.
	if (msgSuccess && !msgSuccess.classList.contains("hidden")) {
		// Читаем код купона из DOM (второй дочерний div в блоке успеха)
		const codeEl = msgSuccess.querySelectorAll("div")[1];
		const code = codeEl ? codeEl.textContent.trim() : "";
		setStateCouponApplied(code);
	} else {
		setStateNoCoupon();
	}
}

// ─── Валидация поля ввода промокода ──────────────────────────────────────────

/**
 * Навешивает обработчик на поле ввода промокода:
 * если поле пустое — кнопка «Применить» неактивна (класс unactive),
 * если введён хотя бы один символ — кнопка становится активной.
 */
function initPromoInputValidation() {
	const input = document.querySelector(SEL_PROMO_INPUT);
	const applyBtn = document.querySelector(SEL_PROMO_APPLY_BTN);

	if (!input || !applyBtn) return;

	// Выставляем начальное состояние кнопки в зависимости от значения поля
	const syncButtonState = () => {
		const isEmpty = input.value.trim().length === 0;
		applyBtn.classList.toggle("unactive", isEmpty);
	};

	// Слушаем ввод и изменения в поле
	input.addEventListener("input", syncButtonState);
	input.addEventListener("change", syncButtonState);

	// Выставляем начальное состояние сразу
	syncButtonState();
}

// ─── Применение промокода ─────────────────────────────────────────────────────

/**
 * Блокирует или разблокирует элементы управления промо-блока
 * (поле ввода и кнопка «Применить») на время выполнения запроса.
 *
 * @param {boolean} isLocked — true для блокировки, false для разблокировки.
 */
function togglePromoControlsLock(isLocked) {
	const input = document.querySelector(SEL_PROMO_INPUT);
	const applyBtn = document.querySelector(SEL_PROMO_APPLY_BTN);

	if (input) input.disabled = isLocked;
	if (applyBtn) applyBtn.classList.toggle("unactive", isLocked);
}

/**
 * Навешивает обработчик на кнопку «Применить»:
 * при клике отправляет запрос применения промокода,
 * блокирует UI на время запроса, затем переключает состояние блока.
 */
function initApplyCoupon() {
	const applyBtn = document.querySelector(SEL_PROMO_APPLY_BTN);
	if (!applyBtn) return;

	applyBtn.addEventListener("click", async () => {
		// Игнорируем клик, если кнопка неактивна
		if (applyBtn.classList.contains("unactive")) return;

		const input = document.querySelector(SEL_PROMO_INPUT);
		const code = input ? input.value.trim() : "";

		if (!code) return;

		const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
		if (!api || !api.coupons || typeof api.coupons.applyCoupon !== "function") {
			showSystemMessage("error", "API промокодов недоступен");
			return;
		}

		try {
			// Блокируем элементы управления промо-блока
			togglePromoControlsLock(true);
			// Показываем лоадер в блоке итогов
			toggleSummaryLoader(true);
			// Блокируем кнопку «Оформить заказ»
			toggleCheckoutButton(true);

			const cart = await api.coupons.applyCoupon(code);

			// Промокод успешно применён — переключаем состояние
			setStateCouponApplied(code);

			// Обновляем цены и счётчики в UI корзины
			await refreshCartData(cart);
		} catch (err) {
			// Ошибка применения — показываем состояние ошибки
			setStateCouponError();
			showSystemMessage(
				"error",
				"Произошла ошибка во время применения промокода",
			);
		} finally {
			// Снимаем блокировку элементов управления
			togglePromoControlsLock(false);
			// Скрываем лоадер
			toggleSummaryLoader(false);
			// Разблокируем кнопку «Оформить заказ»
			toggleCheckoutButton(false);
		}
	});
}

// ─── Удаление промокода ───────────────────────────────────────────────────────

/**
 * Навешивает обработчик на кнопку «Удалить промокод»:
 * при клике отправляет запрос удаления активного купона,
 * блокирует UI на время запроса, затем возвращает состояние «не применён».
 */
function initRemoveCoupon() {
	const deleteBtn = document.querySelector(SEL_PROMO_DELETE_BTN);
	if (!deleteBtn) return;

	deleteBtn.addEventListener("click", async () => {
		// Игнорируем клик, если кнопка неактивна
		if (deleteBtn.classList.contains("unactive")) return;

		const api = window.MOVEAT_API && window.MOVEAT_API.woocommerce;
		if (
			!api ||
			!api.coupons ||
			typeof api.coupons.removeCoupon !== "function"
		) {
			showSystemMessage("error", "API промокодов недоступен");
			return;
		}

		// Получаем текущий применённый код из DOM
		const msgSuccess = document.querySelector(SEL_PROMO_MSG_SUCCESS);
		const codeEl = msgSuccess ? msgSuccess.querySelectorAll("div")[1] : null;
		const code = codeEl ? codeEl.textContent.trim() : "";

		if (!code) return;

		try {
			// Временно блокируем кнопку удаления
			deleteBtn.classList.add("unactive");
			// Показываем лоадер в блоке итогов
			toggleSummaryLoader(true);
			// Блокируем кнопку «Оформить заказ»
			toggleCheckoutButton(true);

			const cart = await api.coupons.removeCoupon(code);

			// Промокод успешно удалён — возвращаем состояние «не применён»
			setStateNoCoupon();

			// Обновляем цены и счётчики в UI корзины
			await refreshCartData(cart);
		} catch (err) {
			showSystemMessage(
				"error",
				"Произошла ошибка во время удаления промокода",
			);
			deleteBtn.classList.remove("unactive");
		} finally {
			toggleSummaryLoader(false);
			toggleCheckoutButton(false);
		}
	});
}

// ─── Точка входа модуля ───────────────────────────────────────────────────────

/**
 * Инициализирует весь функционал промокодов на странице корзины.
 * Вызывается при загрузке DOM. Если блок промокода отсутствует на странице —
 * функция завершается без ошибок (модуль подключён глобально).
 */
function initCouponProcess() {
	// Проверяем, находимся ли мы на странице корзины
	const promoBlock = document.querySelector(".cart-page__promo");
	if (!promoBlock) return;

	// Определяем начальное состояние блока (применён ли купон в корзине)
	initCouponState();

	// Подключаем валидацию поля ввода
	initPromoInputValidation();

	// Подключаем обработчик применения промокода
	initApplyCoupon();

	// Подключаем обработчик удаления промокода
	initRemoveCoupon();
}

// Запускаем инициализацию после загрузки DOM
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initCouponProcess);
} else {
	initCouponProcess();
}
