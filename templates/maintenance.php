<?php
/*
	Template Name: Техническое обслуживание
*/

// Тексты вёрстки на языке страницы — assets/dictionaries/templates/maintenance.
$page_dict = moveat_dictionary( 'templates/maintenance' );
$t = function ( $key ) use ( $page_dict ) {
	return moveat_dictionary_text( $page_dict, $key );
};
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head data-head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<style>
		[data-spinner] {
			position: fixed;
			inset: 0;
			background: #fff;
			z-index: 9999;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: opacity 0.4s ease;
		}

		[data-spinner].hide {
			opacity: 0;
			pointer-events: none;
		}
	</style>
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'tech-page-body' ); ?>>
	<?php wp_body_open(); ?>
	<div data-spinner></div>

	<main class="tech-page">
		<section class="tech-page__section">
			<div class="tech-page__container max-width-limiter">
				<div class="tech-page__badge"><?php echo esc_html( $t( 'badge' ) ); ?></div>

				<h1 class="tech-page__title"><?php echo esc_html( $t( 'title' ) ); ?></h1>

				<div class="tech-page__content">
					<p><?php echo esc_html( $t( 'intro' ) ); ?></p>

					<h2><?php echo esc_html( $t( 'what_title' ) ); ?></h2>
					<p><?php echo esc_html( $t( 'what_text' ) ); ?></p>

					<p><?php echo esc_html( $t( 'contact_text' ) ); ?></p>

					<p><?php echo esc_html( $t( 'channels_text' ) ); ?></p>
				</div>

				<div class="tech-page__links" aria-label="<?php echo esc_attr( $t( 'links_label' ) ); ?>">
					<?php foreach ( [ 'telegram', 'whatsapp', 'viber', 'instagram' ] as $channel ) : ?>
						<?php
						$channel_url = $t( $channel . '_url' );
						if ( '' === $channel_url ) {
							continue;
						}
						// viber:// открывает приложение — новая вкладка ему не нужна.
						$is_app_link = 'viber' === $channel;
						?>
						<a class="tech-page__link tech-page__link--<?php echo esc_attr( $channel ); ?>" href="<?php echo esc_url( $channel_url, [ 'http', 'https', 'viber' ] ); ?>"<?php echo $is_app_link ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>>
							<span class="tech-page__link-name"><?php echo esc_html( $t( $channel . '_name' ) ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>

				<p class="tech-page__footer-text"><?php echo esc_html( $t( 'footer' ) ); ?></p>
			</div>
		</section>
	</main>
	<?php wp_footer(); ?>
</body>

</html>
