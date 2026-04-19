<?php
/*
 * Template Name: Результаты опроса в чат-боте
 * Template Post Type: post, page
 * Description: Шаблон страницы результатов опроса через чат-бот
 */
get_header(); ?>
<script>
	// Определяем высоту хедера
	document.addEventListener("DOMContentLoaded", () => {
		const headerHeight = document.querySelector("header").clientHeight;
		const screenHeight = document.documentElement.clientHeight;
		const mainBlock = document.querySelector(".questresult__title-block");
		const mainBlockHeight = screenHeight - headerHeight + 10;
		mainBlock.style.height = `${mainBlockHeight}px`;
	})
</script>

<main class="questresult">
	<div class="questresult__container">
		<div class="questresult__title-block">
			<div class="questresult__bg-container"></div>
			<img src="<?php echo get_template_directory_uri() ?>/assets/images/trees.webp" alt="" class="questresult__bg-image">
			<h1 class="questresult__title">
				Ваши результаты по 8 главным индикаторам здоровья готовы!
			</h1>
			<div class="questresult__subtitle">
				Спасибо за ваши ответы. Прежде, чем вы увидите ваши результаты, мы хотим настроить вас на серьёзный лад. Несмотря на кажущуюся простоту вопросов, сравнение содержимого вашей тарелки и состояния организма позволяет сделать достаточно обоснованные выводы о том, как ваше питание влияет на ваши моложавость, здоровье и энергичность.
			</div>
			<button class="questresult__button">
				Узнать результаты
			</button>
		</div>
		<div class="questresult__conclusions conclusions">
			<h2 class="conclusions__title">
				Общие выводы
			</h2>
			<div class="conclusions__subtitle average-text-container">
					<?php the_field('general-conclusions-text1') ?>
					<?php 
						if( have_rows('general-conclusions-links-1') ):
							while( have_rows('general-conclusions-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('general-conclusions-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('general-conclusions-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
			</div>
			<div class="conclusions__subtitle average-text-container">
					<?php the_field('general-conclusions-text2') ?>
					<?php 
						if( have_rows('general-conclusions-links-2') ):
							while( have_rows('general-conclusions-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('general-conclusions-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('general-conclusions-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
			</div>		
			<div class="conclusions__subtitle average-text-container">
					<?php the_field('general-conclusions-text3') ?>
					<?php 
						if( have_rows('general-conclusions-links-3') ):
							while( have_rows('general-conclusions-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('general-conclusions-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('general-conclusions-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
			</div>	
		</div>
		<div class="questresult__graphics graphics">
			<div class="graphics__title">
				Результаты
			</div>
			<div class="graphics__subtitle">
				А теперь мы подробнее расскажем о том, какие нарушения питания и состояния здоровья мы можем заподозрить после анализа ваших ответов
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
	<h3 class="main-graphic__title">
		Риск метаболического расстройства
	</h3>
	<div class="main-graphic__graphic graphic">
		<div class="graphic__container">
			<div class="graphic__actual-number-container metab-syndrome-scale">
				<div class="graphic__point-container">
					<div class="graphic__actual-number">4</div>
					<div class="graphic__triangle"></div>
				</div>
			</div>
			<div class="graphic__progress progress">
				<div class="progress__min">0</div>
				<div class="grayback progress-green"></div>
				<div class="strips progress-yellow"></div>
				<div class="progress-red"></div>
				<div class="progress__max">6.1</div>
			</div>
			<div class="graphic__low-high">
				<div class="graphic__low">Низкий</div>
				<div class="graphic__high">Высокий</div>
			</div>
		</div>
	</div>
	<div class="main-graphic__result-description metab-text-container">
		<?php the_field("metabolic-disorder-text1") ?>
		<?php 
			if( have_rows('metabolic-disorder-links-1') ):
				while( have_rows('metabolic-disorder-links-1') ) : the_row(); ?>
					<a href="<?php the_sub_field('metabolic-disorder-link-1') ?>" class="main-graphic__post-link">
						<?php the_sub_field('metabolic-disorder-text-1') ?>
					</a>
				<?php 
				endwhile;
			endif;
		?>
	</div>
	<div class="main-graphic__result-description metab-text-container ">
		<?php the_field("metabolic-disorder-text2") ?>
		<?php 
			if( have_rows('metabolic-disorder-links-2') ):
				while( have_rows('metabolic-disorder-links-2') ) : the_row(); ?>
					<a href="<?php the_sub_field('metabolic-disorder-link-2') ?>" class="main-graphic__post-link">
						<?php the_sub_field('metabolic-disorder-text-2') ?>
					</a>
				<?php 
				endwhile;
			endif;
		?>
	</div>
	<div class="main-graphic__result-description metab-text-container ">
		<?php the_field("metabolic-disorder-text3") ?>
		<?php 
			if( have_rows('metabolic-disorder-links-3') ):
				while( have_rows('metabolic-disorder-links-3') ) : the_row(); ?>
					<a href="<?php the_sub_field('metabolic-disorder-link-3') ?>" class="main-graphic__post-link">
						<?php the_sub_field('metabolic-disorder-text-3') ?>
					</a>
				<?php 
				endwhile;
			endif;
		?>
	</div>

</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Системное воспаление
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container system-inflammation-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">6.5</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description inflammation-text-container">
					<?php the_field('systemic-inflammation-text1') ?>
					<?php 
						if( have_rows('systemic-inflammation-links-1') ):
							while( have_rows('systemic-inflammation-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('systemic-inflammation-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('systemic-inflammation-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description inflammation-text-container ">
					<?php the_field('systemic-inflammation-text2') ?>
					<?php 
						if( have_rows('systemic-inflammation-links-2') ):
							while( have_rows('systemic-inflammation-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('systemic-inflammation-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('systemic-inflammation-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description inflammation-text-container ">
					<?php the_field('systemic-inflammation-text3') ?>
					<?php 
						if( have_rows('systemic-inflammation-links-3') ):
							while( have_rows('systemic-inflammation-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('systemic-inflammation-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('systemic-inflammation-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Уровень закисления
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container acidification-level-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">3.4</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description acidification-text-container">
					<?php the_field('acidification-level-text1') ?>
					<?php 
						if( have_rows('acidification-level-links-1') ):
							while( have_rows('acidification-level-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('acidification-level-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('acidification-level-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description acidification-text-container">
					<?php the_field('acidification-level-text2') ?>
					<?php 
						if( have_rows('acidification-level-links-2') ):
							while( have_rows('acidification-level-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('acidification-level-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('acidification-level-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description acidification-text-container">
					<?php the_field('acidification-level-text3') ?>
					<?php 
						if( have_rows('acidification-level-links-3') ):
							while( have_rows('acidification-level-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('acidification-level-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('acidification-level-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Гликемичность рациона
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container glycemic-level-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">5.2</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description glycemic-text-container">
					<?php the_field('glycemic-level-text1') ?>
					<?php 
						if( have_rows('glycemic-level-links-1') ):
							while( have_rows('glycemic-level-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('glycemic-level-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('glycemic-level-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description glycemic-text-container">
					<?php the_field('glycemic-level-text2') ?>
					<?php 
						if( have_rows('glycemic-level-links-2') ):
							while( have_rows('glycemic-level-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('glycemic-level-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('glycemic-level-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description glycemic-text-container">
					<?php the_field('glycemic-level-text3') ?>
					<?php 
						if( have_rows('glycemic-level-links-3') ):
							while( have_rows('glycemic-level-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('glycemic-level-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('glycemic-level-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Риск ускоренного старения
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container accelerated-aging-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">6</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description accelerated-text-container">
					<?php the_field('accelerated-aging-text1') ?>
					<?php 
						if( have_rows('accelerated-aging-links-1') ):
							while( have_rows('accelerated-aging-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('accelerated-aging-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('accelerated-aging-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description accelerated-text-container">
					<?php the_field('accelerated-aging-text2') ?>
					<?php 
						if( have_rows('accelerated-aging-links-2') ):
							while( have_rows('accelerated-aging-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('accelerated-aging-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('accelerated-aging-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description accelerated-text-container">
					<?php the_field('accelerated-aging-text3') ?>
					<?php 
						if( have_rows('accelerated-aging-links-3') ):
							while( have_rows('accelerated-aging-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('accelerated-aging-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('accelerated-aging-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Нарушение способности организма к самоочищению
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container self-cleaning-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">5.1</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description self-cleaning-text-container">
					<?php the_field('self-cleaning-text1') ?>
					<?php 
						if( have_rows('self-cleaning-links-1') ):
							while( have_rows('self-cleaning-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('self-cleaning-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('self-cleaning-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description self-cleaning-text-container">
					<?php the_field('self-cleaning-text2') ?>
					<?php 
						if( have_rows('self-cleaning-links-2') ):
							while( have_rows('self-cleaning-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('self-cleaning-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('self-cleaning-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description self-cleaning-text-container">
					<?php the_field('self-cleaning-text3') ?>
					<?php 
						if( have_rows('self-cleaning-links-3') ):
							while( have_rows('self-cleaning-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('self-cleaning-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('self-cleaning-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Риск раковых заболеваний
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container cancer-risk-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">5.6</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description cancer-risk-text-container">
					<?php the_field('cancer-risk-text1') ?>
					<?php 
						if( have_rows('cancer-risk-links-1') ):
							while( have_rows('cancer-risk-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('cancer-risk-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('cancer-risk-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description cancer-risk-text-container">
					<?php the_field('cancer-risk-text2') ?>
					<?php 
						if( have_rows('cancer-risk-links-2') ):
							while( have_rows('cancer-risk-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('cancer-risk-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('cancer-risk-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description cancer-risk-text-container">
					<?php the_field('cancer-risk-text3') ?>
					<?php 
						if( have_rows('cancer-risk-links-3') ):
							while( have_rows('cancer-risk-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('cancer-risk-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('cancer-risk-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

<!--  ------------------------  -->

			<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Количество пустых калорий в пище
				</h3>
				<div class="main-graphic__graphic graphic">
				<div class="graphic__container">
						<div class="graphic__actual-number-container quantity-calories-scale">
							<div class="graphic__point-container">
								<div class="graphic__actual-number">4</div>
								<div class="graphic__triangle"></div>
							</div>
						</div>
						<div class="graphic__progress progress">
							<div class="progress__min">0</div>
							<div class="grayback progress-green"></div>
							<div class="strips progress-yellow"></div>
							<div class="progress-red"></div>
							<div class="progress__max">4.8</div>
						</div>
						<div class="graphic__low-high">
							<div class="graphic__low">Низкий</div>
							<div class="graphic__high">Высокий</div>
						</div>
					</div>
				</div>
				<div class="main-graphic__result-description quantity-calories-text-container">
					<?php the_field('quantity-calories-text1') ?>
					<?php 
						if( have_rows('quantity-calories-links-1') ):
							while( have_rows('quantity-calories-links-1') ) : the_row(); ?>
								<a href="<?php the_sub_field('quantity-calories-link-1') ?>" class="main-graphic__post-link">
									<?php the_sub_field('quantity-calories-text-1') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description quantity-calories-text-container">
					<?php the_field('quantity-calories-text2') ?>
					<?php 
						if( have_rows('quantity-calories-links-2') ):
							while( have_rows('quantity-calories-links-2') ) : the_row(); ?>
								<a href="<?php the_sub_field('quantity-calories-link-2') ?>" class="main-graphic__post-link">
									<?php the_sub_field('quantity-calories-text-2') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
				<div class="main-graphic__result-description quantity-calories-text-container">
					<?php the_field('quantity-calories-text3') ?>
					<?php 
						if( have_rows('quantity-calories-links-3') ):
							while( have_rows('quantity-calories-links-3') ) : the_row(); ?>
								<a href="<?php the_sub_field('quantity-calories-link-3') ?>" class="main-graphic__post-link">
									<?php the_sub_field('quantity-calories-text-3') ?>
								</a>
							<?php 
							endwhile;
						endif;
					?>
				</div>
			</div>

			<!-- Баннер внизу страницы -->
			<?php
				if( function_exists('get_field') ) {
						$banner_shortcode = get_field('bottom-banner');
						if( $banner_shortcode ) {
								echo do_shortcode($banner_shortcode);
						}
				}
			?>

		</div>
	</div>
</main>

<script>
// Получаем в переменные информацию из адреса
let currentUrl = window.location.href;
let url = new URL(`${currentUrl}`);
let params = new URLSearchParams(url.search);
// let name = params.get("name");
let averageScore = params.get("average_score");
let averageScoreText = params.get("average_score_text");
let metabSyndrome = params.get("metab_syndrome");
let metabSyndromeText = params.get("metab_syndrome_text");
let inflamation = params.get("inflamation");
let inflamationText = params.get("inflamation_text");
let acidification = params.get("acidification");
let acidificationText = params.get("acidification_text");
let glycemicLevel = params.get("glycemic_level");
let glycemicLevelText = params.get("glycemic_level_text");
let riskOfAging = params.get("risk_of_aging");
let riskOfAgingText = params.get("risk_of_aging_text");
let cleanability = params.get("cleanability");
let cleanabilityText = params.get("cleanability_text");
let cancerRisk = params.get("cancer_risk");
let cancerRiskText = params.get("cancer_risk_text");
let emptyCalories = params.get("empty_calories");
let emptyCaloriesText = params.get("empty_calories_text");

// Получаем каждую шкалу в переменную:
const metabSyndromeScale = document.querySelector(".metab-syndrome-scale");
const systemInflammationScale = document.querySelector(".system-inflammation-scale");
const acidificationLevelScale = document.querySelector(".acidification-level-scale");
const glycemicLevelScale = document.querySelector(".glycemic-level-scale");
const acceleratedAgingScale = document.querySelector(".accelerated-aging-scale");
const selfCleaningScale = document.querySelector(".self-cleaning-scale");
const cancerRiskScale = document.querySelector(".cancer-risk-scale");
const quantityCaloriesScale = document.querySelector(".quantity-calories-scale");

// Посчитать ширину шкалы в процентах
function calculatePercentScaleWidth(actual, max, element) {
	let actualParam = +actual / 10;
	let percentWidth = Math.round(( actualParam / max ) * 100);
	let scaleElement = element.firstElementChild;

	if ( percentWidth > 100 ) {
		element.style.width = "100%";
	} else {
		element.style.width = `${percentWidth}%`;
	}

	if ( actual > max * 10 ) {
		scaleElement.firstElementChild.textContent = max;
	} else {
		scaleElement.firstElementChild.textContent = actualParam;
	}
	
}

// Вызываем функцию для каждого блока
calculatePercentScaleWidth(metabSyndrome, 6.1, metabSyndromeScale);
calculatePercentScaleWidth(inflamation, 6.5, systemInflammationScale);
calculatePercentScaleWidth(acidification, 3.4, acidificationLevelScale);
calculatePercentScaleWidth(glycemicLevel, 5.2, glycemicLevelScale);
calculatePercentScaleWidth(riskOfAging, 6, acceleratedAgingScale);
calculatePercentScaleWidth(cleanability, 5.1, selfCleaningScale);
calculatePercentScaleWidth(cancerRisk, 5.6, cancerRiskScale);
calculatePercentScaleWidth(emptyCalories, 4.8, quantityCaloriesScale);

// Получаем массивы с текстовыми контейнерами
const averageTextContainers = document.querySelectorAll(".average-text-container");
const metabTextContainers = document.querySelectorAll(".metab-text-container");
const inflammationTextContainers = document.querySelectorAll(".inflammation-text-container");
const acidificationTextContainers = document.querySelectorAll(".acidification-text-container");
const glycemicTextContainers = document.querySelectorAll(".glycemic-text-container");
const acceleratedAgeTextContainers = document.querySelectorAll(".accelerated-text-container");
const selfCleaningTextContainers = document.querySelectorAll(".self-cleaning-text-container");
const cancerRiskTextContainers = document.querySelectorAll(".cancer-risk-text-container");
const quantityCaloriesTextContainers = document.querySelectorAll(".quantity-calories-text-container");

// Функция для подстановки нужного варианта текста
function addActualText(array, textNumber) {
	for (let i = 0; i < array.length; i++) {
		if ( i !== +textNumber - 1 ) {
			array[i].classList.add("_hidden");
		} else {}
	}
}

// Вызываем функции для подстановки нужного варианта
addActualText(averageTextContainers, averageScoreText);
addActualText(metabTextContainers, metabSyndromeText);
addActualText(inflammationTextContainers, inflamationText);
addActualText(acidificationTextContainers, acidificationText);
addActualText(glycemicTextContainers, glycemicLevelText);
addActualText(acceleratedAgeTextContainers, riskOfAgingText);
addActualText(selfCleaningTextContainers, cleanabilityText);
addActualText(cancerRiskTextContainers, cancerRiskText);
addActualText(quantityCaloriesTextContainers, emptyCaloriesText);

// Прокрутка при нажатии на кнопку в главном блоке
const firstBlockButton = document.querySelector(".questresult__button");
const targetBlock = document.querySelector(".conclusions");
firstBlockButton.addEventListener("click", () => {
	targetBlock.scrollIntoView({ behavior: 'smooth' });
})


// Ссылка для теста
// https://moveat.expert/resultat-kachestvo-pitaniya/?=average_score=2&average_score_text=2&metab_syndrome=38&metab_syndrome_text=2&inflamation=48&inflamation_text=3&acidification=26&acidification_text=2&glycemic_level=34&glycemic_level_text=1&risk_of_aging=39&risk_of_aging_text=3&cleanability=21&cleanability_text=2&cancer_risk=35&cancer_risk_text=2&empty_calories=22&empty_calories_text=3

</script>

<?php get_footer(); ?>

