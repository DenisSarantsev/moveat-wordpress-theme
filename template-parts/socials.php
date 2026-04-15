<?php
/*
 	Template part: socials
 	Вывод блока с иконками на социальные сети
*/
defined( 'ABSPATH' ) || exit;

// Вспомогательная функция: пытается получить поле из глобальной страницы (GLOBAL_SETTINGS_PAGE_ID), затем ACF options, затем ACF, затем get_option
if ( ! function_exists( 'moveat_get_social_field' ) ) {
	function moveat_get_social_field( $key ) {
		// Попытка: глобальная страница настроек
		if ( defined( 'GLOBAL_SETTINGS_PAGE_ID' ) && function_exists( 'get_field' ) ) {
			$val = get_field( $key, GLOBAL_SETTINGS_PAGE_ID );
			if ( ! empty( $val ) ) {
				return $val;
			}
		}
		// ACF options
		if ( function_exists( 'get_field' ) ) {
			$val = get_field( $key, 'option' );
			if ( ! empty( $val ) ) {
				return $val;
			}
			$val = get_field( $key );
			if ( ! empty( $val ) ) {
				return $val;
			}
		}
		// Стандартный option
		$opt = get_option( $key );
		return $opt !== false ? $opt : null;
	}
}

// Вывод соцсетей: пытаемся прочитать до 5 соцсетей
$printed = 0;
for ( $i = 1; $i <= 5; $i++ ) {
	$prefix = 'social_module_' . $i;
	$label  = moveat_get_social_field( $prefix . '_label' );
	$url    = moveat_get_social_field( $prefix . '_url' );
	$icon   = moveat_get_social_field( $prefix . '_icon' );
	$bg     = moveat_get_social_field( $prefix . '_bg_color' );
	$style  = moveat_get_social_field( $prefix . '_style' );

	// Нормализуем иконку: ACF может вернуть URL, массив с ['url'] или ID
	$icon_url = '';
	if ( is_array( $icon ) && ! empty( $icon['url'] ) ) {
		$icon_url = $icon['url'];
	} elseif ( is_numeric( $icon ) ) {
		$icon_url = wp_get_attachment_image_url( intval( $icon ), 'thumbnail' );
	} else {
		$icon_url = $icon;
	}

	// Нормализация URL: считаем некоторые заглушки пустыми
	$url = is_string( $url ) ? trim( $url ) : $url;
	if ( $url === '#' || $url === '' || $url === 'javascript:void(0)' ) {
		$url = '';
	}

	// Показываем элемент только если есть и URL, и иконка
	if ( empty( $url ) || empty( $icon_url ) ) {
		continue;
	}

	$slug = $label ? sanitize_title( $label ) : 'social-' . $i;
	$link_style = '';
	if ( ! empty( $bg ) ) {
		$link_style = 'background-color: ' . esc_attr( $bg ) . ';';
	}

	?>
	<a href="<?php echo esc_url( $url ); ?>" class="cart-page__messenger-link cart-page__messenger-link--<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( $label ?: $slug ); ?>"<?php echo $link_style ? ' style="' . esc_attr( $link_style ) . '"' : ''; ?>>
		<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $label ?: $slug ); ?>">
	</a>
	<?php
	$printed++;
}
?>
