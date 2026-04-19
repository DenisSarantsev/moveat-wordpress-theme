<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Moveat - Еда станет вашим лекарством!</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
		<?php if ( has_site_icon() ) : ?>
			<link href="<?php echo esc_url( get_site_icon_url() ); ?>" rel="icon">
		<?php endif; ?>
    <?php wp_head(); ?>
</head>

<body>
		<!-- <div id="spinner" data-spinner class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
			<div class="spinner-border text-primary" role="status"></div>
		</div> -->

		<!-- Render gtranslate inside a hidden container to keep functionality but hide UI -->
		<div id="gtranslate-container" style="display:none !important;">
			<?php echo do_shortcode('[google-translator]'); ?>
		</div>

    <!-- Header Module -->
    <header class="header container-fluid fixed-top px-0 wow fadeIn" data-wow-delay="0.1s">
			<nav class="header__navbar navbar navbar-light wow fadeIn" data-wow-delay="0.1s">
				<?php
					$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
					$home_url       = esc_url( home_url( '/' ) );
					if ( $custom_logo_id ) {
						$logo_img = wp_get_attachment_image(
							$custom_logo_id,
							'full',
							false,
							[
								'class' => 'logo-img',
								'alt'   => esc_attr( get_bloginfo( 'name' ) ),
							]
						);
					}
				?>
				<a href="<?php echo $home_url; ?>" class="navbar-brand">
					<?php echo $logo_img; ?>
				</a>
				<div class="navbar-content">
					<?php
						wp_nav_menu( [
							'theme_location' => 'header_menu',
							'container'      => false,
							'menu_class'     => 'navbar-nav ms-auto p-4 p-lg-0',
							'fallback_cb'    => false,
						] );
					?>
					<div class="navbar-content__top-background"></div>

					<!-- Language switcher -->
					<div class="lang-switcher" tabindex="0" aria-label="Выбор языка">
						<input type="radio" name="site-lang" id="lang-ru" checked hidden />
						<input type="radio" name="site-lang" id="lang-uk" hidden />
						<!-- Current (display controlled via CSS based on the checked input) -->
						<div
							class="lang-switcher__toggle"
							role="button"
							aria-haspopup="listbox">
							<span class="lang-switcher__flag" aria-hidden="true"></span>
							<span class="lang-switcher__code lang-switcher__code--ru">RU</span>
							<span class="lang-switcher__code lang-switcher__code--uk">UA</span>
							<svg
								class="lang-switcher__arrow"
								width="10"
								height="6"
								viewBox="0 0 10 6"
								fill="none"
								xmlns="http://www.w3.org/2000/svg"
								aria-hidden="true">
								<path
									d="M1 1L5 5L9 1"
									stroke="currentColor"
									stroke-width="1.5"
									stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
						</div>
						<ul class="lang-switcher__list" role="listbox" aria-label="Выбор языка">
							<li>
								<label for="lang-ru" class="lang-switcher__option">
									<span class="lang-switcher__flag" aria-hidden="true"></span>
									<span class="lang-switcher__option-code">RU</span>
									<span class="lang-switcher__option-name">Русский</span>
								</label>
							</li>
							<li>
								<label for="lang-uk" class="lang-switcher__option">
									<span
										class="lang-switcher__flag lang-switcher__flag--uk"
										aria-hidden="true"></span>
									<span class="lang-switcher__option-code">UA</span>
									<span class="lang-switcher__option-name">Українська</span>
								</label>
							</li>
						</ul>
					</div>

					<div class="header-icons">
						<!-- <a class="header-icon" href="">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/search.png" alt="Search" class="img-fluid">
						</a>
						<a class="header-icon" href="">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/user.png" alt="User" class="img-fluid">
						</a> -->
						<?php
							$moveat_cart_count = function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
						?>
						<a class="header-icon cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) ); ?>">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.png" alt="Shopping cart" class="img-fluid">
							<div class="cart-count" data-cart-count <?php echo $moveat_cart_count > 0 ? '' : 'hidden'; ?>><?php echo esc_html( $moveat_cart_count ); ?></div>
						</a>
					</div>
				</div>

				<div class="header__menu-icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/mobile-menu.png" alt="mobile menu icon" class="header__mobile-menu-icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cross.png" alt="cross icon" class="header__cross-icon">
				</div>
			</nav>
		</header>

		<div class="messages-container">
			<div class="messages-container__message messages-container__message--success is-template">
				<div class="messages-container__message-icon success-icon">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/check.png' ); ?>" alt="Success" class="img-fluid">
				</div>
				<div class="messages-container__message-icon warning-icon">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/warning.png' ); ?>" alt="Warning" class="img-fluid">
				</div>
				<div class="messages-container__message-icon error-icon">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/error.png' ); ?>" alt="Error" class="img-fluid">
				</div>
				<div class="messages-container__message-icon info-icon">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/info.png' ); ?>" alt="Info" class="img-fluid">
				</div>
				<div class="messages-container__message-text">
					Ваш заказ успешно оформлен lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quos.
				</div>
				<button type="button" class="messages-container__message-close" aria-label="Закрыть уведомление" data-message-close>
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/cross.png' ); ?>" alt="Close" class="img-fluid">
				</button>
			</div>
		</div>

		<div class="lang-modal" id="lang-modal" role="dialog" aria-modal="true">
			<div class="lang-modal__overlay" data-lang-modal-overlay></div>
			<div class="lang-modal__dialog" role="document">
				<button
					class="lang-modal__close"
					type="button"
					aria-label="Закрыть"
					data-lang-modal-close>
					&times;
				</button>
				<div class="lang-modal__body">
					<h3 class="lang-modal__title">Выберите язык</h3>
					<p class="lang-modal__desc">
						На каком языке вы хотите пользоваться сайтом?
					</p>
					<div class="lang-modal__actions">
						<button class="lang-modal__btn ru" data-lang-select data-lang="ru">
							Русский (RU)
						</button>
						<button class="lang-modal__btn uk" data-lang-select data-lang="uk">
							Українська (UA)
						</button>
					</div>
				</div>
			</div>
		</div>
