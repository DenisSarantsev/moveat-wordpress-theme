<?php
/*
	Template Name: Партнеры
	Template Post Type: page
*/

defined( 'ABSPATH' ) || exit;

$page_id         = get_queried_object_id();
$assets_uri      = get_template_directory_uri() . '/assets/images';
$placeholder_img = $assets_uri . '/illustrations/vegetables.jpg';
$check_icon      = $assets_uri . '/icons/filled/check-mark-filled.png';

if ( ! function_exists( 'moveat_partners_paragraphs' ) ) {
	/*
	 Разбивает текст на абзацы по пустой строке
	 и оборачивает каждый в <p> с указанным классом
	*/
	function moveat_partners_paragraphs( $text, $class ) {
		$text = trim( (string) $text );
		if ( $text === '' ) {
			return '';
		}
		$blocks = preg_split( '/\n\s*\n/', $text );
		$html   = '';
		foreach ( $blocks as $block ) {
			$block = trim( $block );
			if ( $block === '' ) {
				continue;
			}
			$html .= '<p class="' . esc_attr( $class ) . '">' . nl2br( esc_html( $block ) ) . '</p>';
		}
		return $html;
	}
}

if ( ! function_exists( 'moveat_partners_list_items' ) ) {
	/*
	 Разбивает текст построчно и оборачивает
	 каждую строку в <li> с указанным классом
	*/
	function moveat_partners_list_items( $text, $class ) {
		$text = trim( (string) $text );
		if ( $text === '' ) {
			return '';
		}
		$lines = preg_split( '/\r\n|\r|\n/', $text );
		$html  = '';
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}
			$html .= '<li class="' . esc_attr( $class ) . '">' . esc_html( $line ) . '</li>';
		}
		return $html;
	}
}

$hero_title  = trim( (string) get_field( 'partners_hero_title', $page_id ) );
$hero_title  = $hero_title !== '' ? $hero_title : 'С кем мы сотрудничаем';

$intro_text = get_field( 'partners_intro_text', $page_id );

/*
 Декоративная часть блоков «Типы партнёров»:
 цветовой модификатор, порядковый номер и SVG-иконка.
 Редактируемый контент (изображение, заголовок, текст) берётся из ACF.
*/
$collab_meta = [
	[
		'color' => 'is-green',
		'index' => '01',
		'svg'   => '<path d="m6.5 6.5 11 11" /><path d="m21 21-1-1" /><path d="m3 3 1 1" /><path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />',
	],
	[
		'color' => 'is-orange',
		'index' => '02',
		'svg'   => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />',
	],
	[
		'color' => 'is-green',
		'index' => '03',
		'svg'   => '<circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" />',
	],
	[
		'color' => 'is-orange',
		'index' => '04',
		'svg'   => '<rect width="20" height="14" x="2" y="7" rx="2" /><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />',
	],
	[
		'color' => 'is-green',
		'index' => '05',
		'svg'   => '<path d="M9 3h6" /><path d="M10 3v6.5L4.5 18a2 2 0 0 0 1.8 3h11.4a2 2 0 0 0 1.8-3L14 9.5V3" /><path d="M6.5 15h11" />',
	],
	[
		'color' => 'is-orange',
		'index' => '06',
		'svg'   => '<rect x="9" y="2" width="6" height="12" rx="3" /><path d="M5 10a7 7 0 0 0 14 0" /><path d="M12 17v4" /><path d="M8 21h8" />',
	],
];

get_header();
?>

<div class="hero-block">
	<div class="hero-block__bg-wrapper">
		<img
			class="hero-block__bg-image"
			src="<?php echo esc_url( $assets_uri . '/illustrations/vegetables.jpg' ); ?>"
			alt="<?php echo esc_attr__( 'vegetables', 'moveat' ); ?>" />
	</div>
	<div class="hero-block__container">
		<h1 class="hero-block__title"><?php echo esc_html( $hero_title ); ?></h1>
		<nav aria-label="breadcrumb" class="page-hero__breadcrumbs">
			<ol class="breadcrumb no-padding page-hero__breadcrumbs-list">
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<a class="text-body" href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a>
				</li>
				<li class="breadcrumb-item page-hero__breadcrumbs-item white">
					<span class="text-body">Партнёры</span>
				</li>
			</ol>
		</nav>
	</div>
</div>

<!-- Partners Page Start -->
<main class="partners-page">
	<!-- Вступление -->
	<?php if ( trim( (string) $intro_text ) !== '' ) : ?>
		<section class="partners-page__section partners-page__section--tight">
			<div class="partners-page__container">
				<div class="partners-intro">
					<?php echo moveat_partners_paragraphs( $intro_text, 'partners-intro__text' ); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Типы партнёров -->
	<section class="partners-page__section">
		<div class="partners-page__container">
			<div class="partners-collab">
				<?php foreach ( $collab_meta as $i => $meta ) : ?>
					<?php
					$num          = $i + 1;
					$collab_image = get_field( "partners_collab_{$num}_image", $page_id );
					$collab_image = $collab_image ? $collab_image : $placeholder_img;
					$collab_title = get_field( "partners_collab_{$num}_title", $page_id );
					$collab_text  = get_field( "partners_collab_{$num}_text", $page_id );

					if ( trim( (string) $collab_title ) === '' && trim( (string) $collab_text ) === '' ) {
						continue;
					}
					?>
					<article class="partners-collab__row">
						<div class="partners-collab__media <?php echo esc_attr( $meta['color'] ); ?>">
							<span class="partners-collab__index"><?php echo esc_html( $meta['index'] ); ?></span>
							<img
								class="partners-collab__image"
								src="<?php echo esc_url( $collab_image ); ?>"
								alt="<?php echo esc_attr( $collab_title ); ?>"
								loading="lazy"
								decoding="async" />
						</div>
						<div class="partners-collab__body">
							<span class="partners-collab__chip <?php echo esc_attr( $meta['color'] ); ?>">
								<svg
									viewBox="0 0 24 24"
									fill="none"
									stroke="currentColor"
									stroke-width="2"
									stroke-linecap="round"
									stroke-linejoin="round"
									aria-hidden="true"><?php echo $meta['svg']; ?></svg>
							</span>
							<?php if ( trim( (string) $collab_title ) !== '' ) : ?>
								<h2 class="partners-collab__title"><?php echo esc_html( $collab_title ); ?></h2>
							<?php endif; ?>
							<?php echo moveat_partners_paragraphs( $collab_text, 'partners-collab__text' ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Что получат ваши клиенты -->
	<?php
	$benefits_title = get_field( 'partners_benefits_title', $page_id );
	$benefits       = [];
	for ( $b = 1; $b <= 7; $b++ ) {
		$b_title = get_field( "partners_benefit_{$b}_title", $page_id );
		$b_text  = get_field( "partners_benefit_{$b}_text", $page_id );
		if ( trim( (string) $b_title ) === '' && trim( (string) $b_text ) === '' ) {
			continue;
		}
		$benefits[] = [ 'title' => $b_title, 'text' => $b_text ];
	}
	?>
	<?php if ( trim( (string) $benefits_title ) !== '' || count( $benefits ) > 0 ) : ?>
		<section class="partners-page__section partners-page__section--muted">
			<div class="partners-page__container">
				<?php if ( trim( (string) $benefits_title ) !== '' ) : ?>
					<div class="partners-page__section-head">
						<h2 class="partners-page__section-title"><?php echo esc_html( $benefits_title ); ?></h2>
					</div>
				<?php endif; ?>

				<?php if ( count( $benefits ) > 0 ) : ?>
					<div class="partners-benefits">
						<?php foreach ( $benefits as $benefit ) : ?>
							<div class="partners-benefits__item">
								<div class="partners-benefits__head">
									<span class="partners-benefits__check">
										<img src="<?php echo esc_url( $check_icon ); ?>" alt="" loading="lazy" decoding="async" />
									</span>
									<?php if ( trim( (string) $benefit['title'] ) !== '' ) : ?>
										<h3 class="partners-benefits__title"><?php echo esc_html( $benefit['title'] ); ?></h3>
									<?php endif; ?>
								</div>
								<?php if ( trim( (string) $benefit['text'] ) !== '' ) : ?>
									<p class="partners-benefits__text"><?php echo nl2br( esc_html( trim( (string) $benefit['text'] ) ) ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- Что получите вы -->
	<?php
	$value_title   = get_field( 'partners_value_title', $page_id );
	$value_lead    = get_field( 'partners_value_lead', $page_id );
	$value_subhead = get_field( 'partners_value_subhead', $page_id );
	$value_list    = get_field( 'partners_value_list', $page_id );
	$value_text    = get_field( 'partners_value_text', $page_id );
	?>
	<section class="partners-page__section">
		<div class="partners-page__container">
			<?php if ( trim( (string) $value_title ) !== '' || trim( (string) $value_lead ) !== '' ) : ?>
				<div class="partners-page__section-head">
					<?php if ( trim( (string) $value_title ) !== '' ) : ?>
						<h2 class="partners-page__section-title"><?php echo esc_html( $value_title ); ?></h2>
					<?php endif; ?>
					<?php if ( trim( (string) $value_lead ) !== '' ) : ?>
						<p class="partners-page__section-lead"><?php echo nl2br( esc_html( trim( (string) $value_lead ) ) ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="partners-value">
				<?php if ( trim( (string) $value_subhead ) !== '' ) : ?>
					<p class="partners-value__subhead"><?php echo esc_html( $value_subhead ); ?></p>
				<?php endif; ?>
				<?php if ( trim( (string) $value_list ) !== '' ) : ?>
					<ul class="partners-list">
						<?php echo moveat_partners_list_items( $value_list, 'partners-list__item' ); ?>
					</ul>
				<?php endif; ?>
				<?php echo moveat_partners_paragraphs( $value_text, 'partners-value__text' ); ?>
			</div>
		</div>
	</section>

	<!-- Также мы можем рассмотреть -->
	<?php
	$consider_title = get_field( 'partners_consider_title', $page_id );
	$consider_list  = get_field( 'partners_consider_list', $page_id );
	$consider_text  = get_field( 'partners_consider_text', $page_id );
	?>
	<section class="partners-page__section partners-page__section--muted">
		<div class="partners-page__container">
			<?php if ( trim( (string) $consider_title ) !== '' ) : ?>
				<div class="partners-page__section-head">
					<h2 class="partners-page__section-title"><?php echo esc_html( $consider_title ); ?></h2>
				</div>
			<?php endif; ?>

			<div class="partners-value">
				<?php if ( trim( (string) $consider_list ) !== '' ) : ?>
					<ul class="partners-list">
						<?php echo moveat_partners_list_items( $consider_list, 'partners-list__item' ); ?>
					</ul>
				<?php endif; ?>
				<?php echo moveat_partners_paragraphs( $consider_text, 'partners-value__text' ); ?>
			</div>
		</div>
	</section>

	<!-- Финальный призыв -->
	<?php
	$cta_title = get_field( 'partners_cta_title', $page_id );
	$cta_text  = get_field( 'partners_cta_text', $page_id );
	$cta_email = get_field( 'partners_cta_email', $page_id );
	$cta_note  = get_field( 'partners_cta_note', $page_id );
	?>
	<section class="partners-page__section">
		<div class="partners-page__container">
			<div class="partners-cta">
				<?php if ( trim( (string) $cta_title ) !== '' ) : ?>
					<h2 class="partners-cta__title"><?php echo esc_html( $cta_title ); ?></h2>
				<?php endif; ?>
				<?php if ( trim( (string) $cta_text ) !== '' ) : ?>
					<p class="partners-cta__text"><?php echo nl2br( esc_html( trim( (string) $cta_text ) ) ); ?></p>
				<?php endif; ?>
				<?php if ( trim( (string) $cta_email ) !== '' ) : ?>
					<a
						class="partners-cta__email"
						href="<?php echo esc_url( 'mailto:' . antispambot( $cta_email ) ); ?>">
						<svg
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="2"
							stroke-linecap="round"
							stroke-linejoin="round"
							aria-hidden="true">
							<rect width="20" height="16" x="2" y="4" rx="2" />
							<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
						</svg>
						<?php echo esc_html( antispambot( $cta_email ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( trim( (string) $cta_note ) !== '' ) : ?>
					<p class="partners-cta__note"><?php echo esc_html( $cta_note ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<!-- Partners Page End -->

<?php get_footer(); ?>
