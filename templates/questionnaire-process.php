<?php
/*
 	Template Name: Опросник "Оценка качества питания"
 	Template Post Type: post, page
 	Description: Шаблон страницы прохождения опросника о качестве питания
*/

/*
	Тексты опросника — в словарях assets/dictionaries/templates/questionnaire.
	Шаблон один на все языки: Polylang создаёт отдельную страницу-перевод, ей
	назначается этот же шаблон, а язык подставляет moveat_current_lang().

	Порядок вопросов и вариантов задаётся словарём и менять его нельзя: баллы
	в assets/scripts/js/modules/questionnaire-scoring.js (таблица ANSWER_SCORES)
	привязаны к позиции — [номер вопроса][номер варианта].
*/
$moveat_quest     = moveat_dictionary( 'templates/questionnaire' );
$moveat_questions = isset( $moveat_quest['questions'] ) ? (array) $moveat_quest['questions'] : [];

/*
	Адрес страницы результатов, куда уходит пользователь после ввода почты.
	Ищем страницу по её шаблону — тогда при смене слага ничего править
	не придётся. Пока такой страницы нет, работает запасной адрес ниже.
*/
/*
	suppress_filters обходит языковой фильтр Polylang: на этом шаге нам нужна
	любая страница с нужным шаблоном как точка отсчёта. Иначе на /uk/ выборка
	была бы пустой до тех пор, пока результатам не сделают перевод.
	Сортировка по ID — чтобы при нескольких таких страницах выбиралась одна
	и та же (самая старая, то есть исходная).
*/
$moveat_results_ids = get_posts( [
	'post_type'        => 'page',
	'post_status'      => 'publish',
	'numberposts'      => 1,
	'meta_key'         => '_wp_page_template',
	'meta_value'       => 'templates/questionnaire-results-with-modal.php',
	'fields'           => 'ids',
	'orderby'          => 'ID',
	'order'            => 'ASC',
	'suppress_filters' => true,
] );

$moveat_results_id = ! empty( $moveat_results_ids ) ? (int) $moveat_results_ids[0] : 0;

/*
	А вот языковую версию спрашиваем у Polylang: с украинского опросника
	уводим на украинские результаты, с русского — на русские. Пока перевода
	страницы результатов нет, pll_get_post() ничего не отдаёт и остаётся
	найденная выше страница — человек увидит результаты на другом языке,
	но дойдёт до них.
*/
if ( $moveat_results_id && function_exists( 'pll_get_post' ) ) {
	$moveat_results_translation = pll_get_post( $moveat_results_id );

	if ( $moveat_results_translation ) {
		$moveat_results_id = (int) $moveat_results_translation;
	}
}

$moveat_results_url = $moveat_results_id
	? get_permalink( $moveat_results_id )
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
				<p class="questprocess__eyebrow">
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'eyebrow' ) ); ?>
				</p>
				<h1 class="questprocess__title">
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'title' ) ); ?>
				</h1>
				<p class="questprocess__subtitle">
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'subtitle' ) ); ?>
				</p>
			</div>

			<!-- ── Прогресс-бар с точками ──────────────────────────── -->
			<div
				class="questprocess__progress quest-progress"
				role="progressbar"
				aria-valuemin="0"
				aria-valuemax="100"
				aria-valuenow="0"
				aria-label="<?php echo esc_attr( moveat_dictionary_text( $moveat_quest, 'progress_label' ) ); ?>"
				data-quest-progress>
				<div class="quest-progress__head">
					<?php
						/*
							Счётчик перерисовывает questionnaire-process.js: он
							подставляет в этот шаблон номер текущего вопроса
							({current}) и общее их число ({total}). В разметке
							оставляем первый шаг — он виден до запуска скрипта.
						*/
						$moveat_counter = moveat_dictionary_text( $moveat_quest, 'counter', 'Вопрос {current} из {total}' );
					?>
					<span
						class="quest-progress__counter"
						data-quest-counter
						data-counter-format="<?php echo esc_attr( $moveat_counter ); ?>">
						<?php
							echo esc_html( str_replace(
								[ '{current}', '{total}' ],
								[ '1', (string) count( $moveat_questions ) ],
								$moveat_counter
							) );
						?>
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
				<?php
					$moveat_hint = moveat_dictionary_text( $moveat_quest, 'hint' );

					foreach ( $moveat_questions as $moveat_index => $moveat_step ) :
						$moveat_number  = $moveat_index + 1;
						$moveat_options = isset( $moveat_step['options'] ) ? (array) $moveat_step['options'] : [];
				?>
				<!-- Вопрос <?php echo (int) $moveat_number; ?> -->
				<fieldset class="questprocess__step" data-step data-type="single">
					<legend class="questprocess__question">
						<?php echo esc_html( isset( $moveat_step['question'] ) ? $moveat_step['question'] : '' ); ?>
					</legend>
					<p class="questprocess__hint"><?php echo esc_html( $moveat_hint ); ?></p>
					<div class="questprocess__options">
						<?php foreach ( $moveat_options as $moveat_option_index => $moveat_option ) : ?>
						<label class="option-card">
							<input
								class="option-card__input"
								type="radio"
								name="q<?php echo (int) $moveat_number; ?>"
								value="<?php echo (int) $moveat_option_index + 1; ?>" />
							<span class="option-card__marker" aria-hidden="true"></span>
							<span class="option-card__text"><?php echo esc_html( $moveat_option ); ?></span>
						</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
				<?php endforeach; ?>
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
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'back' ) ); ?>
				</button>
				<?php
					$moveat_next   = moveat_dictionary_text( $moveat_quest, 'next' );
					$moveat_finish = moveat_dictionary_text( $moveat_quest, 'finish' );
				?>
				<button
					type="button"
					class="questprocess__next primary-button unactive"
					data-quest-next
					data-label-next="<?php echo esc_attr( $moveat_next ); ?>"
					data-label-finish="<?php echo esc_attr( $moveat_finish ); ?>"
					disabled>
					<span data-quest-next-label><?php echo esc_html( $moveat_next ); ?></span>
				</button>
			</div>

			<p class="questprocess__note">
				<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'note' ) ); ?>
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
				aria-label="<?php echo esc_attr( moveat_dictionary_text( $moveat_quest, 'modal_close' ) ); ?>"
				data-email-modal-close>
				&times;
			</button>
			<h2 class="email-modal__title" id="quest-email-title">
				<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'modal_title' ) ); ?>
			</h2>
			<p class="email-modal__desc">
				<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'modal_desc' ) ); ?>
			</p>
			<form class="email-modal__form" data-email-form novalidate>
				<label class="email-modal__label" for="quest-email">
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'email_label' ) ); ?>
				</label>
				<input
					class="form-input email-modal__input"
					type="email"
					id="quest-email"
					name="email"
					placeholder="<?php echo esc_attr( moveat_dictionary_text( $moveat_quest, 'email_holder', 'name@example.com' ) ); ?>"
					autocomplete="email"
					inputmode="email"
					aria-describedby="quest-email-error"
					data-email-input />
				<p
					class="email-modal__error"
					id="quest-email-error"
					role="alert"
					data-email-error>
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'email_error' ) ); ?>
				</p>
				<button
					type="submit"
					class="email-modal__submit primary-button unactive"
					data-email-submit
					disabled>
					<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'email_submit' ) ); ?>
				</button>
			</form>
			<p class="email-modal__note">
				<?php echo esc_html( moveat_dictionary_text( $moveat_quest, 'modal_note' ) ); ?>
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
