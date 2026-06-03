<?php

// ----------------------------------- Google AdSense (глобальный скрипт в head)
function moveat_enqueue_google_adsense() {
	wp_enqueue_script(
		'moveat-google-adsense',
		'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1367492349870959',
		[],
		null,
		false
	);
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_google_adsense' );

function moveat_google_adsense_script_tag( $tag, $handle ) {
	if ( 'moveat-google-adsense' !== $handle ) {
		return $tag;
	}
	return str_replace( ' src', ' async crossorigin="anonymous" src', $tag );
}
add_filter( 'script_loader_tag', 'moveat_google_adsense_script_tag', 10, 2 );

// ----------------------------------- Google Analytics 4 (gtag.js)
function moveat_enqueue_google_analytics() {
	$measurement_id = 'G-50GT6697R2';
	wp_enqueue_script(
		'moveat-google-analytics',
		'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $measurement_id ),
		[],
		null,
		false
	);
	$inline = sprintf(
		"window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '%s');",
		esc_js( $measurement_id )
	);
	wp_add_inline_script( 'moveat-google-analytics', $inline, 'after' );
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_google_analytics' );

function moveat_google_analytics_script_tag( $tag, $handle ) {
	if ( 'moveat-google-analytics' !== $handle ) {
		return $tag;
	}
	return str_replace( ' src', ' async src', $tag );
}
add_filter( 'script_loader_tag', 'moveat_google_analytics_script_tag', 10, 2 );
