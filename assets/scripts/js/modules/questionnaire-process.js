/**
 * Динамический опросник (questionnaire-process.html).
 *
 * Показывает вопросы по одному, рисует прогресс-бар с точками, блокирует
 * «Продолжить» пока вариант не выбран, а после последнего вопроса открывает
 * модалку с e-mail и уводит на страницу результатов.
 *
 * Файл намеренно не входит в сборку: подключается к странице напрямую тегом
 * <script> и не тянется в общий бандл сайта. Требует, чтобы перед ним был
 * подключён questionnaire-scoring.js — оттуда берётся расчёт баллов.
 */
(function (global) {
	"use strict";

	var NEXT_LABEL_FALLBACK = "Продолжить";
	var FINISH_LABEL_FALLBACK = "Узнать результат";
	var DEFAULT_RESULT_URL = "questionnaire-results.html";
	// Столько же длится закрытие модалки в CSS (--modal-out)
	var CLOSE_ANIMATION_MS = 260;

	/**
	 * Практичная проверка адреса: непустая локальная часть, одна «собака»,
	 * домен с точкой и зоной хотя бы из двух букв. Отдельную библиотеку не
	 * тянем — файл должен оставаться автономным.
	 */
	var EMAIL_RE = /^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)*\.[A-Za-z]{2,}$/;

	/**
	 * Последний посчитанный результат. Живёт в памяти страницы: пересчитывается
	 * на каждый ответ и доступен снаружи через MoveatQuestionnaire.getResult().
	 */
	var lastResult = null;

	/** Введённый в модалке e-mail — нужен при передаче результата дальше */
	var lastEmail = null;

	/**
	 * Идентификатор этого прохождения опроса — уходит на страницу результатов
	 * параметром qid. Заводится один раз и переиспользуется: иначе повторное
	 * нажатие кнопки отправки дало бы новый идентификатор, а с ним и второе
	 * письмо на ту же почту.
	 */
	var questionnaireId = null;

	function getResult() {
		return lastResult;
	}

	function getEmail() {
		return lastEmail;
	}

	function getQuestionnaireId() {
		if (!questionnaireId) {
			questionnaireId = global.MoveatQuestionnaireScoring
				? global.MoveatQuestionnaireScoring.generateQuestionnaireId()
				: null;
		}

		return questionnaireId;
	}

	function init() {
		var root = document.querySelector("[data-questprocess]");
		if (!root) return;

		var steps = Array.prototype.slice.call(
			root.querySelectorAll("[data-step]")
		);
		if (steps.length === 0) return;

		var scoring = global.MoveatQuestionnaireScoring;
		if (!scoring) {
			console.error(
				"questionnaire-process: не найден questionnaire-scoring.js — " +
					"подключите его перед этим файлом"
			);
			return;
		}

		var progress = root.querySelector("[data-quest-progress]");
		var dotsHost = root.querySelector("[data-quest-dots]");
		var fill = root.querySelector("[data-quest-fill]");
		var counter = root.querySelector("[data-quest-counter]");
		var percent = root.querySelector("[data-quest-percent]");
		var backBtn = root.querySelector("[data-quest-back]");
		var nextBtn = root.querySelector("[data-quest-next]");
		var nextLabel = root.querySelector("[data-quest-next-label]");

		var total = steps.length;
		var resultUrl = root.dataset.resultUrl || DEFAULT_RESULT_URL;
		var labelNext =
			(nextBtn && nextBtn.dataset.labelNext) || NEXT_LABEL_FALLBACK;
		var labelFinish =
			(nextBtn && nextBtn.dataset.labelFinish) || FINISH_LABEL_FALLBACK;

		var current = 0;

		// ── Точки прогресса ───────────────────────────────────────────
		// Генерируем по числу шагов в разметке, чтобы прогресс-бар не пришлось
		// править вручную при добавлении или удалении вопроса.
		var dots = [];

		// При большом числе вопросов номера в точки уже не помещаются —
		// переводим шкалу в компактный режим (мелкие точки без цифр).
		var COMPACT_FROM = 13;
		if (progress && total >= COMPACT_FROM) {
			progress.classList.add("is-compact");
		}

		if (dotsHost) {
			dotsHost.style.setProperty("--quest-steps", String(total));
			steps.forEach(function (step, index) {
				var dot = document.createElement("span");
				dot.className = "quest-progress__dot";
				dot.textContent = String(index + 1);
				dotsHost.appendChild(dot);
				dots.push(dot);
			});
		}

		// ── Состояние ответов ─────────────────────────────────────────
		function inputs(step) {
			return Array.prototype.slice.call(
				step.querySelectorAll(".option-card__input")
			);
		}

		function isAnswered(step) {
			return inputs(step).some(function (input) {
				return input.checked;
			});
		}

		function answeredCount() {
			return steps.reduce(function (acc, step) {
				return isAnswered(step) ? acc + 1 : acc;
			}, 0);
		}

		/**
		 * Ответы в виде { номер вопроса: номер варианта }. Номера берём из
		 * порядка шагов и вариантов в разметке, а не из value — так таблица
		 * баллов остаётся единственным источником правды о начислении.
		 */
		function collectAnswers() {
			var answers = {};

			steps.forEach(function (step, stepIndex) {
				var option = inputs(step).findIndex(function (input) {
					return input.checked;
				});
				if (option >= 0) answers[stepIndex + 1] = option + 1;
			});

			return answers;
		}

		/** Пересчитывает результат и кладёт его в память страницы */
		function recalculate() {
			lastResult = scoring.calculateQuestionnaireResult(collectAnswers());

			document.dispatchEvent(
				new CustomEvent("questionnaire:calculated", { detail: lastResult })
			);

			return lastResult;
		}

		// ── Отрисовка ─────────────────────────────────────────────────
		function renderProgress() {
			var done = answeredCount();
			var ratio = Math.min(100, Math.round((done / total) * 100));

			if (fill) fill.style.width = ratio + "%";
			if (percent) percent.textContent = ratio + "%";
			if (counter) {
				counter.textContent = "Вопрос " + (current + 1) + " из " + total;
			}
			if (progress) progress.setAttribute("aria-valuenow", String(ratio));

			dots.forEach(function (dot, index) {
				dot.classList.toggle("is-done", isAnswered(steps[index]));
				dot.classList.toggle("is-current", index === current);
			});
		}

		function renderNav() {
			var isLast = current === total - 1;
			var ready = isAnswered(steps[current]);

			if (backBtn) backBtn.hidden = current === 0;

			if (nextBtn) {
				nextBtn.classList.toggle("unactive", !ready);
				nextBtn.disabled = !ready;
				nextBtn.setAttribute("aria-disabled", String(!ready));
			}

			if (nextLabel) nextLabel.textContent = isLast ? labelFinish : labelNext;
		}

		function renderSelection(step) {
			inputs(step).forEach(function (input) {
				var card = input.closest(".option-card");
				if (card) card.classList.toggle("is-selected", input.checked);
			});
		}

		function showStep(index, scroll) {
			current = Math.max(0, Math.min(index, total - 1));

			steps.forEach(function (step, i) {
				var active = i === current;
				step.hidden = !active;
				step.classList.toggle("is-active", active);
			});

			renderSelection(steps[current]);
			renderProgress();
			renderNav();

			if (scroll) scrollToTopOfQuest();
		}

		// На мобильных карточка вопроса может уехать за верхнюю границу экрана —
		// подтягиваем её обратно с учётом фиксированной шапки.
		function scrollToTopOfQuest() {
			var header = document.querySelector(".header");
			var headerHeight = header ? header.getBoundingClientRect().height : 0;
			var top =
				root.getBoundingClientRect().top +
				window.pageYOffset -
				headerHeight -
				20;

			if (window.pageYOffset > top) {
				window.scrollTo({ top: Math.max(top, 0), behavior: "smooth" });
			}
		}

		// ── Обработчики ───────────────────────────────────────────────
		root.addEventListener("change", function (event) {
			var target = event.target;
			var input = target ? target.closest(".option-card__input") : null;
			if (!input) return;

			var step = input.closest("[data-step]");
			if (!step) return;

			renderSelection(step);
			renderProgress();
			renderNav();
			recalculate();
		});

		if (backBtn) {
			backBtn.addEventListener("click", function () {
				if (current === 0) return;
				showStep(current - 1, true);
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener("click", function () {
				if (!isAnswered(steps[current])) return;

				if (current === total - 1) {
					recalculate();
					openEmailModal();
					return;
				}

				showStep(current + 1, true);
			});
		}

		// Форма нужна только как контейнер полей — сабмит по Enter не нужен.
		var questForm = root.querySelector("[data-quest-form]");
		if (questForm) {
			questForm.addEventListener("submit", function (event) {
				event.preventDefault();
			});
		}

		// ── Модалка с e-mail ──────────────────────────────────────────
		// Открывается после последнего вопроса; без валидного адреса кнопка
		// перехода к результатам остаётся заблокированной.
		var modal = document.querySelector("[data-email-modal]");
		var emailForm = modal && modal.querySelector("[data-email-form]");
		var emailInput = modal && modal.querySelector("[data-email-input]");
		var emailError = modal && modal.querySelector("[data-email-error]");
		var emailSubmit = modal && modal.querySelector("[data-email-submit]");

		// Показываем ошибку не на каждый символ, а после blur или отправки
		var emailDirty = false;

		function emailValue() {
			return emailInput ? emailInput.value.trim() : "";
		}

		function isEmailValid() {
			var value = emailValue();
			return value.length > 0 && value.length <= 254 && EMAIL_RE.test(value);
		}

		function renderEmailState() {
			var valid = isEmailValid();
			var showError = emailDirty && !valid && emailValue().length > 0;

			if (emailInput) emailInput.classList.toggle("is-invalid", showError);
			if (emailError) emailError.classList.toggle("is-visible", showError);

			if (emailSubmit) {
				emailSubmit.classList.toggle("unactive", !valid);
				emailSubmit.disabled = !valid;
				emailSubmit.setAttribute("aria-disabled", String(!valid));
			}
		}

		/**
		 * Уводит на страницу результатов, передавая баллы и почту параметрами
		 * адреса — их разбирает шаблон questionnaire-results-with-modal.php.
		 */
		function goToResults(result, email) {
			window.location.href = scoring.buildResultUrl(
				resultUrl,
				result,
				email,
				getQuestionnaireId()
			);
		}

		function openEmailModal() {
			if (!modal) {
				// Модалки на странице нет — уводим сразу на результаты, без почты
				goToResults(lastResult || recalculate());
				return;
			}

			emailDirty = false;
			renderEmailState();

			// В разметке модалка помечена hidden, чтобы не мелькнуть до стилей.
			// Снимаем его и форсим пересчёт: без этого браузер объединит
			// появление элемента и класс в один кадр, и анимация не отработает.
			modal.hidden = false;
			void modal.offsetWidth;

			modal.classList.add("is-visible");
			modal.setAttribute("aria-hidden", "false");
			// Класс вешаем и на html: на body одного overflow не хватает —
			// прокрутку в этой вёрстке ведёт корневой элемент
			document.documentElement.classList.add("email-modal-open");
			document.body.classList.add("email-modal-open");

			// Фокус в поле — чтобы можно было сразу печатать.
			// preventScroll, иначе браузер дёргает страницу под модалкой.
			if (emailInput) emailInput.focus({ preventScroll: true });
		}

		function closeEmailModal() {
			if (!modal) return;

			modal.classList.remove("is-visible");
			modal.setAttribute("aria-hidden", "true");
			document.documentElement.classList.remove("email-modal-open");
			document.body.classList.remove("email-modal-open");
			if (nextBtn) nextBtn.focus({ preventScroll: true });

			// Возвращаем hidden, когда анимация закрытия доиграла, — но только
			// если модалку не успели открыть заново
			window.setTimeout(function () {
				if (!modal.classList.contains("is-visible")) modal.hidden = true;
			}, CLOSE_ANIMATION_MS);
		}

		if (emailInput) {
			emailInput.addEventListener("input", renderEmailState);

			emailInput.addEventListener("blur", function () {
				if (emailValue().length > 0) emailDirty = true;
				renderEmailState();
			});
		}

		if (emailForm) {
			emailForm.addEventListener("submit", function (event) {
				event.preventDefault();

				emailDirty = true;
				renderEmailState();
				if (!isEmailValid()) {
					if (emailInput) emailInput.focus();
					return;
				}

				lastEmail = emailValue();
				var result = recalculate();

				document.dispatchEvent(
					new CustomEvent("questionnaire:completed", {
						detail: { result: result, email: lastEmail }
					})
				);

				goToResults(result, lastEmail);
			});
		}

		if (modal) {
			var closeBtn = modal.querySelector("[data-email-modal-close]");
			if (closeBtn) closeBtn.addEventListener("click", closeEmailModal);

			var overlay = modal.querySelector("[data-email-modal-overlay]");
			if (overlay) overlay.addEventListener("click", closeEmailModal);
		}

		document.addEventListener("keydown", function (event) {
			if (
				event.key === "Escape" &&
				modal &&
				modal.classList.contains("is-visible")
			) {
				closeEmailModal();
			}
		});

		showStep(0);
		recalculate();

		// Публичный интерфейс страницы: пригодится для передачи результата
		// дальше и для отладки из консоли
		global.MoveatQuestionnaire = {
			getResult: getResult,
			getEmail: getEmail,
			collectAnswers: collectAnswers,
			recalculate: recalculate,
			openEmailModal: openEmailModal,
			closeEmailModal: closeEmailModal,
			getQuestionnaireId: getQuestionnaireId,
			buildResultUrl: function (email) {
				return scoring.buildResultUrl(
					resultUrl,
					lastResult || recalculate(),
					email || lastEmail,
					getQuestionnaireId()
				);
			}
		};
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})(window);
