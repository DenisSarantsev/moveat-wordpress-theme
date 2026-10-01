/**
 * Подсчёт результатов опросника (questionnaire-process.html).
 *
 * Каждый вариант ответа даёт баллы сразу по восьми категориям здоровья.
 * Средний балл категории — сумма её баллов, делённая на общее число вопросов
 * (неотвеченные вопросы дают 0, но делитель не меняется). Общая оценка —
 * среднее арифметическое восьми средних.
 *
 * Чем выше балл, тем хуже: 0 — нет риска, верх шкалы — выраженный риск.
 *
 * Файл намеренно не входит в сборку: подключается к странице напрямую тегом
 * <script> и не тянется в общий бандл сайта. Отсюда обычный скрипт без
 * импортов — публичный интерфейс отдаётся через window.
 */
(function (global) {
	"use strict";

	/** Всего вопросов в опроснике — он же делитель для средних баллов */
	var QUESTION_COUNT = 32;

	/** Категории в том же порядке, в каком идут баллы в ANSWER_SCORES */
	var CATEGORIES = [
		{
			key: "metabolic",
			label: "Метаболический синдром",
			scale: { min: 0.4, max: 6.6 },
			thresholds: { lowMax: 1.7, mediumMax: 3.22 },
		},
		{
			key: "inflammation",
			label: "Воспаления",
			scale: { min: 0.6, max: 7.1 },
			thresholds: { lowMax: 1.8, mediumMax: 3.63 },
		},
		{
			key: "acidification",
			label: "Закисление",
			scale: { min: 0.4, max: 4.3 },
			thresholds: { lowMax: 1.1, mediumMax: 2.06 },
		},
		{
			key: "glycemic",
			label: "Гликемичность",
			scale: { min: 0.4, max: 5.8 },
			thresholds: { lowMax: 1.5, mediumMax: 2.84 },
		},
		{
			key: "aging",
			label: "Риск старения",
			scale: { min: 0.4, max: 6.7 },
			thresholds: { lowMax: 1.7, mediumMax: 3.47 },
		},
		{
			key: "detox",
			label: "Очищаемость",
			scale: { min: 0.1, max: 5.6 },
			thresholds: { lowMax: 1.4, mediumMax: 2.88 },
		},
		{
			key: "cancer",
			label: "Риск рака",
			scale: { min: 0.5, max: 6.4 },
			thresholds: { lowMax: 1.6, mediumMax: 3.03 },
		},
		{
			key: "empty",
			label: "Пустые калории",
			scale: { min: 0.4, max: 5.6 },
			thresholds: { lowMax: 1.4, mediumMax: 2.53 },
		},
];

	/** Пороги для общей оценки (последняя колонка таблицы) */
	var OVERALL_SCALE = { min: 0.4, max: 6.0 };
	var OVERALL_THRESHOLDS = { lowMax: 1.5, mediumMax: 2.96 };

	/**
	 * Баллы по вариантам ответов: [вопрос][вариант][категория].
	 * Порядок категорий совпадает с CATEGORIES.
	 */
	var ANSWER_SCORES = [
		// 1. У вас бывают резкие приступы голода?
		[
			[8, 5, 0, 6, 5, 0, 2, 2],
			[4, 3, 0, 3, 3, 0, 1, 1],
			[0, 0, 0, 0, 1, 0, 0, 0],
		],
		// 2. Бывает ли у вас вечерний жор?
		[
			[8, 5, 0, 6, 5, 0, 2, 2],
			[4, 3, 0, 3, 3, 0, 1, 1],
			[0, 0, 0, 0, 1, 0, 0, 0],
		],
		// 3. Хватает ли вам энергии в течение дня?
		[
			[8, 8, 6, 2, 4, 8, 3, 2],
			[4, 4, 3, 1, 2, 4, 2, 1],
			[0, 1, 0, 0, 0, 0, 0, 0],
		],
		// 4. Бывает ли у вас очень сильное желание поесть сладкого?
		[
			[8, 8, 4, 8, 8, 4, 8, 8],
			[4, 4, 3, 3, 3, 3, 4, 4],
			[0, 1, 1, 1, 0, 0, 1, 1],
		],
		// 5. Хочется ли вам заниматься спортом?
		[
			[8, 8, 2, 4, 8, 5, 8, 3],
			[4, 4, 1, 3, 4, 3, 4, 2],
			[0, 1, 0, 1, 1, 0, 1, 0],
		],
		// 6. Пропадает ли концентрация / появляется сонливость?
		[
			[8, 8, 2, 8, 5, 4, 4, 4],
			[4, 4, 1, 4, 3, 2, 2, 2],
			[0, 1, 0, 1, 1, 0, 1, 0],
		],
		// 7. Часто ли вы переедаете и не можете остановиться?
		[
			[5, 5, 5, 4, 6, 8, 4, 8],
			[3, 3, 3, 2, 4, 4, 2, 3],
			[0, 0, 2, 1, 1, 0, 1, 0],
		],
		// 8. Хочется ли вам перекусывать в течение дня?
		[
			[8, 8, 2, 8, 5, 4, 4, 4],
			[4, 4, 1, 4, 3, 2, 2, 2],
			[0, 1, 0, 1, 1, 0, 1, 0],
		],
		// 9. Страдаете ли вы от воспалений кожи, высыпаний?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 10. Страдаете ли вы от выпадения волос?
		[
			[8, 8, 2, 8, 5, 4, 4, 4],
			[4, 4, 1, 4, 3, 2, 2, 2],
			[0, 1, 0, 1, 1, 0, 1, 0],
		],
		// 11. Есть ли у вас проблемы с лишним весом?
		[
			[8, 8, 2, 8, 8, 8, 8, 4],
			[4, 4, 1, 4, 5, 5, 5, 2],
			[0, 1, 0, 1, 1, 1, 1, 0],
		],
		// 12. Легко ли вам сбросить вес и контролировать его?
		[
			[8, 4, 4, 8, 8, 5, 8, 5],
			[5, 2, 2, 5, 5, 3, 5, 3],
			[2, 1, 1, 1, 1, 1, 1, 1],
		],
		// 13. Любите ли вы солёную пищу?
		[
			[3, 8, 5, 3, 6, 8, 8, 8],
			[2, 5, 3, 2, 4, 4, 4, 5],
			[1, 1, 1, 1, 1, 1, 1, 1],
		],
		// 14. Сколько воды и чая вы выпиваете в течение дня?
		[
			[1, 5, 2, 2, 5, 8, 3, 2],
			[2, 3, 2, 1, 3, 6, 2, 1],
			[0, 1, 1, 1, 1, 0, 1, 0],
		],
		// 15. Часто ли вы болеете простудными заболеваниями?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 16. Бывают ли у вас запоры?
		[
			[8, 6, 8, 5, 6, 8, 8, 8],
			[3, 3, 5, 3, 4, 5, 5, 5],
			[1, 1, 1, 0, 0, 1, 1, 1],
		],
		// 17. Как часто у вас бывает отёчность?
		[
			[8, 5, 8, 8, 8, 8, 4, 4],
			[5, 3, 6, 6, 6, 6, 3, 3],
			[0, 0, 1, 0, 0, 0, 0, 0],
		],
		// 18. На каком жире/масле вы чаще всего готовите?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 19. Какой заправкой вы заправляете салаты?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 20. Сколько раз в день вы едите животный белок?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 21. Сколько раз в неделю вы употребляете молоко?
		[
			[4, 8, 2, 2, 8, 6, 8, 3],
			[2, 6, 1, 1, 6, 3, 6, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 22. Сколько раз в неделю вы едите мясопродукты и копчёности?
		[
			[5, 8, 8, 3, 8, 8, 8, 8],
			[3, 6, 6, 2, 6, 6, 6, 6],
			[1, 1, 1, 0, 1, 1, 1, 1],
		],
		// 23. Сколько раз в неделю вы едите красное и/или белое мясо?
		[
			[5, 7, 2, 7, 8, 6, 8, 4],
			[3, 5, 1, 4, 4, 3, 4, 2],
			[1, 1, 0, 0, 0, 0, 1, 1],
		],
		// 24. Сколько зелени в неделю вы съедаете?
		[
			[8, 8, 3, 3, 3, 3, 3, 8],
			[3, 3, 2, 1, 1, 1, 1, 3],
			[0, 0, 0, 0, 0, 0, 0, 0],
		],
		// 25. Сколько овощей в неделю вы съедаете (без картошки)?
		[
			[8, 8, 3, 3, 3, 3, 3, 8],
			[3, 3, 2, 1, 1, 1, 1, 3],
			[0, 0, 0, 0, 0, 0, 0, 0],
		],
		// 26. Сколько фруктов и ягод в неделю вы съедаете?
		[
			[8, 8, 3, 3, 3, 3, 3, 8],
			[3, 3, 2, 1, 2, 1, 1, 3],
			[0, 0, 1, 0, 1, 0, 0, 0],
		],
		// 27. Соотношение овощей/картошки к кашам и животной пище
		[
			[8, 8, 8, 4, 4, 3, 4, 8],
			[3, 3, 3, 3, 2, 1, 2, 3],
			[1, 1, 1, 1, 1, 0, 0, 1],
		],
		// 28. Сколько раз в неделю вы едите орехи и семечки?
		[
			[4, 4, 2, 2, 4, 2, 3, 3],
			[2, 2, 2, 1, 2, 1, 1, 2],
			[1, 1, 1, 1, 0, 0, 0, 1],
		],
		// 29. Сколько раз в неделю вы едите бобовые?
		[
			[4, 4, 2, 2, 4, 2, 3, 3],
			[2, 2, 2, 1, 2, 1, 1, 2],
			[1, 1, 1, 1, 0, 0, 0, 1],
		],
		// 30. Сколько раз в неделю вы едите не цельнозерновые хлеб, каши, пасту?
		[
			[8, 4, 8, 8, 8, 8, 8, 8],
			[6, 3, 6, 6, 6, 6, 6, 6],
			[0, 0, 1, 0, 0, 0, 0, 0],
		],
		// 31. Сколько раз в неделю вы употребляете омега-3 или рыбий жир?
		[
			[1, 2, 2, 2, 3, 3, 4, 2],
			[1, 1, 1, 1, 2, 2, 2, 1],
			[1, 0, 1, 1, 1, 1, 1, 0],
		],
		// 32. Сколько раз в неделю вы употребляете льняное семя, грецкий орех, масла?
		[
			[1, 2, 2, 2, 3, 3, 4, 2],
			[1, 1, 1, 1, 2, 2, 2, 1],
			[1, 0, 1, 1, 1, 1, 1, 0],
		],
];

	/**
	 * Зону определяем по округлённому баллу — по тому же числу, которое увидит
	 * пользователь. Иначе значение ровно на границе (например 2,06) попадало бы
	 * в соседнюю зону из-за незаметного «хвоста» после запятой.
	 *
	 * @param {number} rounded
	 * @param {{ lowMax: number, mediumMax: number }} thresholds
	 * @returns {"low"|"medium"|"high"}
	 */
	function levelOf(rounded, thresholds) {
		if (rounded <= thresholds.lowMax) return "low";
		if (rounded <= thresholds.mediumMax) return "medium";
		return "high";
	}

	function round2(value) {
		return Math.round(value * 100) / 100;
	}

	/**
	 * Считает баллы по категориям и общую оценку.
	 *
	 * @param {Object.<number, number>} answers номер вопроса (1…32) → номер
	 *        выбранного варианта (1…3). Пропущенные вопросы не дают баллов.
	 * @returns {Object} результат с разбивкой по категориям и общей оценкой
	 */
	function calculateQuestionnaireResult(answers) {
		var totals = CATEGORIES.map(function () {
			return 0;
		});
		var clean = {};

		for (var question = 1; question <= QUESTION_COUNT; question += 1) {
			var option = answers[question];
			if (!option) continue;

			var row = ANSWER_SCORES[question - 1];
			var scores = row ? row[option - 1] : null;
			if (!scores) continue;

			clean[question] = option;
			scores.forEach(function (value, index) {
				totals[index] += value;
			});
		}

		var categories = CATEGORIES.map(function (meta, index) {
			var average = round2(totals[index] / QUESTION_COUNT);
			return {
				key: meta.key,
				label: meta.label,
				scale: meta.scale,
				thresholds: meta.thresholds,
				total: totals[index],
				average: average,
				level: levelOf(average, meta.thresholds)
			};
		});

		// Общая оценка — среднее восьми средних, а не среднее всех баллов
		var overallAverage = round2(
			categories.reduce(function (acc, category) {
				return acc + category.total / QUESTION_COUNT;
			}, 0) / categories.length
		);

		var answered = Object.keys(clean).length;

		return {
			categories: categories,
			overall: {
				average: overallAverage,
				level: levelOf(overallAverage, OVERALL_THRESHOLDS),
				scale: OVERALL_SCALE,
				thresholds: OVERALL_THRESHOLDS
			},
			answers: clean,
			answered: answered,
			total: QUESTION_COUNT,
			completed: answered === QUESTION_COUNT
		};
	}

	/**
	 * Имена GET-параметров, которые ждёт шаблон страницы результатов
	 * (templates/questionnaire-results-with-modal.php).
	 *
	 * value — балл, умноженный на 10: шаблон делит его обратно (+actual / 10),
	 *         поэтому 5.47 уезжает как 55 и показывается как 5.5.
	 * text  — номер варианта текста из ACF (text1 / text2 / text3), шаблон
	 *         прячет все контейнеры, кроме выбранного.
	 *
	 * Опечатка в "inflamation" — из шаблона, менять её здесь нельзя.
	 */
	var RESULT_PARAMS = {
		metabolic: { value: "metab_syndrome", text: "metab_syndrome_text" },
		inflammation: { value: "inflamation", text: "inflamation_text" },
		acidification: { value: "acidification", text: "acidification_text" },
		glycemic: { value: "glycemic_level", text: "glycemic_level_text" },
		aging: { value: "risk_of_aging", text: "risk_of_aging_text" },
		detox: { value: "cleanability", text: "cleanability_text" },
		cancer: { value: "cancer_risk", text: "cancer_risk_text" },
		empty: { value: "empty_calories", text: "empty_calories_text" }
	};

	/** Параметры общей оценки — отдельная пара, вне списка категорий */
	var OVERALL_PARAMS = {
		value: "average_score",
		text: "average_score_text"
	};

	/** Параметр с почтой: письмо со страницы результатов уходит по нему */
	var EMAIL_PARAM = "email";

	/**
	 * Параметр с идентификатором прохождения опроса.
	 *
	 * Страница результатов открывается по прямой ссылке, поэтому её легко
	 * перезагрузить или открыть повторно из истории. Приёмник письма отличает
	 * такое повторное открытие от нового прохождения по этому идентификатору
	 * и второй раз результаты на почту не отправляет.
	 */
	var QUESTIONNAIRE_ID_PARAM = "qid";

	/** Длина идентификатора прохождения в знаках */
	var QUESTIONNAIRE_ID_LENGTH = 10;

	/**
	 * Случайный идентификатор прохождения — строка из 10 цифр.
	 *
	 * Берём crypto.getRandomValues, где он есть; Math.random остаётся
	 * запасным вариантом для старых браузеров. Первая цифра не нулевая —
	 * иначе идентификатор потеряет разряд везде, где его прочитают числом.
	 *
	 * @returns {string} идентификатор из QUESTIONNAIRE_ID_LENGTH цифр
	 */
	function generateQuestionnaireId() {
		var digits = "";
		var crypto = global.crypto;
		var i;

		if (crypto && typeof crypto.getRandomValues === "function") {
			var values = new Uint32Array(QUESTIONNAIRE_ID_LENGTH);
			crypto.getRandomValues(values);
			for (i = 0; i < QUESTIONNAIRE_ID_LENGTH; i++) {
				digits += String(values[i] % 10);
			}
		} else {
			for (i = 0; i < QUESTIONNAIRE_ID_LENGTH; i++) {
				digits += String(Math.floor(Math.random() * 10));
			}
		}

		if (digits.charAt(0) === "0") {
			digits = String(1 + Math.floor(Math.random() * 9)) + digits.slice(1);
		}

		return digits;
	}

	/**
	 * Зона балла → номер варианта текста в ACF.
	 *
	 * Тексты заведены от худшего к лучшему: text1 разбирает высокий риск,
	 * text3 — низкий. Это обратно порядку самих баллов, где выше значит хуже,
	 * поэтому зоны и номера здесь намеренно идут навстречу друг другу.
	 */
	var LEVEL_TEXT_NUMBER = { low: 3, medium: 2, high: 1 };

	/** Балл в том виде, в каком его ждёт шаблон: целое, умноженное на 10 */
	function scaledValue(average) {
		return String(Math.round(average * 10));
	}

	/**
	 * Собирает адрес страницы результатов со всеми параметрами.
	 *
	 * @param {string} baseUrl адрес страницы результатов — относительный или
	 *        абсолютный (при переезде на прод меняется только он)
	 * @param {Object} result результат calculateQuestionnaireResult()
	 * @param {string} [email] почта для отправки письма со страницы результатов
	 * @param {string} [questionnaireId] идентификатор прохождения; если не
	 *        передан, генерируется новый — передавайте свой там, где на одно
	 *        прохождение адрес собирается несколько раз
	 * @returns {string} готовый URL с параметрами
	 */
	function buildResultUrl(baseUrl, result, email, questionnaireId) {
		var url = new URL(baseUrl, global.location.href);
		var params = url.searchParams;

		params.set(OVERALL_PARAMS.value, scaledValue(result.overall.average));
		params.set(
			OVERALL_PARAMS.text,
			String(LEVEL_TEXT_NUMBER[result.overall.level])
		);

		result.categories.forEach(function (category) {
			var names = RESULT_PARAMS[category.key];
			if (!names) return;

			params.set(names.value, scaledValue(category.average));
			params.set(names.text, String(LEVEL_TEXT_NUMBER[category.level]));
		});

		if (email) params.set(EMAIL_PARAM, email);

		params.set(
			QUESTIONNAIRE_ID_PARAM,
			questionnaireId || generateQuestionnaireId()
		);

		return url.toString();
	}

	global.MoveatQuestionnaireScoring = {
		QUESTION_COUNT: QUESTION_COUNT,
		CATEGORIES: CATEGORIES,
		ANSWER_SCORES: ANSWER_SCORES,
		OVERALL_SCALE: OVERALL_SCALE,
		OVERALL_THRESHOLDS: OVERALL_THRESHOLDS,
		calculateQuestionnaireResult: calculateQuestionnaireResult,
		RESULT_PARAMS: RESULT_PARAMS,
		OVERALL_PARAMS: OVERALL_PARAMS,
		EMAIL_PARAM: EMAIL_PARAM,
		QUESTIONNAIRE_ID_PARAM: QUESTIONNAIRE_ID_PARAM,
		QUESTIONNAIRE_ID_LENGTH: QUESTIONNAIRE_ID_LENGTH,
		generateQuestionnaireId: generateQuestionnaireId,
		buildResultUrl: buildResultUrl
	};
})(window);
