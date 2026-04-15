<?php
/*
	Template Name: Главная страница
	Template Post Type: page
*/
?>

<?php get_header(); ?>

	<?php
		$hero_bg_image    = get_field('hero_bg_image');
		$hero_bg_image_mobile    = get_field('hero_bg_image_mobile');
		$hero_title       = get_field('hero_title');
		$hero_description = get_field('hero_description');
		$hero_btn_text    = get_field('hero_button_text');
		$hero_btn_url     = get_field('hero_button_url');
		$hero_right_image = get_field('hero_right_image');
	?>
	<div class="hero">
		<div class="hero__wrapper">
			<div class="hero__bg-filter"></div> 
			<?php if ($hero_bg_image) : ?>
				<img class="hero__image desktop" src="<?php echo esc_url($hero_bg_image); ?>" alt="Image">
				<img class="hero__image mobile" src="<?php echo esc_url($hero_bg_image_mobile); ?>" alt="Image">
			<?php endif; ?>
			<div class="hero__container">
				<div class="hero__content">
					<div class="hero__left">
						<?php if ($hero_title) : ?>
							<h1 class="hero__title"><?php echo esc_html($hero_title); ?></h1>
						<?php endif; ?>
						<?php if ($hero_description) : ?>
							<p class="hero__description"><?php echo nl2br(esc_html($hero_description)); ?></p>
						<?php endif; ?>
						<?php if ($hero_btn_text && $hero_btn_url) : ?>
							<div class="hero__buttons">
								<a href="<?php echo esc_url($hero_btn_url); ?>" class="primary-button hero__button--primary"><?php echo esc_html($hero_btn_text); ?></a>
							</div>
						<?php endif; ?>
					</div>
					<?php if ($hero_right_image) : ?>
						<div class="hero__right">
							<img class="hero__right-image" src="<?php echo esc_url($hero_right_image); ?>" alt="max pogorelov">
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php
		$trust_title       = get_field('trust_title');
		$trust_description = get_field('trust_description');
	?>

	<div class="trust">
		<div class="trust__container">
			<?php if ($trust_title || $trust_description) : ?>
				<div class="trust__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($trust_title) : ?>
						<h2 class="trust__title section-title"><?php echo esc_html($trust_title); ?></h2>
					<?php endif; ?>
					<?php if ($trust_description) : ?>
						<p class="trust__description"><?php echo esc_html($trust_description); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="trust__cards">
				<?php
				$i = 1;
				while ($icon = get_field('trust_card_' . $i . '_icon')) :
					$title = get_field('trust_card_' . $i . '_title');
					if (!$title) { $i++; continue; }
				?>
					<div class="trust__card">
						<img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($title); ?>">
						<h3 class="trust__card-title"><?php echo esc_html($title); ?></h3>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
		$problem_title       = get_field('problem_title');
		$problem_description = get_field('problem_description');

		$problem_bg_elements = [
			['class' => 'grey',          'src' => get_template_directory_uri() . '/assets/images/elements/semi-circles.png'],
			['class' => 'grey rotate-45','src' => get_template_directory_uri() . '/assets/images/elements/oval.png'],
			['class' => 'green-opacity', 'src' => get_template_directory_uri() . '/assets/images/elements/cube-in-cube.png'],
			['class' => 'grey',          'src' => get_template_directory_uri() . '/assets/images/elements/semi-circles.png'],
			['class' => 'grey rotate-45','src' => get_template_directory_uri() . '/assets/images/elements/oval.png'],
			['class' => 'green-opacity', 'src' => get_template_directory_uri() . '/assets/images/elements/cube-in-cube.png'],
		];

		$problem_cards = [];
		$i = 1;
		while ($icon = get_field('problem_card_' . $i . '_icon')) :
			$title = get_field('problem_card_' . $i . '_title');
			$desc  = get_field('problem_card_' . $i . '_description');
			if ($title && $desc) {
				$problem_cards[] = ['icon' => $icon, 'title' => $title, 'desc' => $desc];
			}
			$i++;
		endwhile;
		$problem_last = count($problem_cards) - 1;
	?>
	<div class="problem">
		<div class="problem__container">
			<div class="problem__image-wrapper"></div>
			<?php if ($problem_title || $problem_description) : ?>
				<div class="problem__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($problem_title) : ?>
						<h2 class="problem__title section-title"><?php echo esc_html($problem_title); ?></h2>
					<?php endif; ?>
					<?php if ($problem_description) : ?>
						<p class="problem__description"><?php echo nl2br(esc_html($problem_description)); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="problem__grid">
				<?php foreach ($problem_cards as $idx => $card) :
					$is_last   = ($idx === $problem_last);
					$card_class = $is_last ? ' dark-green' : '';
					$bg = $problem_bg_elements[$idx % count($problem_bg_elements)];
				?>
					<div class="problem__card section-visual-card<?php echo $card_class; ?>">
						<?php if ($is_last) : ?>
							<img class="section-visual-card__bg-element <?php echo esc_attr($bg['class']); ?>" src="<?php echo esc_url($bg['src']); ?>" alt="Полукруги">
							<div class="section-visual-card__icon-wrapper white">
								<img class="section-visual-card__icon white" src="<?php echo esc_url($card['icon']); ?>" alt="<?php echo esc_attr($card['title']); ?>">
							</div>
							<h3 class="section-visual-card__title white"><?php echo esc_html($card['title']); ?></h3>
							<p class="section-visual-card__description white"><?php echo esc_html($card['desc']); ?></p>
						<?php else : ?>
							<img class="section-visual-card__bg-element <?php echo esc_attr($bg['class']); ?>" src="<?php echo esc_url($bg['src']); ?>" alt="Полукруги">
							<div class="section-visual-card__icon-wrapper">
								<img class="section-visual-card__icon" src="<?php echo esc_url($card['icon']); ?>" alt="<?php echo esc_attr($card['title']); ?>">
							</div>
							<h3 class="section-visual-card__title"><?php echo esc_html($card['title']); ?></h3>
							<p class="section-visual-card__description"><?php echo esc_html($card['desc']); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php
		$decision_title       = get_field('decision_title');
		$decision_description = get_field('decision_description');

		$decision_cards = [];
		$i = 1;
		while ($card_title = get_field('decision_card_' . $i . '_title')) :
			$decision_cards[] = $card_title;
			$i++;
		endwhile;
		$decision_last = count($decision_cards) - 1;
	?>
	<div class="decision">
		<div class="decision__container">
			<?php if ($decision_title || $decision_description) : ?>
				<div class="decision__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($decision_title) : ?>
						<h2 class="decision__title section-title"><?php echo esc_html($decision_title); ?></h2>
					<?php endif; ?>
					<?php if ($decision_description) : ?>
						<p class="decision__description"><?php echo esc_html($decision_description); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="decision__grid">
				<?php foreach ($decision_cards as $idx => $card_title) :
					$is_first    = ($idx === 0);
					$is_last     = ($idx === $decision_last);
					$step_number = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
				?>
					<div class="decision__card">
						<div class="decision__step">
							<div class="decision__step-line<?php echo $is_first ? ' none' : ''; ?>"></div>
							<div class="decision__step-circle">
								<span class="decision__step-number"><?php echo $step_number; ?></span>
							</div>
							<div class="decision__step-line<?php echo $is_last ? ' none' : ''; ?>"></div>
						</div>
						<h3 class="decision__card-title"><?php echo esc_html($card_title); ?></h3>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php
		$team_title       = get_field('team_title');
		$team_description = get_field('team_description');
	?>
	<div class="team">
		<div class="team__container">
			<?php if ($team_title || $team_description) : ?>
				<div class="team__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($team_title) : ?>
						<h2 class="team__title section-title"><?php echo esc_html($team_title); ?></h2>
					<?php endif; ?>
					<?php if ($team_description) : ?>
						<p class="team__description"><?php echo esc_html($team_description); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="team__grid">
				<?php
				$i = 1;
				while ($photo = get_field('team_member_' . $i . '_photo')) :
					$name     = get_field('team_member_' . $i . '_name');
					$position = get_field('team_member_' . $i . '_position');
					if (!$name || !$position) { $i++; continue; }
					$fb_url = get_field('team_member_' . $i . '_facebook_url');
					$tw_url = get_field('team_member_' . $i . '_twitter_url');
					$li_url = get_field('team_member_' . $i . '_linkedin_url');
					$ig_url = get_field('team_member_' . $i . '_instagram_url');
				?>
					<div class="team-item">
						<div class="team-item__wrapper">
							<div class="team-img">
								<img src="<?php echo esc_url($photo); ?>" class="img-fluid" alt="<?php echo esc_attr($name); ?>">
							</div>
							<div class="team-title">
								<h4 class="mb-0"><?php echo esc_html($name); ?></h4>
								<p class="mb-0"><?php echo esc_html($position); ?></p>
							</div>
							<div class="team-icon">
								<?php if ($fb_url) : ?>
									<a class="btn btn-primary btn-sm-square rounded-circle me-3" href="<?php echo esc_url($fb_url); ?>"><i class="fab fa-facebook-f"></i></a>
								<?php endif; ?>
								<?php if ($tw_url) : ?>
									<a class="btn btn-primary btn-sm-square rounded-circle me-3" href="<?php echo esc_url($tw_url); ?>"><i class="fab fa-twitter"></i></a>
								<?php endif; ?>
								<?php if ($li_url) : ?>
									<a class="btn btn-primary btn-sm-square rounded-circle me-3" href="<?php echo esc_url($li_url); ?>"><i class="fab fa-linkedin-in"></i></a>
								<?php endif; ?>
								<?php if ($ig_url) : ?>
									<a class="btn btn-primary btn-sm-square rounded-circle me-0" href="<?php echo esc_url($ig_url); ?>"><i class="fab fa-instagram"></i></a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
		$hiw_title       = get_field('how_it_works_title');
		$hiw_description = get_field('how_it_works_description');
		$hiw_btn_text    = get_field('how_it_works_button_text');
		$hiw_btn_url     = get_field('how_it_works_button_url');
	?>
	<div class="how-it-works">
		<div class="how-it-works__container">
			<div class="how-it-works__header">
				<div class="how-it-works__header-content">
					<div class="title-decoration how-it-works__decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($hiw_title) : ?>
						<h2 class="how-it-works__title section-title"><?php echo esc_html($hiw_title); ?></h2>
					<?php endif; ?>
					<?php if ($hiw_description) : ?>
						<p class="how-it-works__description"><?php echo esc_html($hiw_description); ?></p>
					<?php endif; ?>
				</div>
				<?php if ($hiw_btn_text && $hiw_btn_url) : ?>
					<a href="<?php echo esc_url($hiw_btn_url); ?>" class="primary-button how-it-works__button"><?php echo esc_html($hiw_btn_text); ?></a>
				<?php endif; ?>
			</div>
			<div class="how-it-works__grid">
				<?php
				$i = 1;
				while ($card_image = get_field('how_it_works_card_' . $i . '_image')) :
					$card_step  = get_field('how_it_works_card_' . $i . '_step');
					$card_title = get_field('how_it_works_card_' . $i . '_title');
					if (!$card_step || !$card_title) { $i++; continue; }
				?>
					<div class="how-it-works__card">
						<div class="how-it-works__card-image-wrapper">
							<div class="how-it-works__card-image-overlay"></div>
							<img class="how-it-works__card-image" src="<?php echo esc_url($card_image); ?>" alt="<?php echo esc_attr($card_step); ?>">
						</div>
						<div class="how-it-works__card-content">
							<div class="how-it-works__card-step"><?php echo esc_html($card_step); ?></div>
							<h3 class="how-it-works__card-title"><?php echo esc_html($card_title); ?></h3>
						</div>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
		$reviews_title       = get_field('reviews_title');
		$reviews_description = get_field('reviews_description');
	?>
	<div class="reviews">
		<div class="reviews__container">
			<?php if ($reviews_title || $reviews_description) : ?>
				<div class="reviews__container-header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($reviews_title) : ?>
						<h2 class="section-title"><?php echo esc_html($reviews_title); ?></h2>
					<?php endif; ?>
					<?php if ($reviews_description) : ?>
						<p class="reviews__description"><?php echo esc_html($reviews_description); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="reviews__carousel owl-carousel testimonial-carousel">
				<?php
				$i = 1;
				while ($text = get_field('review_' . $i . '_text')) :
					$avatar      = get_field('review_' . $i . '_avatar');
					$author_name = get_field('review_' . $i . '_author_name');
					$author_role = get_field('review_' . $i . '_author_role');
					if (!$text) { $i++; continue; }
				?>
					<div class="reviews__item testimonial-item position-relative bg-white p-5">
						<div class="reviews__icon">
							<i class="fa fa-quote-left fa-3x text-primary"></i>
						</div>
						<p class="reviews__text mb-4"><?php echo nl2br(esc_html($text)); ?></p>
						<div class="reviews__author d-flex align-items-center">
							<?php if (!empty($avatar)) : ?>
								<img class="reviews__author-avatar flex-shrink-0 rounded-circle" src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author_name ?: ''); ?>">
							<?php endif; ?>
							<div class="reviews__author-info ms-3">
								<?php if ($author_name) : ?>
									<h5 class="reviews__author-name mb-1"><?php echo esc_html($author_name); ?></h5>
								<?php endif; ?>
								<?php if ($author_role) : ?>
									<span class="reviews__author-role"><?php echo esc_html($author_role); ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
		$program_title    = get_field('program_title');
		$program_desc     = get_field('program_description');
		$program_btn_text = get_field('program_button_text');
		$program_btn_url  = get_field('program_button_url');

		$program_bg_elements = [
			['class' => 'grey',           'src' => get_template_directory_uri() . '/assets/images/elements/semi-circles.png'],
			['class' => 'grey rotate-90', 'src' => get_template_directory_uri() . '/assets/images/elements/geometry1.png'],
			['class' => 'grey',           'src' => get_template_directory_uri() . '/assets/images/elements/geometry2.png'],
			['class' => 'grey rotate-45', 'src' => get_template_directory_uri() . '/assets/images/elements/geometry3.png'],
			['class' => 'grey rotate-270','src' => get_template_directory_uri() . '/assets/images/elements/oval.png'],
			['class' => 'green-opacity',  'src' => get_template_directory_uri() . '/assets/images/elements/star.png'],
		];

		$program_cards = [];
		$i = 1;
		while ($icon = get_field('program_card_' . $i . '_icon')) :
			$title = get_field('program_card_' . $i . '_title');
			$desc  = get_field('program_card_' . $i . '_description');
			if ($title && $desc) {
				$program_cards[] = ['icon' => $icon, 'title' => $title, 'desc' => $desc];
			}
			$i++;
		endwhile;
		$program_last = count($program_cards) - 1;
	?>
	<div class="program">
		<div class="program__container ">
			<?php if ($program_title || $program_desc) : ?>
				<div class="program__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($program_title) : ?>
						<h2 class="program__title section-title"><?php echo esc_html($program_title); ?></h2>
					<?php endif; ?>
					<?php if ($program_desc) : ?>
						<p class="program__description"><?php echo nl2br(esc_html($program_desc)); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="program__grid">
				<?php foreach ($program_cards as $idx => $card) :
					$is_last    = ($idx === $program_last);
					$card_class = $is_last ? ' dark-green' : '';
					$bg         = $program_bg_elements[$idx % count($program_bg_elements)];
				?>
					<div class="program__card section-visual-card<?php echo $card_class; ?>">
						<img class="section-visual-card__bg-element <?php echo esc_attr($bg['class']); ?>" src="<?php echo esc_url($bg['src']); ?>" alt="Полукруги">
						<?php if ($is_last) : ?>
							<div class="section-visual-card__icon-wrapper white">
								<img class="section-visual-card__icon white" src="<?php echo esc_url($card['icon']); ?>" alt="<?php echo esc_attr($card['title']); ?>">
							</div>
							<h3 class="section-visual-card__title white"><?php echo esc_html($card['title']); ?></h3>
							<p class="section-visual-card__description white"><?php echo esc_html($card['desc']); ?></p>
						<?php else : ?>
							<div class="section-visual-card__icon-wrapper">
								<img class="section-visual-card__icon" src="<?php echo esc_url($card['icon']); ?>" alt="<?php echo esc_attr($card['title']); ?>">
							</div>
							<h3 class="section-visual-card__title"><?php echo esc_html($card['title']); ?></h3>
							<p class="section-visual-card__description"><?php echo esc_html($card['desc']); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ($program_btn_text && $program_btn_url) : ?>
				<div class="program__button-wrapper">
					<a href="<?php echo esc_url($program_btn_url); ?>" class="primary-button program__button"><?php echo esc_html($program_btn_text); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
	$ov_title = get_field('other_variants_title');
	$ov_desc  = get_field('other_variants_description');
	?>
	<div class="other-variants">
		<div class="other-variants__bg" aria-hidden="true">
			<img src="/assets/hero3-pjMiwMLC.jpg" alt="">
		</div>
		<div class="other-variants__container">
			<?php if ($ov_title || $ov_desc) : ?>
				<div class="other-variants__header title-subtitle-header">
					<?php if ($ov_title) : ?>
						<h2 class="other-variants__title section-title"><?php echo esc_html($ov_title); ?></h2>
					<?php endif; ?>
					<?php if ($ov_desc) : ?>
						<p class="other-variants__description"><?php echo esc_html($ov_desc); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="other-variants__grid">
				<?php
				$i = 1;
				while ($icon = get_field('other_variant_' . $i . '_icon')) :
					$title = get_field('other_variant_' . $i . '_title');
					$text  = get_field('other_variant_' . $i . '_text');
					$link  = get_field('other_variant_' . $i . '_link');
					if (!$title || !$text) { $i++; continue; }
				?>
						<a href="<?php echo esc_url( $link ); ?>" class="other-variants__card">
						<div class="other-variants__icon-wrapper">
							<img class="other-variants__icon" src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($title); ?>">
						</div>
						<h3 class="other-variants__card-title"><?php echo esc_html($title); ?></h3>
						<p class="other-variants__card-text"><?php echo esc_html($text); ?></p>
				</a>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
	$science_title = get_field('science_title');
	$science_desc  = get_field('science_description');
	?>
	<div class="science">
		<div class="science__container">
			<?php if ($science_title || $science_desc) : ?>
				<div class="science__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($science_title) : ?>
						<h2 class="science__title section-title"><?php echo esc_html($science_title); ?></h2>
					<?php endif; ?>
					<?php if ($science_desc) : ?>
						<p class="science__description"><?php echo esc_html($science_desc); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="science__grid">
				<?php
				$i = 1;
				while ($image = get_field('science_card_' . $i . '_image')) :
					$title = get_field('science_card_' . $i . '_title');
					$url   = get_field('science_card_' . $i . '_url');
					if (!$title || !$url) { $i++; continue; }
				?>
					<div class="science__card">
						<div class="science__bg-filter"></div>
						<img class="science__card-image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
						<div class="science__card-content">
							<h3 class="science__card-title">
								<a class="science__card-title" href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
							</h3>
						</div>
					</div>
				<?php $i++; endwhile; ?>
			</div>
		</div>
	</div>

	<?php
	$faq_title    = get_field('faq_title');
	$faq_subtitle = get_field('faq_subtitle');
	$faq_image    = get_field('faq_image');
	?>
	<section class="faq" data-faq>
		<div class="faq__container">
			<?php if ($faq_title || $faq_subtitle) : ?>
				<div class="faq__header title-subtitle-header">
					<div class="title-decoration">
						<div class="title-decoration_top"></div>
						<div class="title-decoration_bottom"></div>
					</div>
					<?php if ($faq_title) : ?>
						<h2 class="faq__title section-title"><?php echo esc_html($faq_title); ?></h2>
					<?php endif; ?>
					<?php if ($faq_subtitle) : ?>
						<p class="faq__subtitle"><?php echo esc_html($faq_subtitle); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="faq__content">
				<div class="faq__left">
					<div class="faq__accordion">
						<?php
						$i = 1;
						while ($question = get_field('faq_' . $i . '_question')) :
							$answer = get_field('faq_' . $i . '_answer');
							if (!$answer) { $i++; continue; }
						?>
							<div class="faq__item" data-faq-item>
								<button class="faq__question" type="button" data-faq-trigger aria-expanded="false" aria-controls="faq-panel-<?php echo $i; ?>">
									<h4 class="faq__question-title"><?php echo esc_html($question); ?></h4>
									<img class="faq__icon" aria-hidden="true" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAA+0lEQVR4nO2YwQ6CMBBE53/0c7rhoP/BUY/+syYQY70Q0AI72y3ZSThw6cyj0J0AhEKhUChUrhMa9rgDeAG4sgwACIAngAcj/JAvFoTk8F8fNYjzZOEh33daBvisNefx9lZRmjHQ2glZWPsCZTEgxCo8A8I8vCZEtfAaENXD74FwE34LhLvwayDchi+BcB/+3zRlT3H6Trh/8qUQTYTHwjvPbLEm4ZuA6Fr+iOXHUcms4ioqOefdQsiKIeUOQjZMWDcQsqMeVIcQhW5TDUIUi5k5BKNVJisIZiVObIhD/NjqDVplmkDctA16g0qcMoR6+EP8Xg+FQqHQ8TQCNxwr5J48PtQAAAAASUVORK5CYII=" alt="cross icon">
								</button>
								<div class="faq__panel" id="faq-panel-<?php echo $i; ?>" data-faq-panel aria-hidden="true">
									<div class="faq__answer">
										<p><?php echo nl2br(esc_html($answer)); ?></p>
									</div>
								</div>
							</div>
						<?php $i++; endwhile; ?>
					</div>
				</div>

				<?php if ($faq_image) : ?>
					<div class="faq__right">
						<img class="faq__image" src="<?php echo esc_url($faq_image); ?>" alt="FAQ">
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	$cta_title    = get_field('cta_title');
	$cta_text     = get_field('cta_text');
	$cta_btn_text = get_field('cta_button_text');
	$cta_btn_url  = get_field('cta_button_url');
	?>
	<?php if ($cta_title || $cta_text) : ?>
	<section class="cta">
		<div class="cta__container">
			<div class="cta__content">
				<?php if ($cta_title) : ?>
					<h2 class="cta__title section-title"><?php echo esc_html($cta_title); ?></h2>
				<?php endif; ?>
				<?php if ($cta_text) : ?>
					<p class="cta__text"><?php echo nl2br(esc_html($cta_text)); ?></p>
				<?php endif; ?>
			</div>
			<?php if ($cta_btn_text && $cta_btn_url) : ?>
				<div class="cta__actions">
					<a class="primary-button cta__button" href="<?php echo esc_url($cta_btn_url); ?>"><?php echo esc_html($cta_btn_text); ?></a>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

<?php get_footer(); ?>
