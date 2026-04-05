<?php

/**
 * Enable support for Custom Logo in Customizer (Appearance → Customize → Site Identity).
 */
function moveat_add_custom_logo_support() {
	add_theme_support(
		'custom-logo',
		[
			'height'      => 100,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
		]
	);
}
add_action( 'after_setup_theme', 'moveat_add_custom_logo_support' );

