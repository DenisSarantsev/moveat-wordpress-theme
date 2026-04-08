<?php
/*
	Template Name: Техническое обслуживание
*/
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
				<div class="tech-page__badge">Технические работы</div>

				<h1 class="tech-page__title">Друзья, наш сайт ушел на небольшое обновление! 🛠</h1>

				<div class="tech-page__content">
					<p>
						Мы решили провести плановую «генеральную уборку»: обновляем интерфейс и оптимизируем работу
						разделов, чтобы вам было еще удобнее находить нужную информацию и оформлять заказы.
					</p>

					<h2>Что это значит для вас?</h2>
					<p>Сайт будет временно недоступен в течение 1-2 дней.</p>

					<p>
						Мы на связи! Все вопросы по текущим заказам и консультациям оперативно решаем в мессенджерах.
					</p>

					<p>Пишите нам в удобный для вас канал:</p>
				</div>

				<div class="tech-page__links" aria-label="Ссылки на мессенджеры и социальные сети">
					<a class="tech-page__link tech-page__link--telegram" href="https://t.me/MaxPogorelyBot?start=657b3f6ba17c770cfe08738c" target="_blank" rel="noopener noreferrer">
						<span class="tech-page__link-name">Телеграм бот</span>
					</a>
					<a class="tech-page__link tech-page__link--whatsapp" href="https://380991900483.wa.pulse.is/" target="_blank" rel="noopener noreferrer">
						<span class="tech-page__link-name">Вотсап бот</span>
					</a>
					<a class="tech-page__link tech-page__link--viber" href="viber://pa?chatURI=max_pogorely&context=657b4bccc151da5d54055ea2">
						<span class="tech-page__link-name">Вайбер бот</span>
					</a>
					<a class="tech-page__link tech-page__link--instagram" href="https://www.instagram.com/max_pogorely/" target="_blank" rel="noopener noreferrer">
						<span class="tech-page__link-name">Инстаграм (Direct)</span>
					</a>
				</div>

				<p class="tech-page__footer-text">Скоро вернемся с обновленным и быстрым сайтом!</p>
			</div>
		</section>
	</main>
	<?php wp_footer(); ?>
</body>

</html>
