(function () {
	function hideSpinner() {
		var spinner = document.querySelector('[data-spinner]');
		if (!spinner) return;

		// Плавное скрытие через inline-стили (не зависит от Bootstrap-классов)
		spinner.style.transition = 'opacity 0.4s ease';
		spinner.style.opacity = '0';
		spinner.style.pointerEvents = 'none';

		// Полное удаление из потока после завершения перехода
		setTimeout(function () {
			spinner.style.display = 'none';
		}, 450);
	}

	if (document.readyState === 'complete') {
		// Страница уже загружена — скрываем сразу
		hideSpinner();
	} else {
		window.addEventListener('load', hideSpinner);
	}
}());
