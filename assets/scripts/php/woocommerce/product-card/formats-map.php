<?php
defined( 'ABSPATH' ) || exit;

/**
 * Returns a map of format keys to icon URL and label.
 */
function moveat_wc_get_formats_map() {
	$theme_uri = get_template_directory_uri();
	$base_icons = $theme_uri . '/assets/icons/formats';

	// Fallback to existing colored icons if formats dir is missing
	$fallback_base = $theme_uri . '/assets/images/icons/colored';

	return [
		'pdf'   => [
			'icon'  => file_exists( get_template_directory() . '/assets/icons/formats/pdf.svg' ) ? ( $base_icons . '/pdf.svg' ) : ( $fallback_base . '/pdf.png' ),
			'label' => __( 'PDF файл', 'moveat' ),
		],
		'audio' => [
			'icon'  => file_exists( get_template_directory() . '/assets/icons/formats/audio.svg' ) ? ( $base_icons . '/audio.svg' ) : ( $fallback_base . '/audio.png' ),
			'label' => __( 'Аудио файл', 'moveat' ),
		],
		'video' => [
			'icon'  => $base_icons . '/video.svg',
			'label' => __( 'Видео файл', 'moveat' ),
		],
		'zip'   => [
			'icon'  => $base_icons . '/zip.svg',
			'label' => __( 'Архив ZIP', 'moveat' ),
		],
		'image' => [
			'icon'  => $base_icons . '/image.svg',
			'label' => __( 'Изображения', 'moveat' ),
		],
		'text'  => [
			'icon'  => $base_icons . '/txt.svg',
			'label' => __( 'Текстовый файл', 'moveat' ),
		],
	];
}

