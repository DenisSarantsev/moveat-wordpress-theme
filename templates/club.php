<?php
/**
 * Template Name: Club
 * Description: Страница «Клуб» — поля из assets/acf-fields/club.json (команда до 10 человек, FAQ до 12 вопросов).
 */

defined( 'ABSPATH' ) || exit;

/*
 Иконка раскрытия FAQ совпадает с главной страницей (data URI)
*/
$faq_cross_icon_src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAA+0lEQVR4nO2YwQ6CMBBE53/0c7rhoP/BUY/+syYQY70Q0AI72y3ZSThw6cyh0J0AhEKhUChUrhMa9rgDeAG4sgwACIAngAcj/JAvFoTk8F8fNYjzZOEh33daBvisNefx9lZRmjHQ2glZWPsCZTEgxCo8A8I8vCZEtfAaENXD74FwE34LhLvwayDchi+BcB/+3zRlT3H6Trh/8rUQTYTHwjvPbLEm4ZuA6Fr+iOXHUcms4ioqOefdQsiKIeUOQjZMWDcQsqMeVIcQhW5TDUIUi5k5BKNVJisIZiVObIhD/NjqDVplmkDctA16g0qcMoR6+EP8Xg+FQqHQ8TQCNxwr5J48PtQAAAAASUVORK5CYII=';

$club_hero_title          = trim( (string) get_field( 'club_hero_title' ) ) ?: 'Клуб Макса Погорелого';
$club_breadcrumb_current  = trim( (string) get_field( 'club_breadcrumb_current' ) ) ?: 'Клуб';
$club_intro_image         = get_field( 'club_intro_image' );
$club_intro_alt           = trim( (string) get_field( 'club_intro_alt' ) ) ?: $club_hero_title;
$club_intro_lead          = trim( (string) get_field( 'club_intro_lead' ) );
$club_intro_text          = trim( (string) get_field( 'club_intro_text' ) );
$club_intro_cta_text      = trim( (string) get_field( 'club_intro_cta_text' ) ) ?: 'Присоединиться к клубу';

$club_material_items = [];
for ( $m = 1; $m <= 7; $m++ ) {
	$item = trim( (string) get_field( 'club_material_item_' . $m ) );
	if ( $item !== '' ) {
		$club_material_items[] = $item;
	}
}

$club_team_heading = trim( (string) get_field( 'club_team_heading' ) ) ?: 'Наша команда';

$club_intro_aria_attrs = '';
if ( $club_intro_lead !== '' || $club_intro_text !== '' ) {
	$club_intro_aria_attrs = ' aria-labelledby="club-intro-heading"';
} elseif ( ! empty( $club_intro_image ) ) {
	$club_intro_aria_attrs = ' aria-label="' . esc_attr__( 'Информация о клубе', 'moveat' ) . '"';
} else {
	$club_intro_aria_attrs = ' aria-label="' . esc_attr__( 'Присоединение к клубу', 'moveat' ) . '"';
}

get_header();
?>

<div class="hero-block">
	<div class="hero-block__bg-wrapper">
		<img
			class="hero-block__bg-image"
			src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/illustrations/vegetables.jpg' ); ?>"
			alt="<?php echo esc_attr__( 'vegetables', 'moveat' ); ?>" />
	</div>
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php echo esc_html( $club_hero_title ); ?></h1>
		<nav aria-label="хлебные крошки">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<a class="text-body" href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
				</li>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<span class="text-body"><?php echo esc_html( $club_breadcrumb_current ); ?></span>
				</li>
			</ol>
		</nav>
	</div>
</div>

<main class="club-page">
	<div class="club-page__container">
		<section class="club-page__intro"<?php echo $club_intro_aria_attrs; ?>>
			<?php if ( ! empty( $club_intro_image ) ) : ?>
				<div class="club-page__intro-media">
					<img
						class="club-page__intro-image"
						src="<?php echo esc_url( $club_intro_image ); ?>"
						alt="<?php echo esc_attr( $club_intro_alt ); ?>"
						loading="eager"
						decoding="async" />
				</div>
			<?php endif; ?>
			<div class="club-page__intro-copy">
				<?php if ( $club_intro_lead !== '' ) : ?>
					<p class="club-page__intro-lead" id="club-intro-heading"><?php echo esc_html( $club_intro_lead ); ?></p>
				<?php endif; ?>
				<?php if ( $club_intro_text !== '' ) : ?>
					<p class="club-page__intro-text"<?php echo ( $club_intro_lead === '' ) ? ' id="club-intro-heading"' : ''; ?>><?php echo esc_html( $club_intro_text ); ?></p>
				<?php endif; ?>
				<button
					type="button"
					class="primary-button club-page__intro-cta"
					data-club-scroll-to-pricing
					aria-label="<?php echo esc_attr__( 'Перейти к блоку оплаты и оформить участие', 'moveat' ); ?>">
					<?php echo esc_html( $club_intro_cta_text ); ?>
				</button>
			</div>
		</section>

		<section
			class="club-page__band"
			aria-labelledby="club-materials-heading">
			<h2 class="club-page__section-title" id="club-materials-heading">
				<?php echo esc_html( get_field( 'club_materials_title' ) ?: 'Эксклюзивные материалы только для членов клуба' ); ?>
			</h2>
			<div class="club-page__materials">
				<div class="club-page__materials-visual">
					<?php
					$mat_fig1 = get_field( 'club_materials_figure_1_image' );
					$mat_fig2 = get_field( 'club_materials_figure_2_image' );
					$mat_alt1 = trim( (string) get_field( 'club_materials_figure_1_alt' ) ) ?: 'Витамины и нутриенты';
					$mat_alt2 = trim( (string) get_field( 'club_materials_figure_2_alt' ) ) ?: 'Продукты и питание';
					?>
					<?php if ( ! empty( $mat_fig1 ) ) : ?>
						<figure class="club-page__figure">
							<img src="<?php echo esc_url( $mat_fig1 ); ?>" alt="<?php echo esc_attr( $mat_alt1 ); ?>" loading="lazy" decoding="async" />
						</figure>
					<?php endif; ?>
					<?php if ( ! empty( $mat_fig2 ) ) : ?>
						<figure class="club-page__figure">
							<img src="<?php echo esc_url( $mat_fig2 ); ?>" alt="<?php echo esc_attr( $mat_alt2 ); ?>" loading="lazy" decoding="async" />
						</figure>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $club_material_items ) ) : ?>
					<ul class="club-page__materials-list">
						<?php foreach ( $club_material_items as $mat_li ) : ?>
							<li><?php echo esc_html( $mat_li ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>

		<section
			class="club-page__spotlight"
			aria-labelledby="club-online-heading">
			<h2 class="club-page__section-title" id="club-online-heading">
				<?php echo esc_html( get_field( 'club_online_title' ) ?: 'Онлайн-общение с Максом' ); ?>
			</h2>
			<?php
			$online_text = trim( (string) get_field( 'club_online_text' ) );
			if ( $online_text !== '' ) :
				?>
				<p class="club-page__spotlight-text"><?php echo esc_html( $online_text ); ?></p>
			<?php endif; ?>
			<?php
			$online_img = get_field( 'club_online_image' );
			$online_alt = trim( (string) get_field( 'club_online_image_alt' ) ) ?: 'Макс на встрече с участниками клуба';
			if ( ! empty( $online_img ) ) :
				?>
				<figure class="club-page__spotlight-figure">
					<img
						src="<?php echo esc_url( $online_img ); ?>"
						alt="<?php echo esc_attr( $online_alt ); ?>"
						loading="lazy"
						decoding="async" />
				</figure>
			<?php endif; ?>
		</section>

		<section
			class="club-page__band club-page__band--tight"
			aria-labelledby="club-community-heading">
			<h2 class="club-page__section-title" id="club-community-heading">
				<?php echo esc_html( get_field( 'club_community_title' ) ?: 'Общение и поддержка единомышленников' ); ?>
			</h2>
			<?php
			$comm_text_raw = trim( (string) get_field( 'club_community_text' ) );
			if ( $comm_text_raw !== '' ) :
				?>
				<p class="club-page__narrow-text">
					<?php echo wp_kses_post( nl2br( esc_html( $comm_text_raw ), false ) ); ?>
				</p>
			<?php endif; ?>
			<?php
			$map_img = get_field( 'club_community_map_image' );
			$map_alt = trim( (string) get_field( 'club_community_map_alt' ) ) ?: 'Участники клуба по всему миру';
			if ( ! empty( $map_img ) ) :
				?>
				<figure class="club-page__worldmap">
					<img
						src="<?php echo esc_url( $map_img ); ?>"
						alt="<?php echo esc_attr( $map_alt ); ?>"
						loading="lazy"
						decoding="async" />
				</figure>
			<?php endif; ?>
		</section>

		<section class="club-page__team" aria-labelledby="club-team-heading">
			<h2 class="club-page__section-title" id="club-team-heading">
				<?php echo esc_html( $club_team_heading ); ?>
			</h2>
			<div class="club-page__team-grid">
				<?php
				for ( $t = 1; $t <= 10; $t++ ) {
					$tm_name = trim( (string) get_field( 'club_team_member_' . $t . '_name' ) );
					$tm_role = trim( (string) get_field( 'club_team_member_' . $t . '_role' ) );
					$tm_bio  = get_field( 'club_team_member_' . $t . '_bio' );
					$tm_ph   = get_field( 'club_team_member_' . $t . '_photo' );

					if ( empty( $tm_name ) && empty( $tm_role ) && empty( $tm_bio ) && empty( $tm_ph ) ) {
						continue;
					}

					?>
					<article class="club-page__team-card">
						<?php if ( ! empty( $tm_ph ) ) : ?>
							<img class="club-page__team-photo" src="<?php echo esc_url( $tm_ph ); ?>" alt="" loading="lazy" decoding="async" />
						<?php endif; ?>
						<?php if ( $tm_name !== '' ) : ?>
							<h3 class="club-page__team-name"><?php echo esc_html( $tm_name ); ?></h3>
						<?php endif; ?>
						<?php if ( $tm_role !== '' ) : ?>
							<p class="club-page__team-role"><?php echo esc_html( $tm_role ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $tm_bio ) ) : ?>
							<div class="club-page__team-bio"><?php echo wp_kses_post( $tm_bio ); ?></div>
						<?php endif; ?>
					</article>
					<?php
				}
				?>
			</div>
		</section>

		<section
			class="club-page__pricing"
			id="club-pricing"
			aria-labelledby="club-pricing-heading">
			<h2 class="club-page__section-title" id="club-pricing-heading">
				<?php echo esc_html( get_field( 'club_pricing_title' ) ?: 'Стать участником клуба' ); ?>
			</h2>
			<div class="club-page__pricing-grid">
				<div class="club-page__pricing-card">
					<?php
					$b1 = get_field( 'club_pricing_card_1_body' );
					if ( ! empty( $b1 ) ) :
						?>
						<div class="club-page__pricing-text"><?php echo wp_kses_post( $b1 ); ?></div>
					<?php endif; ?>
					<?php
					$u1   = trim( (string) get_field( 'club_pricing_card_1_url' ) );
					$btn1 = trim( (string) get_field( 'club_pricing_card_1_btn' ) ) ?: 'Оплатить';
					if ( $u1 !== '' ) :
						?>
						<a class="primary-button club-page__pricing-btn" href="<?php echo esc_url( $u1 ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( $btn1 ); ?></a>
					<?php endif; ?>
				</div>
				<div class="club-page__pricing-divider" aria-hidden="true"></div>
				<div class="club-page__pricing-card">
					<?php
					$b2 = get_field( 'club_pricing_card_2_body' );
					if ( ! empty( $b2 ) ) :
						?>
						<div class="club-page__pricing-text"><?php echo wp_kses_post( $b2 ); ?></div>
					<?php endif; ?>
					<?php
					$u2   = trim( (string) get_field( 'club_pricing_card_2_url' ) );
					$btn2 = trim( (string) get_field( 'club_pricing_card_2_btn' ) ) ?: 'Оплатить';
					if ( $u2 !== '' ) :
						?>
						<a class="primary-button club-page__pricing-btn" href="<?php echo esc_url( $u2 ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( $btn2 ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section
			class="club-page__outreach"
			aria-labelledby="club-rules-heading">
			<h2 class="club-page__outreach-title" id="club-rules-heading">
				<?php
				$r_text = trim( (string) get_field( 'club_rules_link_text' ) ) ?: 'Правила клуба';
				$r_url  = trim( (string) get_field( 'club_rules_link_url' ) );
				if ( $r_url !== '' ) :
					?>
					<a
						class="club-page__rules-link"
						href="<?php echo esc_url( $r_url ); ?>"
						target="_blank"
						rel="noreferrer noopener"
						><?php echo esc_html( $r_text ); ?></a>
				<?php else : ?>
					<span class="club-page__rules-link"><?php echo esc_html( $r_text ); ?></span>
				<?php endif; ?>
			</h2>
			<?php
			$reach = trim( (string) get_field( 'club_outreach_text' ) );
			if ( $reach !== '' ) :
				?>
				<p class="club-page__outreach-text">
					<?php echo wp_kses_post( nl2br( esc_html( $reach ), false ) ); ?>
				</p>
			<?php endif; ?>
			<?php
			$mess_panel_title = trim( (string) get_field( 'club_messengers_panel_title' ) );
			$tg_u             = trim( (string) get_field( 'club_messenger_telegram_url' ) );
			$tg_icon          = get_field( 'club_messenger_telegram_icon' );
			$tg_l             = trim( (string) get_field( 'club_messenger_telegram_label' ) ) ?: 'Telegram';
			$wa_u             = trim( (string) get_field( 'club_messenger_whatsapp_url' ) );
			$wa_icon          = get_field( 'club_messenger_whatsapp_icon' );
			$wa_l             = trim( (string) get_field( 'club_messenger_whatsapp_label' ) ) ?: 'WhatsApp';
			$vb_u             = trim( (string) get_field( 'club_messenger_viber_url' ) );
			$vb_icon          = get_field( 'club_messenger_viber_icon' );
			$vb_l             = trim( (string) get_field( 'club_messenger_viber_label' ) ) ?: 'Viber';

			if ( $mess_panel_title !== '' || $tg_u !== '' || $wa_u !== '' || $vb_u !== '' ) :
				?>
			<div class="club-page__messengers">
				<div class="club-page__messengers-panel">
					<?php if ( $mess_panel_title !== '' ) : ?>
						<div class="club-page__messengers-title"><?php echo wp_kses_post( nl2br( esc_html( $mess_panel_title ), false ) ); ?></div>
					<?php endif; ?>
					<div class="club-page__messengers-row">
						<?php if ( $tg_u !== '' ) : ?>
							<a
								class="club-page__messengers-link club-page__messengers-link--telegram"
								href="<?php echo esc_url( $tg_u ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								aria-label="<?php echo esc_attr( sprintf( __( 'Открыть %s', 'moveat' ), $tg_l ) ); ?>">
								<?php if ( ! empty( $tg_icon ) ) : ?>
									<img src="<?php echo esc_url( $tg_icon ); ?>" alt="<?php echo esc_attr( $tg_l ); ?>" loading="lazy" decoding="async" />
								<?php else : ?>
									<span class="club-page__messengers-label-fallback"><?php echo esc_html( $tg_l ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( $wa_u !== '' ) : ?>
							<a
								class="club-page__messengers-link club-page__messengers-link--whatsapp"
								href="<?php echo esc_url( $wa_u ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								aria-label="<?php echo esc_attr( sprintf( __( 'Открыть %s', 'moveat' ), $wa_l ) ); ?>">
								<?php if ( ! empty( $wa_icon ) ) : ?>
									<img src="<?php echo esc_url( $wa_icon ); ?>" alt="<?php echo esc_attr( $wa_l ); ?>" loading="lazy" decoding="async" />
								<?php else : ?>
									<span class="club-page__messengers-label-fallback"><?php echo esc_html( $wa_l ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ( $vb_u !== '' ) : ?>
							<a
								class="club-page__messengers-link club-page__messengers-link--viber"
								href="<?php echo esc_url( $vb_u, array( 'http', 'https', 'viber' ) ); ?>"
								rel="noopener noreferrer"
								aria-label="<?php echo esc_attr( sprintf( __( 'Открыть %s', 'moveat' ), $vb_l ) ); ?>">
								<?php if ( ! empty( $vb_icon ) ) : ?>
									<img src="<?php echo esc_url( $vb_icon ); ?>" alt="<?php echo esc_attr( $vb_l ); ?>" loading="lazy" decoding="async" />
								<?php else : ?>
									<span class="club-page__messengers-label-fallback"><?php echo esc_html( $vb_l ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
				<?php
			endif;
			?>
		</section>
	</div>

	<?php
	$faq_title    = trim( (string) get_field( 'club_faq_title' ) );
	$faq_subtitle = trim( (string) get_field( 'club_faq_subtitle' ) );
	$faq_items    = [];
	for ( $f = 1; $f <= 12; $f++ ) {
		$fq = trim( (string) get_field( 'club_faq_' . $f . '_question' ) );
		$fa = get_field( 'club_faq_' . $f . '_answer' );
		if ( $fq === '' && empty( trim( wp_strip_all_tags( (string) $fa ) ) ) ) {
			continue;
		}
		$faq_items[] = [ 'question' => $fq, 'answer' => $fa ];
	}

	if ( ! empty( $faq_items ) || $faq_title !== '' || $faq_subtitle !== '' ) :
		?>
	<section class="faq club-page__faq" data-faq>
		<div class="faq__container">
			<div class="faq__header title-subtitle-header">
				<?php if ( $faq_title !== '' ) : ?>
					<h2 class="faq__title section-title"><?php echo esc_html( $faq_title ); ?></h2>
				<?php endif; ?>
				<?php if ( $faq_subtitle !== '' ) : ?>
					<p class="faq__subtitle"><?php echo esc_html( $faq_subtitle ); ?></p>
				<?php endif; ?>
			</div>

			<div class="faq__content">
				<div class="faq__left">
					<div class="faq__accordion">
						<?php
						foreach ( $faq_items as $fi => $faq_row ) {
							$f_index = $fi + 1;
							$panel_id = 'club-faq-panel-' . $f_index;
							?>
							<div class="faq__item" data-faq-item>
								<button
									class="faq__question"
									type="button"
									data-faq-trigger
									aria-expanded="false"
									aria-controls="<?php echo esc_attr( $panel_id ); ?>">
									<h4 class="faq__question-title"><?php echo esc_html( $faq_row['question'] ); ?></h4>
									<img class="faq__icon" aria-hidden="true" src="<?php echo esc_attr( $faq_cross_icon_src ); ?>" alt="" loading="lazy" decoding="async" />
								</button>
								<div class="faq__panel" id="<?php echo esc_attr( $panel_id ); ?>" data-faq-panel aria-hidden="true">
									<div class="faq__answer">
										<?php echo wp_kses_post( $faq_row['answer'] ); ?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>
</main>

<?php
get_footer();
