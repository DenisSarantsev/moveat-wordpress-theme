<?php
/*
 	Template Name: Результаты опроса в чат-боте
 	Template Post Type: post, page
 	Description: Шаблон страницы результатов опроса через чат-бот
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
			<button class="questresult__button primary-button">
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("general-conclusions-link-1_{$__i}");
							$g_text = get_field("general-conclusions-text-1_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
					?>
			</div>
			<div class="conclusions__subtitle average-text-container">
					<?php the_field('general-conclusions-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("general-conclusions-link-2_{$__i}");
							$g_text = get_field("general-conclusions-text-2_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
					?>
			</div>		
			<div class="conclusions__subtitle average-text-container">
					<?php the_field('general-conclusions-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("general-conclusions-link-3_{$__i}");
							$g_text = get_field("general-conclusions-text-3_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
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
	<?php
		$__desc = get_field('metabolic-disorder-description');
		if ( trim( $__desc ) !== '' ) {
			echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
		}
	?>
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
			for ($__i = 1; $__i <= 5; $__i++) {
				$m_link = get_field("metabolic-disorder-link-1_{$__i}");
				$m_text = get_field("metabolic-disorder-text-1_{$__i}");
				if ( ! empty( $m_link ) && ! empty( $m_text ) ) {
					echo '<a href="' . esc_url( $m_link ) . '" class="main-graphic__post-link">' . esc_html( $m_text ) . '</a>';
				}
			}
		?>
	</div>
	<div class="main-graphic__result-description metab-text-container ">
		<?php the_field("metabolic-disorder-text2") ?>
		<?php 
			for ($__i = 1; $__i <= 5; $__i++) {
				$m_link = get_field("metabolic-disorder-link-2_{$__i}");
				$m_text = get_field("metabolic-disorder-text-2_{$__i}");
				if ( ! empty( $m_link ) && ! empty( $m_text ) ) {
					echo '<a href="' . esc_url( $m_link ) . '" class="main-graphic__post-link">' . esc_html( $m_text ) . '</a>';
				}
			}
		?>
	</div>
	<div class="main-graphic__result-description metab-text-container ">
		<?php the_field("metabolic-disorder-text3") ?>
		<?php 
			for ($__i = 1; $__i <= 5; $__i++) {
				$m_link = get_field("metabolic-disorder-link-3_{$__i}");
				$m_text = get_field("metabolic-disorder-text-3_{$__i}");
				if ( ! empty( $m_link ) && ! empty( $m_text ) ) {
					echo '<a href="' . esc_url( $m_link ) . '" class="main-graphic__post-link">' . esc_html( $m_text ) . '</a>';
				}
			}
		?>
	</div>

</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Системное воспаление
				</h3>
			<?php
				$__desc = get_field('systemic-inflammation-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$s_link = get_field("systemic-inflammation-link-1_{$__i}");
							$s_text = get_field("systemic-inflammation-text-1_{$__i}");
							if ( ! empty( $s_link ) && ! empty( $s_text ) ) {
								echo '<a href="' . esc_url( $s_link ) . '" class="main-graphic__post-link">' . esc_html( $s_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description inflammation-text-container ">
					<?php the_field('systemic-inflammation-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$s_link = get_field("systemic-inflammation-link-2_{$__i}");
							$s_text = get_field("systemic-inflammation-text-2_{$__i}");
							if ( ! empty( $s_link ) && ! empty( $s_text ) ) {
								echo '<a href="' . esc_url( $s_link ) . '" class="main-graphic__post-link">' . esc_html( $s_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description inflammation-text-container ">
					<?php the_field('systemic-inflammation-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$s_link = get_field("systemic-inflammation-link-3_{$__i}");
							$s_text = get_field("systemic-inflammation-text-3_{$__i}");
							if ( ! empty( $s_link ) && ! empty( $s_text ) ) {
								echo '<a href="' . esc_url( $s_link ) . '" class="main-graphic__post-link">' . esc_html( $s_text ) . '</a>';
							}
						}
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Уровень закисления
				</h3>
			<?php
				$__desc = get_field('acidification-level-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$a_link = get_field("acidification-level-link-1_{$__i}");
							$a_text = get_field("acidification-level-text-1_{$__i}");
							if ( ! empty( $a_link ) && ! empty( $a_text ) ) {
								echo '<a href="' . esc_url( $a_link ) . '" class="main-graphic__post-link">' . esc_html( $a_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description acidification-text-container">
					<?php the_field('acidification-level-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$a_link = get_field("acidification-level-link-2_{$__i}");
							$a_text = get_field("acidification-level-text-2_{$__i}");
							if ( ! empty( $a_link ) && ! empty( $a_text ) ) {
								echo '<a href="' . esc_url( $a_link ) . '" class="main-graphic__post-link">' . esc_html( $a_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description acidification-text-container">
					<?php the_field('acidification-level-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$a_link = get_field("acidification-level-link-3_{$__i}");
							$a_text = get_field("acidification-level-text-3_{$__i}");
							if ( ! empty( $a_link ) && ! empty( $a_text ) ) {
								echo '<a href="' . esc_url( $a_link ) . '" class="main-graphic__post-link">' . esc_html( $a_text ) . '</a>';
							}
						}
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Гликемичность рациона
				</h3>
			<?php
				$__desc = get_field('glycemic-level-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("glycemic-level-link-1_{$__i}");
							$g_text = get_field("glycemic-level-text-1_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description glycemic-text-container">
					<?php the_field('glycemic-level-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("glycemic-level-link-2_{$__i}");
							$g_text = get_field("glycemic-level-text-2_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description glycemic-text-container">
					<?php the_field('glycemic-level-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$g_link = get_field("glycemic-level-link-3_{$__i}");
							$g_text = get_field("glycemic-level-text-3_{$__i}");
							if ( ! empty( $g_link ) && ! empty( $g_text ) ) {
								echo '<a href="' . esc_url( $g_link ) . '" class="main-graphic__post-link">' . esc_html( $g_text ) . '</a>';
							}
						}
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Риск ускоренного старения
				</h3>
			<?php
				$__desc = get_field('accelerated-aging-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$aa_link = get_field("accelerated-aging-link-1_{$__i}");
							$aa_text = get_field("accelerated-aging-text-1_{$__i}");
							if ( ! empty( $aa_link ) && ! empty( $aa_text ) ) {
								echo '<a href="' . esc_url( $aa_link ) . '" class="main-graphic__post-link">' . esc_html( $aa_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description accelerated-text-container">
					<?php the_field('accelerated-aging-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$aa_link = get_field("accelerated-aging-link-2_{$__i}");
							$aa_text = get_field("accelerated-aging-text-2_{$__i}");
							if ( ! empty( $aa_link ) && ! empty( $aa_text ) ) {
								echo '<a href="' . esc_url( $aa_link ) . '" class="main-graphic__post-link">' . esc_html( $aa_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description accelerated-text-container">
					<?php the_field('accelerated-aging-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$aa_link = get_field("accelerated-aging-link-3_{$__i}");
							$aa_text = get_field("accelerated-aging-text-3_{$__i}");
							if ( ! empty( $aa_link ) && ! empty( $aa_text ) ) {
								echo '<a href="' . esc_url( $aa_link ) . '" class="main-graphic__post-link">' . esc_html( $aa_text ) . '</a>';
							}
						}
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Нарушение способности организма к самоочищению
				</h3>
			<?php
				$__desc = get_field('self-cleaning-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
					// Implemented properly below to avoid accidental parse conflicts
					for ($__i = 1; $__i <= 5; $__i++) {
						$sc_link = get_field("self-cleaning-link-1_{$__i}");
						$sc_text = get_field("self-cleaning-text-1_{$__i}");
						if ( ! empty( $sc_link ) && ! empty( $sc_text ) ) {
							echo '<a href="' . esc_url( $sc_link ) . '" class="main-graphic__post-link">' . esc_html( $sc_text ) . '</a>';
						}
					}
					?>
				</div>
				<div class="main-graphic__result-description self-cleaning-text-container">
					<?php the_field('self-cleaning-text2') ?>
					<?php
					for ($__i = 1; $__i <= 5; $__i++) {
						$sc_link = get_field("self-cleaning-link-2_{$__i}");
						$sc_text = get_field("self-cleaning-text-2_{$__i}");
						if ( ! empty( $sc_link ) && ! empty( $sc_text ) ) {
							echo '<a href="' . esc_url( $sc_link ) . '" class="main-graphic__post-link">' . esc_html( $sc_text ) . '</a>';
						}
					}
					?>
				</div>
				<div class="main-graphic__result-description self-cleaning-text-container">
					<?php the_field('self-cleaning-text3') ?>
					<?php
					for ($__i = 1; $__i <= 5; $__i++) {
						$sc_link = get_field("self-cleaning-link-3_{$__i}");
						$sc_text = get_field("self-cleaning-text-3_{$__i}");
						if ( ! empty( $sc_link ) && ! empty( $sc_text ) ) {
							echo '<a href="' . esc_url( $sc_link ) . '" class="main-graphic__post-link">' . esc_html( $sc_text ) . '</a>';
						}
					}
					?>
				</div>
			</div>

<!--  ------------------------  -->

<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Риск раковых заболеваний
				</h3>
			<?php
				$__desc = get_field('cancer-risk-description');
				if ( trim( $__desc ) !== '' ) {
					echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
				}
			?>
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
						for ($__i = 1; $__i <= 5; $__i++) {
							$cr_link = get_field("cancer-risk-link-1_{$__i}");
							$cr_text = get_field("cancer-risk-text-1_{$__i}");
							if ( ! empty( $cr_link ) && ! empty( $cr_text ) ) {
								echo '<a href="' . esc_url( $cr_link ) . '" class="main-graphic__post-link">' . esc_html( $cr_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description cancer-risk-text-container">
					<?php the_field('cancer-risk-text2') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$cr_link = get_field("cancer-risk-link-2_{$__i}");
							$cr_text = get_field("cancer-risk-text-2_{$__i}");
							if ( ! empty( $cr_link ) && ! empty( $cr_text ) ) {
								echo '<a href="' . esc_url( $cr_link ) . '" class="main-graphic__post-link">' . esc_html( $cr_text ) . '</a>';
							}
						}
					?>
				</div>
				<div class="main-graphic__result-description cancer-risk-text-container">
					<?php the_field('cancer-risk-text3') ?>
					<?php 
						for ($__i = 1; $__i <= 5; $__i++) {
							$cr_link = get_field("cancer-risk-link-3_{$__i}");
							$cr_text = get_field("cancer-risk-text-3_{$__i}");
							if ( ! empty( $cr_link ) && ! empty( $cr_text ) ) {
								echo '<a href="' . esc_url( $cr_link ) . '" class="main-graphic__post-link">' . esc_html( $cr_text ) . '</a>';
							}
						}
					?>
				</div>
			</div>

<!--  ------------------------  -->

			<div class="graphics__main-graphic graphics__content main-graphic">
				<h3 class="main-graphic__title">
					Количество пустых калорий в пище
				</h3>
				<?php
					$__desc = get_field('quantity-calories-description');
					if ( trim( $__desc ) !== '' ) {
						echo '<div class="main-graphic__description">' . apply_filters('the_content', $__desc) . '</div>';
					}
				?>
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
					for ($__i = 1; $__i <= 5; $__i++) {
						$qc_link = get_field("quantity-calories-link-1_{$__i}");
						$qc_text = get_field("quantity-calories-text-1_{$__i}");
						if ( ! empty( $qc_link ) && ! empty( $qc_text ) ) {
							echo '<a href="' . esc_url( $qc_link ) . '" class="main-graphic__post-link">' . esc_html( $qc_text ) . '</a>';
						}
					}
					?>
				</div>
				<div class="main-graphic__result-description quantity-calories-text-container">
					<?php the_field('quantity-calories-text2') ?>
					<?php
					for ($__i = 1; $__i <= 5; $__i++) {
						$qc_link = get_field("quantity-calories-link-2_{$__i}");
						$qc_text = get_field("quantity-calories-text-2_{$__i}");
						if ( ! empty( $qc_link ) && ! empty( $qc_text ) ) {
							echo '<a href="' . esc_url( $qc_link ) . '" class="main-graphic__post-link">' . esc_html( $qc_text ) . '</a>';
						}
					}
					?>
				</div>
				<div class="main-graphic__result-description quantity-calories-text-container">
					<?php the_field('quantity-calories-text3') ?>
					<?php
					for ($__i = 1; $__i <= 5; $__i++) {
						$qc_link = get_field("quantity-calories-link-3_{$__i}");
						$qc_text = get_field("quantity-calories-text-3_{$__i}");
						if ( ! empty( $qc_link ) && ! empty( $qc_text ) ) {
							echo '<a href="' . esc_url( $qc_link ) . '" class="main-graphic__post-link">' . esc_html( $qc_text ) . '</a>';
						}
					}
					?>
				</div>
			</div>

			<!-- Контент из редактора -->
			<?php
				// Выводим весь контент записи/страницы вместо шорткода баннера,
				// обернутый в контейнер, только если контент не пустой.
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						ob_start();
						the_content(); // возвращает уже отфильтрованный контент (wpautop, shortcodes и т.д.)
						$processed = ob_get_clean();
						// проверяем на пустой контент (без HTML)
						if ( trim( strip_tags( $processed ) ) !== '' ) {
							echo '<div class="quest-results-content">' . $processed . '</div>';
						}
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

<!-- Скролл при нажатии на кнопку в вступительном блоке -->
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const btn = document.querySelector('.questresult__button.primary-button');
		const target = document.querySelector('.conclusions');
		if (!btn || !target) return;
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			// try to account for fixed header height
			const header = document.querySelector('[data-header]');
			const headerHeight = header ? header.getBoundingClientRect().height : 0;
			const top = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20; // 20px extra offset
			window.scrollTo({ top: top, behavior: 'smooth' });
			// set focus for accessibility
			if (!target.hasAttribute('tabindex')) {
				target.setAttribute('tabindex', '-1');
			}
			target.focus({ preventScroll: true });
		});
	});
</script>

<?php get_footer(); ?>

