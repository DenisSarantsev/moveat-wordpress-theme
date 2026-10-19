<?php
defined( 'ABSPATH' ) || exit;

/**
 * Returns a map of format keys to icon URL and label.
 */
function moveat_wc_get_formats_map() {
	$theme_uri = get_template_directory_uri();
	// Подписи — из словаря страницы товара (assets/dictionaries/templates/product).
	$dict = moveat_dictionary( 'templates/product' );
	$base_icons = $theme_uri . '/assets/icons/formats';

	// Fallback to existing colored icons if formats dir is missing
	$fallback_base = $theme_uri . '/assets/images/icons/colored';

	return [
		'pdf'   => [
			'icon'  => file_exists( get_template_directory() . '/assets/icons/formats/pdf.svg' ) ? ( $base_icons . '/pdf.svg' ) : ( $fallback_base . '/pdf.png' ),
			'label' => moveat_dictionary_text( $dict, 'format_pdf' ),
		],
		'audio' => [
			'icon'  => file_exists( get_template_directory() . '/assets/icons/formats/audio.svg' ) ? ( $base_icons . '/audio.svg' ) : ( $fallback_base . '/audio.png' ),
			'label' => moveat_dictionary_text( $dict, 'format_audio' ),
		],
		'video' => [
			'icon'  => $base_icons . '/video.svg',
			'label' => moveat_dictionary_text( $dict, 'format_video' ),
		],
		'zip'   => [
			'icon'  => $fallback_base . '/zip.png',
			'label' => moveat_dictionary_text( $dict, 'format_zip' ),
		],
		'image' => [
			'icon'  => $base_icons . '/image.svg',
			'label' => moveat_dictionary_text( $dict, 'format_image' ),
		],
		'text'  => [
			'icon'  => $base_icons . '/txt.svg',
			'label' => moveat_dictionary_text( $dict, 'format_text' ),
		],
	];
}

