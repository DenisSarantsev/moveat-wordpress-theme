<?php
/**
 * Баннер «Подарок за прохождение теста».
 *
 * Кнопка открывает ту же модалку с мессенджерами, что показывается при входе
 * на страницу результатов (обработчик в assets/scripts/js/modules/gift-modal.js
 * слушает [data-gift-modal-open]). Поэтому баннер имеет смысл выводить только
 * там, где модалка реально выводится.
 *
 * Данные приходят из шаблона третьим аргументом get_template_part():
 *   image  — URL картинки (шаблон уже подставил стандартную, если своей нет)
 *   title  — заголовок, пустой не выводим
 *   desc   — описание, пустое не выводим
 *   button — надпись на кнопке (шаблон уже подставил запасную)
 */

$banner = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : [],
	[
		'image'  => get_template_directory_uri() . '/assets/images/illustrations/gift.png',
		'title'  => '',
		'desc'   => '',
		'button' => 'Забрать подарок',
	]
);

$banner_button = '' !== trim( (string) $banner['button'] ) ? $banner['button'] : 'Забрать подарок';
?>
<article class="banner banner-gift" data-banner="gift">
	<div class="banner-gift__wrapper banner__wrapper">
		<div class="banner-gift__content">
			<?php if ( '' !== trim( (string) $banner['image'] ) ) : ?>
				<div class="banner-gift__image-wrapper" aria-hidden="true">
					<img
						class="banner-gift__image"
						src="<?php echo esc_url( $banner['image'] ); ?>"
						alt=""
						loading="lazy" />
				</div>
			<?php endif; ?>
			<?php if ( '' !== trim( (string) $banner['title'] ) ) : ?>
				<h3 class="banner-gift__title">
					<?php echo esc_html( $banner['title'] ); ?>
				</h3>
			<?php endif; ?>
			<?php if ( '' !== trim( (string) $banner['desc'] ) ) : ?>
				<div class="banner-gift__description">
					<?php echo nl2br( esc_html( $banner['desc'] ) ); ?>
				</div>
			<?php endif; ?>
			<button
				type="button"
				class="primary-button banner-gift__cta"
				data-gift-modal-open>
				<?php echo esc_html( $banner_button ); ?>
			</button>
		</div>
	</div>
</article>
