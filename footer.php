    <!-- Footer Module -->
	<div class="footer">
		<div class="footer__container">
			<div class="footer-top">
				<div class="footer-top__logo">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="Logo">
				</div>
				<div class="footer-top__links">
					<div class="footer-top__links-block">
						<?php $i = 1;
						while ( $text = get_field( 'links_block_1_link_' . $i . '_text', GLOBAL_SETTINGS_PAGE_ID ) ) :
							$url = get_field( 'links_block_1_link_' . $i . '_url', GLOBAL_SETTINGS_PAGE_ID ); ?>
							<a class="footer-top__link" href="<?php echo esc_url( $url ?: '#' ); ?>"><?php echo esc_html( $text ); ?></a>
						<?php $i++; endwhile; ?>
					</div>
					<div class="footer-top__links-block">
						<?php $i = 1;
						while ( $text = get_field( 'links_block_2_link_' . $i . '_text', GLOBAL_SETTINGS_PAGE_ID ) ) :
							$url = get_field( 'links_block_2_link_' . $i . '_url', GLOBAL_SETTINGS_PAGE_ID ); ?>
							<a class="footer-top__link" href="<?php echo esc_url( $url ?: '#' ); ?>"><?php echo esc_html( $text ); ?></a>
						<?php $i++; endwhile; ?>
					</div>
					<div class="footer-top__links-block">
						<?php $i = 1;
						while ( $text = get_field( 'links_block_3_link_' . $i . '_text', GLOBAL_SETTINGS_PAGE_ID ) ) :
							$url = get_field( 'links_block_3_link_' . $i . '_url', GLOBAL_SETTINGS_PAGE_ID ); ?>
							<a class="footer-top__link" href="<?php echo esc_url( $url ?: '#' ); ?>"><?php echo esc_html( $text ); ?></a>
						<?php $i++; endwhile; ?>
					</div>
				</div>
				<div class="footer-top__other-links">
					<div class="footer-top__other-links_socials">
						<h5 class="footer-top__other-links_socials-title"><?php echo esc_html( get_field( 'socials_title', GLOBAL_SETTINGS_PAGE_ID ) ); ?></h5>
						<div class="footer-top__other-links_socials-icons">
						<?php $i = 1;
						while ( $img = get_field( 'social_img_' . $i, GLOBAL_SETTINGS_PAGE_ID ) ) :
							$url = get_field( 'social_url_' . $i, GLOBAL_SETTINGS_PAGE_ID ); ?>
							<a href="<?php echo esc_url( $url ?: '#' ); ?>">
								<img src="<?php echo esc_url( $img ); ?>" alt="Social <?php echo $i; ?>">
							</a>
						<?php $i++; endwhile; ?>
						</div>
					</div>
					<div class="footer-top__other-links_payments">
						<h5 class="footer-top__other-links_payments-title"><?php echo esc_html( get_field( 'payments_title', GLOBAL_SETTINGS_PAGE_ID ) ); ?></h5>
						<div class="footer-top__other-links_payments-icons">
						<?php $i = 1;
						while ( $img = get_field( 'payment_img_' . $i, GLOBAL_SETTINGS_PAGE_ID ) ) : ?>
							<a href="#">
								<img src="<?php echo esc_url( $img ); ?>" alt="Payment <?php echo $i; ?>">
							</a>
						<?php $i++; endwhile; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="footer-bottom">
				<p class="footer-bottom__text">
					<?php echo esc_html( get_field( 'copyright_text', GLOBAL_SETTINGS_PAGE_ID ) ); ?>
				</p>
			</div>
		</div>
	</div>
	<button class="back-to-top-button">
		<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/arrow.png" alt="Arrow up">
	</button>

	<?php wp_footer(); ?>
</body>

</html>
