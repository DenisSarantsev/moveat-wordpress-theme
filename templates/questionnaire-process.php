<?php
/*
 	Template Name: Опросник "Оценка качества питания"
 	Template Post Type: post, page
 	Description: Шаблон страницы прохождения опросника о качестве питания
*/

/*
	Адрес страницы результатов, куда уходит пользователь после ввода почты.
	Ищем страницу по её шаблону — тогда при смене слага ничего править
	не придётся. Пока такой страницы нет, работает запасной адрес ниже.
*/
$moveat_results_pages = get_pages( [
	'meta_key'   => '_wp_page_template',
	'meta_value' => 'templates/questionnaire-results-with-modal.php',
	'number'     => 1,
] );

$moveat_results_url = ! empty( $moveat_results_pages )
	? get_permalink( $moveat_results_pages[0] )
	: home_url( '/resultat-kachestvo-pitaniya/' );

get_header(); ?>

<main class="questprocess">
	<div class="questprocess__wrapper">
		<div
			class="questprocess__container"
			data-questprocess
			data-result-url="<?php echo esc_url( $moveat_results_url ); ?>">
			<!-- ── Шапка опросника ─────────────────────────────────── -->
			<div class="questprocess__head">
				<p class="questprocess__eyebrow">Экспресс-анализ питания</p>
				<h1 class="questprocess__title">Опросник здоровья</h1>
				<p class="questprocess__subtitle">
					Ответьте на несколько вопросов о своём рационе и самочувствии —
					и мы покажем, как питание влияет на вашу энергию, здоровье и
					скорость старения.
				</p>
			</div>

			<!-- ── Прогресс-бар с точками ──────────────────────────── -->
			<div
				class="questprocess__progress quest-progress"
				role="progressbar"
				aria-valuemin="0"
				aria-valuemax="100"
				aria-valuenow="0"
				aria-label="Прогресс прохождения опросника"
				data-quest-progress>
				<div class="quest-progress__head">
					<span class="quest-progress__counter" data-quest-counter>
						Вопрос 1 из 32
					</span>
					<span class="quest-progress__percent" data-quest-percent>0%</span>
				</div>
				<div class="quest-progress__track">
					<span class="quest-progress__line" aria-hidden="true"></span>
					<span
						class="quest-progress__fill"
						aria-hidden="true"
						data-quest-fill></span>
					<div
						class="quest-progress__dots"
						aria-hidden="true"
						data-quest-dots></div>
				</div>
			</div>

			<!-- ── Шаги опросника ──────────────────────────────────── -->
			<form class="questprocess__form" data-quest-form novalidate>
				<!-- Вопрос 1 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						У вас бывают резкие приступы голода?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q1"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, несколько раз в день.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q1"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q1"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Не бывает.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 2 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Бывает ли у вас вечерний жор?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q2"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, почти каждый день.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q2"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–3 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q2"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Не бывает.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 3 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Хватает ли вам энергии в течение дня?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q3"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Чаще всего — не хватает.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q3"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								В основном да, но хотелось бы больше.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q3"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да! Всегда! Я — энерджайзер.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 4 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Бывает ли у вас очень сильное желание поесть сладкого?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q4"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, бывает и регулярно.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q4"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Иногда срывает.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q4"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Нет, практически не бывает.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 5 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Хочется ли вам заниматься спортом?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q5"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Нет, совсем не хочется.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q5"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q5"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, более 2 раз в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 6 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Пропадает ли у вас концентрация на работе и/или появляется ли сонливость (обычно с 10 до 12 и с 15 до 17 дня)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q6"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, регулярно.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q6"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Иногда теряю концентрацию и почти засыпаю.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q6"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Нет, всё ОК: всегда бодрячком.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 7 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Часто ли вы переедаете и не можете остановиться, когда едите?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q7"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, часто.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q7"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Иногда.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q7"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Практически никогда.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 8 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Хочется ли вам перекусывать в течение дня?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q8"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, часто.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q8"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Иногда.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q8"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Практически никогда.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 9 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Страдаете ли вы от воспалений кожи, высыпаний или прыщиков?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q9"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да, кожа воспалённая и/или часто бывают высыпания или акне.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q9"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Иногда возникают воспаления или высыпания.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q9"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Нет, всё ОК.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 10 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Страдаете ли вы от выпадения волос?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q10"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да, волосы выпадают довольно сильно.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q10"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Иногда выпадают в волнующем меня количестве.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q10"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Нет, всё ОК: несколько волосков в день.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 11 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Есть ли у вас проблемы с лишним весом?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q11"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да, я вешу существенно больше, чем хотелось бы.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q11"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да, есть небольшая проблема, но мне пока удаётся вес
								контролировать.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q11"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Нет, мой вес оптимален.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 12 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Если у вас есть лишний вес, то легко ли вам сбросить несколько килограммов и контролировать вес?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q12"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Да, мне надо очень напрячься, чтобы сбросить хоть немного, и
								вес постоянно прибавляется.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q12"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Мне относительно просто сбросить, но вес приходится всё время
								контролировать.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q12"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Вес у меня практически не прибавляется, и лишнее сбросить
								очень легко.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 13 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Любите ли вы солёную пищу?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q13"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Очень люблю солёное.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q13"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Люблю умеренно солёное.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q13"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Полное равнодушие к солёному.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 14 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько воды и чая вы выпиваете в течение дня?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q14"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Менее 1 л.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q14"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 л.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q14"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Более 2 л.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 15 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Часто ли вы болеете простудными заболеваниями и/или гриппом, ангиной?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q15"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Более 4 раз в год.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q15"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">2–4 раза в год.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q15"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1 раз в год или реже.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 16 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Бывают ли у вас запоры?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q16"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Да, регулярно.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q16"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Чаще 1–2 раз в месяц.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q16"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Нет или реже, чем раз в месяц.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 17 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Как часто у вас бывает отёчность лица, рук, под и над глазами и/или ног?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q17"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								1–2 раза в неделю или чаще.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q17"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 раза в месяц.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q17"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Никогда.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 18 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						На каком жире/масле вы чаще всего готовите (жарите или тушите)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q18"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Смалец, сало, сливочное, гхи.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q18"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Рафинированные подсолнечное, кукурузное, рапсовое, кунжутное и
								другие, кроме оливкового.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q18"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Оливковое.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 19 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Какой заправкой вы заправляете чаще всего салаты?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q19"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Майонезы, дрессинги.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q19"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Подсолнечное, кукурузное, виноградное, кунжутное и другие,
								кроме оливкового.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q19"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Оливковое (экстра вёрджин или салатное).
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 20 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в день вы едите белковые продукты животного происхождения (мясо, птицу, рыбу, молочное, яйца)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q20"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3 и больше раз в день.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q20"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 раза в день.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q20"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–1 раз в день.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 21 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы употребляете молоко (коровье или козье, именно молоко, а не кисломолочные продукты)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q21"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">6 и более раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q21"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3–5 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q21"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–2 раза в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 22 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы едите мясопродукты (колбасы, сосиски, гамбургеры) и мясные копчёности?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q22"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">6 и более раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q22"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–5 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q22"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Не ем вообще.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 23 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы едите красное и/или белое мясо?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q23"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">6 и более раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q23"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3–5 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q23"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–2 раза в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 24 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько зелени в неделю вы съедаете в среднем?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q24"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–100 г.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q24"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">100–300 г.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q24"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">300 г и более.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 25 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько овощей в неделю вы съедаете (без картошки) в среднем?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q25"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–1 кг.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q25"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 кг.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q25"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">2 кг и более.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 26 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько фруктов и ягод в неделю вы съедаете в среднем?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q26"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–1 кг.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q26"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">1–2 кг.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q26"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">2 кг и более.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 27 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Какое соотношение веса овощей и картошки к кашам, животной пище вместе взятой у вас за день?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q27"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Овощей/картошки существенно меньше, чем каш/мяса, рыбы,
								молочки, яиц вместе взятых.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q27"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Овощей/картошки приблизительно столько же, как и каш/мяса,
								рыбы, молочки, яиц вместе взятых.
							</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q27"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">
								Овощей/картошки в 2 и более раз больше по весу, чем каш/мяса,
								рыбы, молочки, яиц вместе взятых.
							</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 28 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы едите орехи и семечки (в любом виде)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q28"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–1 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q28"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">2–4 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q28"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Более 5 раз в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 29 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы едите бобовые (в любом виде)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q29"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–1 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q29"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">2–4 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q29"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">Более 5 раз в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 30 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы едите не цельнозерновые хлеб, хлебцы, каши и пасту (макаронные изделия)?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q30"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">7 и более раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q30"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3–6 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q30"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–2 раза в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 31 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы употребляете добавки морских омега-3 (DNA и DHA) или рыбий жир?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q31"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–2 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q31"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3–5 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q31"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">6 и более раз в неделю.</span>
						</label>
					</div>
				</fieldset>

				<!-- Вопрос 32 -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						Сколько раз в неделю вы употребляете льняное семя и грецкий орех или льняное, рыжейное масла или масло грецкого ореха?
					</legend>
					<p class="questprocess__hint">Выберите один вариант</p>
					<div class="questprocess__options">
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q32"
								value="1" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">0–2 раза в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q32"
								value="2" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">3–5 раз в неделю.</span>
						</label>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q32"
								value="3" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text">6 и более раз в неделю.</span>
						</label>
					</div>
				</fieldset>
			</form>

			<!-- ── Навигация ───────────────────────────────────────── -->
			<div class="questprocess__nav">
				<button
					type="button"
					class="questprocess__back"
					data-quest-back
					hidden>
					<span class="questprocess__back-icon" aria-hidden="true">
						<svg
							width="16"
							height="16"
							viewBox="0 0 16 16"
							fill="none"
							xmlns="http://www.w3.org/2000/svg">
							<path
								d="M10 13L5 8l5-5"
								stroke="currentColor"
								stroke-width="1.8"
								stroke-linecap="round"
								stroke-linejoin="round" />
						</svg>
					</span>
					Назад
				</button>
				<button
					type="button"
					class="questprocess__next primary-button unactive"
					data-quest-next
					data-label-next="Продолжить"
					data-label-finish="Узнать результат"
					disabled>
					<span data-quest-next-label>Продолжить</span>
				</button>
			</div>

			<p class="questprocess__note">
				Опросник анонимный — мы не сохраняем персональные данные и не
				передаём ответы третьим лицам.
			</p>
		</div>
	</div>
	<!-- Модалка: перед показом результатов просим e-mail.
	     Лежит вне .questprocess__container — у того анимация с transform,
	     а трансформированный предок ломает position: fixed. -->
	<div
		class="email-modal"
		role="dialog"
		aria-modal="true"
		aria-labelledby="quest-email-title"
		aria-hidden="true"
		hidden
		data-email-modal>
		<div class="email-modal__overlay" data-email-modal-overlay></div>
		<div class="email-modal__dialog" role="document">
			<button
				class="email-modal__close"
				type="button"
				aria-label="Закрыть"
				data-email-modal-close>
				&times;
			</button>
			<h2 class="email-modal__title" id="quest-email-title">
				Остался последний шаг
			</h2>
			<p class="email-modal__desc">
				Чтобы показать результаты теста, укажите свой e-mail — на него же
				придёт подробный разбор по каждой категории.
			</p>
			<form class="email-modal__form" data-email-form novalidate>
				<label class="email-modal__label" for="quest-email">
					Ваш e-mail
				</label>
				<input
					class="form-input email-modal__input"
					type="email"
					id="quest-email"
					name="email"
					placeholder="name@example.com"
					autocomplete="email"
					inputmode="email"
					aria-describedby="quest-email-error"
					data-email-input />
				<p
					class="email-modal__error"
					id="quest-email-error"
					role="alert"
					data-email-error>
					Проверьте адрес — похоже, в нём опечатка
				</p>
				<button
					type="submit"
					class="email-modal__submit primary-button unactive"
					data-email-submit
					disabled>
					Показать результаты
				</button>
			</form>
			<p class="email-modal__note">
				Адрес нужен только для отправки результатов — мы не передаём его
				третьим лицам.
			</p>
		</div>
	</div>
</main>

<?php
	/*
		Скрипты опросника: нужны только на этом шаблоне, поэтому подключаем
		здесь, а не в общем moveat_enqueue_scripts(). В общий бандл они
		намеренно не входят. wp_footer ещё не отработал, так что оба файла
		попадут в подвал страницы.

		Порядок важен: questionnaire-process.js берёт расчёт баллов и сборку
		адреса из questionnaire-scoring.js — отсюда зависимость.
	*/
	wp_enqueue_script(
		'moveat-questionnaire-scoring',
		get_template_directory_uri() . '/assets/scripts/js/modules/questionnaire-scoring.js',
		[],
		function_exists( 'moveat_asset_ver' ) ? moveat_asset_ver( 'assets/scripts/js/modules/questionnaire-scoring.js' ) : null,
		true
	);

	wp_enqueue_script(
		'moveat-questionnaire-process',
		get_template_directory_uri() . '/assets/scripts/js/modules/questionnaire-process.js',
		[ 'moveat-questionnaire-scoring' ],
		function_exists( 'moveat_asset_ver' ) ? moveat_asset_ver( 'assets/scripts/js/modules/questionnaire-process.js' ) : null,
		true
	);
?>

<?php get_footer(); ?>
