<?php
/*
	Template Name: Расчет рациона питания
	Template Post Type: page
*/

get_header();

$page_id           = get_queried_object_id();
$icon_arrow_src    = esc_url( get_template_directory_uri() . '/assets/images/icons/arrow.png' );
$problem_group_max = 6;
$problem_items_max = 6;
$cases_total       = 8;
$faq_fp_slots      = 8;
$reviews_total     = 11;
$team_total        = 9;
$price_cards       = 5;
$faq_qa_slots      = 26;

$trim_scalar = static function ( $value ): string {
	if ( null === $value || false === $value ) {
		return '';
	}
	if ( ! is_scalar( $value ) ) {
		return '';
	}
	return trim( (string) $value );
};

$f_txt = static function ( string $key ) use ( $page_id, $trim_scalar ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	return $trim_scalar( get_field( $key, $page_id ) );
};

$f_editor = static function ( string $key ) use ( $page_id ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$v = get_field( $key, $page_id );
	if ( null === $v || false === $v ) {
		return '';
	}
	return trim( is_string( $v ) ? $v : '' );
};

$f_tf = static function ( string $key ) use ( $page_id ): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}
	$v = get_field( $key, $page_id );
	return (bool) $v;
};

$f_nonempty_editor = static function ( string $html ): bool {
	return '' !== trim( wp_strip_all_tags( $html, true ) );
};

$problem_blocks = [];
for ( $g = 1; $g <= $problem_group_max; $g++ ) {
	$t = $f_txt( 'cd_problem_cat_' . $g . '_title' );
	if ( '' === $t ) {
		continue;
	}


	$list = [];
	for ( $item = 1; $item <= $problem_items_max; $item++ ) {
		$line = $f_txt( 'cd_problem_cat_' . $g . '_item_' . $item );
		if ( '' !== $line ) {
			$list[] = $line;
		}
	}


	if ( ! $list ) {
		continue;
	}
	$problem_blocks[] = [
		'title' => $t,
		'items' => $list,
	];
}


$phys_photo_src = esc_url( $f_txt( 'cd_phys_photo' ) );
$phys_alt       = $f_txt( 'cd_phys_photo_alt' );
$phys_name      = $f_txt( 'cd_phys_name' );
$phys_role      = $f_txt( 'cd_phys_role' );
$phys_ready     = ( '' !== $phys_photo_src && '' !== $phys_name && '' !== $phys_role );
$ask_title  = $f_txt( 'cd_ask_banner_title' );
$ask_url    = esc_url( $f_txt( 'cd_ask_banner_url' ) );
$ask_anchor = $f_txt( 'cd_ask_banner_anchor_text' );


$case_slides = [];

for ( $idx = 1; $idx <= $cases_total; $idx++ ) {

	$content = $f_editor( 'cd_case_slide_' . $idx );


	if ( $f_nonempty_editor( $content ) ) {



		$case_slides[] = $content;

	}

}


$faq_fp_pairs = [];
for ( $fq = 1; $fq <= $faq_fp_slots; $fq++ ) {
	$tq = $f_txt( 'cd_fp_faq_q_' . $fq );
	$a = $f_editor( 'cd_fp_faq_a_' . $fq );
	if ( '' !== $tq && $f_nonempty_editor( $a ) ) {
		$faq_fp_pairs[] = [
			'q' => $tq,
			'a' => $a,
		];
	}
}

$price_cards_data = [];
for ( $pci = 1;
 $pci <= $price_cards;
 $pci++
 ) {
	$p_sub = $f_txt( 'cd_price_' . $pci . '_subtitle' );
	$p_cst = $f_txt( 'cd_price_' . $pci . '_cost' );
	$p_rel = $f_txt( 'cd_price_' . $pci . '_more_url' );
	$p_url = esc_url( $p_rel );
	if ( '' === $p_sub || '' === $p_cst || '' === $p_url ) {
		continue;
	}

	$price_cards_data[] = [
		'subtitle' => $p_sub,
		'cost'     => $p_cst,
		'link'     => $p_url,
	];
}

$faq_qa_pairs = [];
for ( $qi = 1; $qi <= $faq_qa_slots; $qi++ ) {
	$tq_qa = $f_txt( 'cd_qa_q_' . $qi );
	$ans_q = $f_editor( 'cd_qa_a_' . $qi );
	if ( '' !== $tq_qa && $f_nonempty_editor( $ans_q ) ) {
		$faq_qa_pairs[] = [
			'q' => $tq_qa,
			'a' => $ans_q,
		];
	}
}

$review_cards = [];
for ( $rj = 1; $rj <= $reviews_total; $rj++ ) {
	$r_img = esc_url( $f_txt( 'cd_review_' . $rj . '_photo' ) );
	$r_who = $f_txt( 'cd_review_' . $rj . '_author' );
	$r_txt = $f_editor( 'cd_review_' . $rj . '_text' );
	if ( '' === $r_img || '' === $r_who || ! $f_nonempty_editor( $r_txt ) ) {
		continue;
	}

	$pbtn = $f_tf( 'cd_review_' . $rj . '_primary_btn' );
	$review_cards[] = [
		'img'           => $r_img,
		'alt'           => $f_txt( 'cd_review_' . $rj . '_photo_alt' ) ?: $r_who,
		'name' => $r_who,
		'text' => $r_txt,
		'primary_btn' => $pbtn,
	];
}

$team_cards = [];
for ( $tj = 1; $tj <= $team_total; $tj++ ) {
	$tp       = esc_url( $f_txt( 'cd_team_' . $tj . '_photo' ) );
	$tname = $f_txt( 'cd_team_' . $tj . '_name' );
	$trole = $f_txt( 'cd_team_' . $tj . '_role' );
	if ( '' === $tp || '' === $tname || '' === $trole ) {
		continue;
	}
	$socials = [];
	foreach (
		[
			'fb' => 'fab fa-facebook-f',
			'tw' => 'fab fa-twitter',
			'li' => 'fab fa-linkedin-in',
			'ig' => 'fab fa-instagram',
		] as
		$key =>
		$ic
	) {
		$u = esc_url( $f_txt( 'cd_team_' . $tj . '_url_' . $key ) );
		if ( '' !== $u ) {
			$socials[] = [ 'href' => $u, 'icon_class' => $ic ];
		}
	}

	$team_cards[] = [
		'photo'       => $tp,
		'name'        => $tname,
		'role'        => $trole,
		'social_links' => $socials,
	];
}

$start_title     = $f_txt( 'cd_start_title' );
$start_subtitle  = $f_txt( 'cd_start_subtitle' );
$body_title      = $f_txt( 'cd_body_sys_title' );
$body_card_title = $f_txt( 'cd_body_card_title' );
$body_lead       = $f_txt( 'cd_body_card_lead' );
$body_bottom     = $f_txt( 'cd_body_card_bottom' );
$body_img_src    = esc_url( $f_txt( 'cd_body_sys_image' ) );
$body_img_alt    = $f_txt( 'cd_body_sys_image_alt' );
$fp_title        = $f_txt( 'cd_fp_title' );
$fp_subtitle     = $f_txt( 'cd_fp_subtitle' );
$fp_prompt       = $f_txt( 'cd_fp_prompt' );
$fp_dl_label     = $f_txt( 'cd_fp_download_anchor' );
$fp_dl_raw       = $f_txt( 'cd_fp_download_url' );
$fp_dl_url       = esc_url( $fp_dl_raw );
$rv_title        = $f_txt( 'cd_rev_title' );
$rv_more_label   = $f_txt( 'cd_rev_more_label' );
$rv_more_raw     = $f_txt( 'cd_rev_more_url' );
$rv_more_url     = esc_url( $rv_more_raw );
$vd_thumb_raw    = $f_txt( 'cd_video_thumb' );
$vd_thumb        = esc_url( $vd_thumb_raw );
$vd_thumb_alt    = $f_txt( 'cd_video_thumb_alt' );
$vd_watch_raw    = $f_txt( 'cd_video_watch_url' );
$vd_watch        = esc_url( $vd_watch_raw );
$vd_quote        = $f_txt( 'cd_video_quote' );
$vd_guest        = $f_txt( 'cd_video_guest' );
$vd_btn_lbl      = $f_txt( 'cd_video_btn_label' );
$vd_ex_raw       = $f_txt( 'cd_video_extra_url' );
$vd_ex_url       = esc_url( $vd_ex_raw );
$vd_ex_lbl       = $f_txt( 'cd_video_extra_label' );
$tm_title        = $f_txt( 'cd_team_title' );
$tm_intro        = $f_txt( 'cd_team_intro' );
$pr_title        = $f_txt( 'cd_price_title' );
$br_title        = $f_txt( 'cd_barrier_title' );
$br_top          = $f_editor( 'cd_barrier_top' );
$br_bot          = $f_editor( 'cd_barrier_bottom' );
$br_img_src      = esc_url( $f_txt( 'cd_barrier_image' ) );
$br_img_alt      = $f_txt( 'cd_barrier_img_alt' );
$qa_title_el     = $f_txt( 'cd_qa_title' );
$qa_sub_el       = $f_txt( 'cd_qa_subtitle' );
$qa_prompt_el    = $f_txt( 'cd_qa_prompt' );
$fin_photo_raw   = $f_txt( 'cd_final_photo' );
$fin_photo       = esc_url( $fin_photo_raw );
$fin_photo_alt   = $f_txt( 'cd_final_photo_alt' );
$fin_name        = $f_txt( 'cd_final_name' );
$fin_role        = $f_txt( 'cd_final_role' );
$fin_heading     = $f_txt( 'cd_final_heading' );
$fin_intro       = $f_editor( 'cd_final_intro' );
$fin_btn_raw     = $f_txt( 'cd_final_btn_url' );
$fin_btn_url     = esc_url( $fin_btn_raw );
$fin_btn_lbl     = $f_txt( 'cd_final_btn_label' );

$ask_ready       = ( '' !== $ask_title && '' !== $ask_url && '' !== $ask_anchor );
$start_lists_ok  = (bool) $problem_blocks;
$start_left_any  = ( $start_lists_ok || $ask_ready );
$show_start      =
	(
		'' !== $start_title
		|| '' !== $start_subtitle
		|| $start_left_any
		|| $phys_ready
	);
$show_body       =
	(
		'' !== $body_title
		|| '' !== $body_card_title
		|| '' !== $body_lead
		|| '' !== $body_bottom
		|| ( '' !== $body_img_src && '' !== $body_img_alt )
	);
$show_cases      = $case_slides;
$faq_fp_have     = (bool) $faq_fp_pairs;
$fp_have_dl_btn  = ( '' !== $fp_dl_label && '' !== $fp_dl_url );
$show_fp         =
	(
		'' !== $fp_title
		|| '' !== $fp_subtitle
		|| '' !== $fp_prompt
		|| $faq_fp_have
		|| $fp_have_dl_btn
	);
$rv_more_ok      = ( '' !== $rv_more_label && '' !== $rv_more_url );
$show_reviews    = ( '' !== $rv_title || $review_cards );
$show_vid        =
	(
		'' !== $vd_thumb
		|| '' !== trim( wp_strip_all_tags( $vd_quote, true ) )
		|| '' !== $vd_guest
		|| '' !== $vd_watch_raw
		|| ( '' !== $vd_btn_lbl && '' !== $vd_watch_raw )
		|| ( '' !== $vd_ex_lbl && '' !== $vd_ex_raw )
	);
$show_team       =
	(
		'' !== $tm_title
		|| '' !== trim( wp_strip_all_tags( $tm_intro, true ) )
		|| $team_cards
	);
$show_prices     = ( '' !== $pr_title || $price_cards_data );
$show_barrier =
	(
		'' !== $br_title
		|| $f_nonempty_editor( $br_top )
		|| $f_nonempty_editor( $br_bot )
		|| ( '' !== $br_img_src && '' !== $br_img_alt )
	);
$show_qa =
	(
		'' !== $qa_title_el
		|| '' !== $qa_sub_el
		|| '' !== $qa_prompt_el
		|| $faq_qa_pairs
	);
$fin_intro_ok      = $f_nonempty_editor( $fin_intro );
$fin_photo_strip   = $trim_scalar( $fin_photo_alt );
$final_ok_left     =
	(
		'' !== $fin_photo && '' !== $fin_photo_strip
			&& '' !== $fin_name
			&& '' !== $fin_role
	);
$final_ok_right =
	(
		'' !== $fin_heading && $fin_intro_ok
		&& '' !== $fin_btn_url && '' !== $fin_btn_lbl
	);
$show_final_cta = ( $final_ok_left && $final_ok_right );
?>
<main>
<?php if ( $show_start ) : ?>
			<section class="razion-start-block">
				<div class="razion-start-block__wrapper max-block-width wrapper-paddings">
					<?php if ( '' !== $start_title ) : ?>
					<h1 class="razion-start-block__title razion-title"><?php echo esc_html( $start_title ); ?></h1>
					<?php endif; ?>
					<?php if ( '' !== $start_subtitle ) : ?>
					<h2 class="razion-start-block__subtitle"><?php echo esc_html( $start_subtitle ); ?></h2>
					<?php endif; ?>
					<div class="razion-start-block__content">
						<div class="razion-start-block__left">
							<?php if ( $problem_blocks ) : ?>
							<div class="razion-start-block__lists">
								<?php foreach ( $problem_blocks as $block ) : ?>
								<div class="razion-start-block__list-item">
									<h3 class="razion-start-block__list-item_title"><?php echo esc_html( $block['title'] ); ?></h3>
									<ul class="razion-start-block__list-item_list">
										<?php foreach ( $block['items'] as $bullet ) : ?>
										<li class="razion-start-block__list-item_list-item"><?php echo esc_html( $bullet ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
							<?php if ( $ask_ready ) : ?>
							<div class="razion-start-block__ask ask-block ask-desktop">
								<h4 class="ask-block__title"><?php echo esc_html( $ask_title ); ?></h4>
								<a href="<?php echo esc_url( $ask_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ask_anchor ); ?></a>
							</div>
							<?php endif; ?>
						</div>
						<?php if ( $phys_ready ) : ?>
						<div class="razion-start-block__right">
							<div class="razion-start-block__right_image-wrapper">
								<img src="<?php echo esc_url( $phys_photo_src ); ?>" alt="<?php echo esc_attr( $phys_alt ); ?>" class="lazyloaded" />
								<div class="razion-start-block__right_info">
									<div class="razion-start-block__right_info-wrapper">
										<h4 class="razion-start-block__right_info-title"><?php echo esc_html( $phys_name ); ?></h4>
										<div class="razion-start-block__right_info-experience"><?php echo nl2br( esc_html( $phys_role ) ); ?></div>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
					</div>
					<?php if ( $ask_ready ) : ?>
					<div class="razion-start-block__ask-mobile ask-block ask-mobile">
						<h4 class="ask-block__title"><?php echo esc_html( $ask_title ); ?></h4>
						<a href="<?php echo esc_url( $ask_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ask_anchor ); ?></a>
					</div>
					<?php endif; ?>
				</div>
			</section>
<?php endif; ?>
<?php if ( $show_body ) : ?>
			<section class="razion-body-system">
				<div class="razion-body-system__wrapper max-block-width wrapper-paddings">
					<?php if ( '' !== $body_title ) : ?>
					<h2 class="razion-body-system__title razion-title"><?php echo esc_html( $body_title ); ?></h2>
					<?php endif; ?>
					<div class="razion-body-system__content">
						<div class="razion-body-system__left">
							<div class="razion-body-system__left-wrapper">
								<div class="razion-body-system__left-content">
									<div class="razion-body-system__left-arrow-wrapper">
										<div class="razion-body-system__left-arrow"></div>
									</div>
									<?php if ( '' !== $body_card_title || '' !== $body_lead ) : ?>
									<div class="razion-body-system__left-top">
										<?php if ( '' !== $body_card_title ) : ?>
										<h3 class="razion-body-system__left-top_title"><?php echo esc_html( $body_card_title ); ?></h3>
										<?php endif; ?>
										<?php if ( '' !== $body_lead ) : ?>
										<div class="razion-body-system__left-top_subtitle"><?php echo wp_kses_post( nl2br( esc_html( $body_lead ) ) ); ?></div>
										<?php endif; ?>
									</div>
									<?php endif; ?>
									<?php if ( '' !== $body_bottom ) : ?>
									<div class="razion-body-system__left-bottom">
										<div class="razion-body-system__left-bottom_subtitle"><?php echo wp_kses_post( nl2br( esc_html( $body_bottom ) ) ); ?></div>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<?php if ( '' !== $body_img_src && '' !== $body_img_alt ) : ?>
						<div class="razion-body-system__right">
							<img src="<?php echo esc_url( $body_img_src ); ?>" alt="<?php echo esc_attr( $body_img_alt ); ?>" class="razion-body-system__right-image lazyloaded" />
						</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
<?php endif; ?>
<?php if ( $show_cases ) : ?>
			<section class="razion-cases-slider">
				<div class="razion-cases-slider__wrapper max-block-width wrapper-paddings">
					<div class="swiper js-cases-swiper max-block-width">
						<div class="swiper-wrapper">
							<div class="swiper-wrapper__left-bg-gradient"></div>
							<div class="swiper-wrapper__right-bg-gradient"></div>
							<?php foreach ( $case_slides as $slide_html ) : ?>
							<div class="swiper-slide js-cases-swiper__slide case-slide">
								<div class="case-slide__content"><?php echo wp_kses_post( $slide_html ); ?></div>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
						<div class="swiper-button-prev">
							<img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" />
						</div>
						<div class="swiper-button-next">
							<img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" />
						</div>
					</div>
				</div>
			</section>
<?php endif; ?>


<?php if ( $show_fp ) : ?>
			<section class="razion-faq" data-faq>
				<div class="razion-faq__wrapper max-block-width wrapper-paddings">
					<?php if ( '' !== $fp_title ) : ?>
					<h2 class="razion-faq__title razion-title"><?php echo esc_html( $fp_title ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $fp_subtitle ) : ?>
					<div class="razion-faq__subtitle"><?php echo esc_html( $fp_subtitle ); ?></div>
					<?php endif; ?>
					<?php if ( '' !== $fp_prompt ) : ?>
					<div class="razion-faq__info-message"><?php echo esc_html( $fp_prompt ); ?></div>
					<?php endif; ?>
					<?php if ( $faq_fp_pairs ) : ?>
					<div class="razion-faq__questions-block">
						<ul class="razion-faq__questions-list">
							<?php
								$fpi = 0;
								foreach ( $faq_fp_pairs as $fp_it ) :
									$fpi++;
									$pid = 'razion-faq-panel-' . $fpi;
							?>
							<li class="razion-faq__questions-list-item" data-faq-item>
								<button type="button" class="razion-faq__questions-list-item_title-block" data-faq-trigger aria-expanded="false" aria-controls="<?php echo esc_attr( $pid ); ?>">
									<div class="razion-faq__questions-list-item_title-block-title"><?php echo esc_html( $fp_it['q'] ); ?></div>
									<img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" />
								</button>
								<div class="razion-faq__questions-list-item_panel" id="<?php echo esc_attr( $pid ); ?>" data-faq-panel aria-hidden="true">
									<div class="razion-faq__questions-list-item_description-block"><?php echo wp_kses_post( $fp_it['a'] ); ?></div>
								</div>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
					<?php if ( $fp_have_dl_btn ) : ?>
					<a class="razion-faq__download-button primary-button" href="<?php echo esc_url( $fp_dl_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $fp_dl_label ); ?></a>
					<?php endif; ?>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_reviews && $review_cards ) : ?>
			<section class="razion-reviews-slider">
				<div class="razion-reviews-slider__wrapper max-block-width wrapper-paddings">
					<?php if ( '' !== $rv_title ) : ?>
					<h2 class="razion-reviews-slider__title razion-title"><?php echo esc_html( $rv_title ); ?></h2>
					<?php endif; ?>
					<div class="swiper js-reviews-swiper max-block-width">
						<div class="swiper-wrapper">
							<?php foreach ( $review_cards as $rw ) : ?>
							<div class="swiper-slide photo-review-slide">
								<div class="photo-review-slide__wrapper">
									<div class="photo-review-slide__left">
										<img src="<?php echo esc_url( $rw['img'] ); ?>" alt="<?php echo esc_attr( $rw['alt'] ); ?>" class="photo-review-slide__left_image" />
									</div>
									<div class="photo-review-slide__right">
										<div class="photo-review-slide__right_arrow-container">
											<div class="photo-review-slide__right_arrow-elem"></div>
										</div>
										<div class="photo-review-slide__right_content review-content">
											<div class="review-content__name-label"><?php echo esc_html( $rw['name'] ); ?></div>
											<div class="review-content__review-text"><?php echo wp_kses_post( $rw['text'] ); ?></div>
											<?php if ( $rv_more_ok ) : ?>
											<a href="<?php echo esc_url( $rv_more_url ); ?>" target="_blank" rel="noopener"<?php echo $rw['primary_btn'] ? ' class="reviews-page__link primary-button"' : ''; ?>><?php echo esc_html( $rv_more_label ); ?></a>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="swiper-pagination"></div>
						<div class="swiper-button-prev"><img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" /></div>
						<div class="swiper-button-next"><img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" /></div>
					</div>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_vid ) : ?>
			<section class="razion-reviews-video">
				<div class="razion-reviews-video__wrapper max-block-width wrapper-paddings">
					<div class="razion-reviews-video__content">
						<?php if ( '' !== $vd_watch_raw || '' !== $vd_thumb_raw ) : ?>
						<a href="<?php echo esc_url( '' !== $vd_watch_raw ? $vd_watch : $vd_thumb ); ?>" class="razion-reviews-video__left" target="_blank" rel="noopener">
							<?php if ( '' !== $vd_thumb ) : ?>
								<img src="<?php echo esc_url( $vd_thumb ); ?>" alt="<?php echo esc_attr( $vd_thumb_alt ); ?>" />
							<?php endif; ?>
						</a>
						<?php endif; ?>
						<div class="razion-reviews-video__right">
							<div class="razion-reviews-video__right_wrapper">
								<?php if ( '' !== $vd_quote ) : ?>
								<div class="razion-reviews-video__subtitle"><?php echo esc_html( $vd_quote ); ?></div>
								<?php endif; ?>
								<?php if ( '' !== $vd_guest ) : ?>
								<div class="razion-reviews-video__name"><?php echo esc_html( $vd_guest ); ?></div>
								<?php endif; ?>
								<?php if ( '' !== $vd_btn_lbl && '' !== $vd_watch ) : ?>
								<a class="razion-reviews-video__button regular-button" href="<?php echo esc_url( $vd_watch ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $vd_btn_lbl ); ?></a>
								<?php endif; ?>
							</div>
							<div class="razion-reviews-video__right_green-bg"></div>
						</div>
					</div>
					<?php if ( '' !== $vd_ex_lbl && '' !== $vd_ex_url ) : ?>
					<div class="razion-reviews-video__button-wrapper"><a class="primary-button" href="<?php echo esc_url( $vd_ex_url ); ?>"><?php echo esc_html( $vd_ex_lbl ); ?></a></div>
					<?php endif; ?>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_team ) : ?>
			<section class="team">
				<div class="team__container">
					<div class="team__header title-subtitle-header">
						<?php if ( '' !== $tm_title ) : ?>
						<h2 class="team__title section-title"><?php echo esc_html( $tm_title ); ?></h2>
						<?php endif; ?>
						<?php if ( '' !== trim( wp_strip_all_tags( $tm_intro, true ) ) ) : ?>
						<p class="team__description"><?php echo nl2br( esc_html( $tm_intro ) ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $team_cards ) : ?>
					<div class="team__grid">
						<?php foreach ( $team_cards as $member ) : ?>
						<div class="team-item">
							<div class="team-item__wrapper">
								<div class="team-img">
									<img src="<?php echo esc_url( $member['photo'] ); ?>" class="img-fluid" alt="<?php echo esc_attr( $member['name'] ); ?>" />
								</div>
								<div class="team-title"><h4 class="mb-0"><?php echo esc_html( $member['name'] ); ?></h4><p class="mb-0"><?php echo esc_html( $member['role'] ); ?></p></div>
								<?php if ( $member['social_links'] ) : ?>
								<div class="team-icon">
									<?php foreach ( $member['social_links'] as $sl ) : ?>
										<a class="btn btn-primary btn-sm-square rounded-circle me-3" href="<?php echo esc_url( $sl['href'] ); ?>" target="_blank" rel="noopener"><i class="<?php echo esc_attr( $sl['icon_class'] ); ?>"></i></a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_prices && $price_cards_data ) : ?>
			<section class="razion-pricing">
				<div class="razion-pricing__wrapper max-block-width wrapper-paddings">
					<div class="razion-pricing__content">
						<div class="razion-pricing__green-bg"></div>
						<div class="razion-pricing__white-bg"></div>
						<?php if ( '' !== $pr_title ) : ?>
						<h2 class="razion-pricing__title razion-title"><?php echo esc_html( $pr_title ); ?></h2>
						<?php endif; ?>
						<div class="razion-pricing__cards">
							<?php foreach ( $price_cards_data as $pc ) : ?>
							<div class="razion-pricing__card">
								<div class="razion-pricing__card_top">
									<div class="razion-pricing__card_subtitle"><?php echo esc_html( $pc['subtitle'] ); ?></div>
									<div class="razion-pricing__card_price"><?php echo esc_html( $pc['cost'] ); ?></div>
								</div>
								<a class="primary-button" href="<?php echo esc_url( $pc['link'] ); ?>"><?php echo esc_html__( 'Подробнее', 'moveat' ); ?></a>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_barrier ) : ?>
			<section class="razion-text-block">
				<div class="razion-text-block__wrapper max-block-width wrapper-paddings">
					<div class="razion-text-block__left">
						<div class="razion-text-block__left_content-wrapper">
							<?php if ( '' !== $br_title ) : ?>
							<div class="razion-text-block__left_top">
								<h3 class="razion-text-block__left_top-title"><?php echo esc_html( $br_title ); ?></h3>
								<?php if ( $f_nonempty_editor( $br_top ) ) : ?>
								<div class="razion-text-block__left_top-text"><?php echo wp_kses_post( $br_top ); ?></div>
								<?php endif; ?>
							</div>
							<?php elseif ( $f_nonempty_editor( $br_top ) ) : ?>
							<div class="razion-text-block__left_top"><div class="razion-text-block__left_top-text"><?php echo wp_kses_post( $br_top ); ?></div></div>
							<?php endif; ?>
							<?php if ( $f_nonempty_editor( $br_bot ) ) : ?>
							<div class="razion-text-block__left_bottom"><div class="razion-text-block__left_bottom-text"><?php echo wp_kses_post( $br_bot ); ?></div></div>
							<?php endif; ?>
							<div class="razion-text-block__left_arrow-container"><div class="razion-text-block__left_arrow"></div></div>
						</div>
					</div>
					<div class="razion-text-block__right">
						<?php if ( '' !== $br_img_src && '' !== $br_img_alt ) : ?>
						<img src="<?php echo esc_url( $br_img_src ); ?>" alt="<?php echo esc_attr( $br_img_alt ); ?>" />
						<?php endif; ?>
					</div>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_qa && $faq_qa_pairs ) : ?>
			<section class="razion-faq razion-faq--qa-extra" data-faq-cd-secondary>
				<div class="razion-faq__wrapper max-block-width wrapper-paddings">
					<?php if ( '' !== $qa_title_el ) : ?>
					<h2 class="razion-faq__title razion-title"><?php echo esc_html( $qa_title_el ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $qa_sub_el ) : ?>
					<div class="razion-faq__subtitle"><?php echo esc_html( $qa_sub_el ); ?></div>
					<?php endif; ?>
					<?php if ( '' !== $qa_prompt_el ) : ?>
					<div class="razion-faq__info-message"><?php echo esc_html( $qa_prompt_el ); ?></div>
					<?php endif; ?>
					<div class="razion-faq__questions-block">
						<ul class="razion-faq__questions-list">
							<?php
								$qai = 0;
								foreach ( $faq_qa_pairs as $qa_it ) :
									$qai++;
									$qapid = 'calculate-diet-qa-panel-' . $qai;
							?>
							<li class="razion-faq__questions-list-item" data-faq-item>
								<button type="button" class="razion-faq__questions-list-item_title-block" data-faq-trigger aria-expanded="false" aria-controls="<?php echo esc_attr( $qapid ); ?>">
									<div class="razion-faq__questions-list-item_title-block-title"><?php echo esc_html( $qa_it['q'] ); ?></div>
									<img src="<?php echo esc_url( $icon_arrow_src ); ?>" alt="" />
								</button>
								<div class="razion-faq__questions-list-item_panel" id="<?php echo esc_attr( $qapid ); ?>" data-faq-panel aria-hidden="true">
									<div class="razion-faq__questions-list-item_description-block"><?php echo wp_kses_post( $qa_it['a'] ); ?></div>
								</div>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</section>
<?php endif; ?>

<?php if ( $show_final_cta ) : ?>
			<section class="razion-final-question">
				<div class="razion-final-question__wrapper max-block-width wrapper-paddings">
					<div class="razion-final-question__left">
						<div class="razion-final-question__left_green-bg"></div>
						<div class="razion-final-question__left_content">
							<img src="<?php echo esc_url( $fin_photo ); ?>" alt="<?php echo esc_attr( $fin_photo_alt ); ?>" class="razion-final-question__left_image" />
							<div class="razion-final-question__left_info">
								<div class="razion-final-question__left_info-title"><?php echo esc_html( $fin_name ); ?></div>
								<div class="razion-final-question__left_info-subtitle"><?php echo nl2br( esc_html( $fin_role ) ); ?></div>
							</div>
						</div>
					</div>
					<div class="razion-final-question__right">
						<div class="razion-final-question__right_content">
							<div class="razion-final-question__right_green-bg"></div>
							<div class="razion-final-question__right_white-bg"></div>
							<?php if ( '' !== $fin_heading ) : ?>
							<h3 class="razion-final-question__right_title"><?php echo esc_html( $fin_heading ); ?></h3>
							<?php endif; ?>
							<div class="razion-final-question__right_text"><?php echo wp_kses_post( $fin_intro ); ?></div>
							<a href="<?php echo esc_url( $fin_btn_url ); ?>" class="razion-final-question__right_button" target="_blank" rel="noopener"><?php echo esc_html( $fin_btn_lbl ); ?></a>
						</div>
					</div>
				</div>
			</section>
<?php endif; ?>

</main>
<?php
get_footer();

