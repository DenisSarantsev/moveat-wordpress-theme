<?php
/**
 * Template Name: Страница с мессенджерами
 * Template Post Type: page
 * Description: Контент и иконки мессенджеров — поля из assets/acf-fields/messengers-page.json.
 */

defined( 'ABSPATH' ) || exit;

$page_id = get_queried_object_id();

$messengers_page_title = trim( (string) get_field( 'messengers_page_title', $page_id ) );
$messengers_page_title = $messengers_page_title !== '' ? $messengers_page_title : 'Пишите про ваши проблемы и цели. Подберу для вас оптимальное решение!';

$messengers_page_body_text = trim( (string) get_field( 'messengers_page_body_text', $page_id ) );
$messengers_page_body_text = $messengers_page_body_text !== '' ? $messengers_page_body_text : 'Lorem ipsum, dolor sit amet consectetur adipisicing elit. Illum ratione vitae pariatur iure hic veritatis, praesentium cupiditate nam, quia rerum autem! Sunt, consectetur reiciendis culpa nobis voluptatibus atque corrupti iusto! Lorem ipsum, dolor sit amet consectetur adipisicing elit. Illum ratione vitae pariatur iure hic veritatis, praesentium cupiditate nam, quia rerum autem! Sunt, consectetur reiciendis culpa nobis voluptatibus atque corrupti iusto!';

$messengers_page_left_image     = get_field( 'messengers_page_left_image', $page_id );
$messengers_page_left_image_alt = trim( (string) get_field( 'messengers_page_left_image_alt', $page_id ) );

$messengers_page_messengers_caption = trim( (string) get_field( 'messengers_page_messengers_caption', $page_id ) );
$messengers_page_messengers_caption = $messengers_page_messengers_caption !== '' ? $messengers_page_messengers_caption : 'Выберите удобный мессенджер и напишите нам';

$social_links = [];
for ( $si = 1; $si <= 10; $si++ ) {
	$social_url   = trim( (string) get_field( 'messengers_social_' . $si . '_url', $page_id ) );
	$social_icon  = get_field( 'messengers_social_' . $si . '_icon', $page_id );
	$social_color = get_field( 'messengers_social_' . $si . '_wrapper_color', $page_id );
	$social_label = trim( (string) get_field( 'messengers_social_' . $si . '_label', $page_id ) );

	if ( $social_url === '' || empty( $social_icon ) ) {
		continue;
	}

	$color_str = is_string( $social_color ) ? trim( $social_color ) : '';
	if ( $color_str === '' ) {
		$color_str = '#CCCCCC';
	}

	$social_links[] = [
		'url'   => $social_url,
		'icon'  => $social_icon,
		'color' => $color_str,
		'label' => $social_label !== '' ? $social_label : __( 'Мессенджер', 'moveat' ),
	];
}

$show_messengers_block = $messengers_page_messengers_caption !== '' || count( $social_links ) > 0;

get_header();
?>

<main class="messengers-page">
	<div class="messengers-page__wrapper">
		<div class="messengers-page__content">
			<div class="messengers-page__left">
				<?php if ( ! empty( $messengers_page_left_image ) ) : ?>
					<img
						src="<?php echo esc_url( $messengers_page_left_image ); ?>"
						alt="<?php echo esc_attr( $messengers_page_left_image_alt ); ?>"
						class="messengers-page__left_image"
						loading="lazy"
						decoding="async" />
				<?php endif; ?>
			</div>
			<div class="messengers-page__right">
				<h1 class="messengers-page__title"><?php echo esc_html( $messengers_page_title ); ?></h1>
				<?php if ( ! empty( $messengers_page_left_image ) ) : ?>
					<img
						src="<?php echo esc_url( $messengers_page_left_image ); ?>"
						alt="<?php echo esc_attr( $messengers_page_left_image_alt ); ?>"
						class="messengers-page__left_image_mobile"
						loading="lazy"
						decoding="async" />
				<?php endif; ?>
				<?php if ( $messengers_page_body_text !== '' ) : ?>
					<div class="messengers-page__right_text">
						<?php echo wp_kses_post( nl2br( esc_html( $messengers_page_body_text ), false ) ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $show_messengers_block ) : ?>
					<div class="club-page__messengers">
						<div class="club-page__messengers-panel">
							<?php if ( $messengers_page_messengers_caption !== '' ) : ?>
								<div class="club-page__messengers-title">
									<?php echo wp_kses_post( nl2br( esc_html( $messengers_page_messengers_caption ), false ) ); ?>
								</div>
							<?php endif; ?>
							<?php if ( count( $social_links ) > 0 ) : ?>
								<div class="club-page__messengers-row">
									<?php foreach ( $social_links as $item ) : ?>
										<?php
										$href    = $item['url'];
										$parsed  = wp_parse_url( $href );
										$scheme  = isset( $parsed['scheme'] ) ? strtolower( (string) $parsed['scheme'] ) : '';
										$is_http = in_array( $scheme, [ 'http', 'https' ], true );
										$esc_href = esc_url( $href, [ 'http', 'https', 'viber', 'mailto', 'tel' ] );
										$link_rel = 'noopener noreferrer';
										$target_attr = $is_http ? ' target="_blank"' : '';
										?>
										<a
											class="club-page__messengers-link"
											href="<?php echo $esc_href; ?>"
											<?php echo $target_attr; ?>
											rel="<?php echo esc_attr( $link_rel ); ?>"
											style="background-color: <?php echo esc_attr( $item['color'] ); ?>;"
											aria-label="<?php echo esc_attr( sprintf( __( 'Открыть %s', 'moveat' ), $item['label'] ) ); ?>">
											<img
												src="<?php echo esc_url( $item['icon'] ); ?>"
												alt="<?php echo esc_attr( $item['label'] ); ?>"
												loading="lazy"
												decoding="async" />
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
