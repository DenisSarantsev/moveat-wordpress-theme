    <!-- Footer Module -->
		<div class="footer">
			<div class="footer__container">
				<div class="footer-top">
					<div class="footer-top__logo">
						<img src="img/logo.png" alt="Logo">
					</div>
					<div class="footer-top__links">
						<div class="footer-top__links-block">
							<a class="footer-top__link" href="#">Оценка качества питания</a>
							<a class="footer-top__link" href="#">Расчет рациона</a>
							<a class="footer-top__link" href="#">Клуб Макса Погорелого</a>
							<a class="footer-top__link" href="#">Полезные материалы</a>
							<a class="footer-top__link" href="#">Видео с Youtube канала</a>
						</div>
						<div class="footer-top__links-block">
							<a class="footer-top__link" href="#">Про нас</a>
							<a class="footer-top__link" href="#">Сотрудничество</a>
							<a class="footer-top__link" href="#">Контакты</a>
							<a class="footer-top__link" href="#">Вопросы и Ответы</a>
							<a class="footer-top__link" href="#">Техническая поддержка</a>
						</div>
						<div class="footer-top__links-block">
							<a class="footer-top__link" href="#">Публичный договор (оферта)</a>
							<a class="footer-top__link" href="#">Политика конфиденциальности</a>
							<a class="footer-top__link" href="#">Ответственность сторон</a>
							<a class="footer-top__link" href="#">Юридический адрес</a>
						</div>
					</div>
					<div class="footer-top__other-links">
						<div class="footer-top__other-links_socials">
							<h5 class="footer-top__other-links_socials-title">Мы в соцсетях</h5>
							<div class="footer-top__other-links_socials-icons">
								<a href="#">
									<img src="img/icons/facebook.png" alt="Facebook">
								</a>
								<a href="#">
									<img src="img/icons/instagram.png" alt="Instagram">
								</a>
								<a href="#">
									<img src="img/icons/telegram.png" alt="Telegram">
								</a>
								<a href="#">
									<img src="img/icons/youtube.png" alt="Youtube">
								</a>
							</div>
						</div>
						<div class="footer-top__other-links_payments">
							<h5 class="footer-top__other-links_payments-title">Мы принимаем</h5>
							<div class="footer-top__other-links_payments-icons">
								<a href="#">
									<img src="img/logotypes/visa.png" alt="Visa">
								</a>
								<a href="#">
									<img src="img/logotypes/paypal.png" alt="Paypal">
								</a>
								<a href="#">
									<img src="img/logotypes/mastercard.png" alt="Mastercard">
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="footer-bottom">
					<p class="footer-bottom__text">
						Copyright © 2018-2026. All Rights Reserved.
					</p>
				</div>
			</div>
		</div>
		<button class="back-to-top-button">
			<img src="img/icons/arrow.png" alt="Arrow up">
		</button>


    <!-- JavaScript Libraries -->
    <!-- Подключаем owl.carousel через CDN для гарантированной работы -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    
    <!-- Подключаем owl.carousel после модулей, чтобы jQuery был уже загружен -->
    <script>
        // Ждем загрузки jQuery из модуля
        (function() {
            function loadOwlCarousel() {
                if (typeof window.$ !== 'undefined' && typeof window.$.fn !== 'undefined') {
                    if (typeof window.$.fn.owlCarousel === 'undefined') {
                        var script = document.createElement('script');
                        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js';
                        script.onload = function() {
                            console.log('owl.carousel загружен');
                        };
                        document.body.appendChild(script);
                    }
                } else {
                    setTimeout(loadOwlCarousel, 50);
                }
            }
            loadOwlCarousel();
        })();
    </script>
</body>

</html>