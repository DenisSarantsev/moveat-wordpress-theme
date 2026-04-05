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
		<style>
			/* Cпиннер виден до загрузки JS */
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
				opacity: 0 !important;
				pointer-events: none;
				visibility: hidden;
			}
    </style>
    <?php wp_head(); ?>
</head>

<body>
		<div id="spinner" data-spinner class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
			<div class="spinner-border text-primary" role="status"></div>
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
					<div class="header-icons">
						<a class="header-icon" href="">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/search.png" alt="Search" class="img-fluid">
						</a>
						<a class="header-icon" href="">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/user.png" alt="User" class="img-fluid">
						</a>
						<a class="header-icon" href="/cart.html">
							<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cart.png" alt="Shopping cart" class="img-fluid">
						</a>
					</div>
				</div>
				<div class="header__menu-icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/mobile-menu.png" alt="mobile menu icon" class="header__mobile-menu-icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/cross.png" alt="cross icon" class="header__cross-icon">
				</div>
			</nav>
		</header>
