/**
 * Модалка с подарком на странице результатов
 * (questionnaire-results-with-modal.html).
 *
 * Показывается сама при входе на страницу и перекрывает результаты, пока
 * пользователь её не закроет: крестиком, кнопкой «Перейти к результатам»,
 * кликом по подложке или Escape. Переход в мессенджер тоже закрывает модалку —
 * ссылка открывается в отдельной вкладке, а под ней человек сразу видит
 * результаты.
 *
 * Файл намеренно не входит в сборку: подключается к странице напрямую тегом
 * <script> и не тянется в общий бандл сайта.
 */
(function (global) {
	"use strict";

	var DEFAULT_DELAY = 400;
	// Столько же длится закрытие в CSS (--gift-modal-out)
	var CLOSE_ANIMATION_MS = 260;

	function init() {
		var modal = document.querySelector("[data-gift-modal]");
		if (!modal) return;

		var dialog = modal.querySelector(".gift-modal__dialog");
		var closeTriggers = Array.prototype.slice.call(
			modal.querySelectorAll("[data-gift-modal-close]")
		);
		var messengers = Array.prototype.slice.call(
			modal.querySelectorAll("[data-gift-modal-messenger]")
		);

		// Небольшая пауза перед показом: страница успевает отрисоваться,
		// и появление модалки читается как отдельное движение
		var delay = parseInt(modal.dataset.giftModalDelay, 10);
		if (isNaN(delay) || delay < 0) delay = DEFAULT_DELAY;

		var isOpen = false;

		function open() {
			if (isOpen) return;
			isOpen = true;

			// В разметке модалка помечена hidden, чтобы не мелькнуть до стилей.
			// Снимаем его и форсим пересчёт: без этого браузер объединит
			// появление элемента и класс в один кадр, и анимация не отработает.
			modal.hidden = false;
			void modal.offsetWidth;

			modal.classList.add("is-visible");
			modal.setAttribute("aria-hidden", "false");
			// Класс вешаем и на html: на body одного overflow не хватает —
			// прокрутку в этой вёрстке ведёт корневой элемент
			document.documentElement.classList.add("gift-modal-open");
			document.body.classList.add("gift-modal-open");

			// Фокус уводим в модалку, иначе Tab уходит гулять по странице под ней.
			// preventScroll, чтобы страница не дёрнулась.
			if (dialog) {
				if (!dialog.hasAttribute("tabindex")) {
					dialog.setAttribute("tabindex", "-1");
				}
				dialog.focus({ preventScroll: true });
			}
		}

		function close() {
			if (!isOpen) return;
			isOpen = false;

			modal.classList.remove("is-visible");
			modal.setAttribute("aria-hidden", "true");
			document.documentElement.classList.remove("gift-modal-open");
			document.body.classList.remove("gift-modal-open");

			// Возвращаем hidden, когда анимация закрытия доиграла, — но только
			// если модалку не успели открыть заново
			window.setTimeout(function () {
				if (!isOpen) modal.hidden = true;
			}, CLOSE_ANIMATION_MS);
		}

		closeTriggers.forEach(function (trigger) {
			trigger.addEventListener("click", close);
		});

		// Клик по мессенджеру не отменяем — ссылка отрабатывает как обычно,
		// а модалку закрываем следом, чтобы человек вернулся к результатам
		messengers.forEach(function (link) {
			link.addEventListener("click", function () {
				window.setTimeout(close, 150);
			});
		});

		// Кнопки баннеров с подарком открывают ту же модалку. Слушатель вешаем
		// на документ, а не на сами кнопки: баннеры подставляются в страницу
		// асинхронно (data-include), и к этому моменту их в DOM ещё нет
		document.addEventListener("click", function (event) {
			var trigger = event.target.closest
				? event.target.closest("[data-gift-modal-open]")
				: null;
			if (!trigger) return;
			event.preventDefault();
			open();
		});

		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape" && isOpen) close();
		});

		if (delay > 0) {
			window.setTimeout(open, delay);
		} else {
			open();
		}

		global.MoveatGiftModal = { open: open, close: close };
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})(window);
