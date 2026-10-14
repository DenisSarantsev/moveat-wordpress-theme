<?php

// ----------------------------------- Чат-бот Pulse (livechat)
// Виджет тяжёлый: на телефоне его bundle.js около 2 секунд занимает основной
// поток, и пока он выполняется, браузер не может дорисовать первый экран
// (Lighthouse: LCP, Total Blocking Time). Даже async это не спасало — скрипт
// всё равно выполнялся во время загрузки страницы.
//
// Поэтому loader подключаем отложенно: при первом действии посетителя
// (скролл, касание, мышь, клавиатура) или через 5 секунд после загрузки
// страницы — что наступит раньше.
define( 'MOVEAT_PULSE_LIVE_CHAT_ID', '660dbb5a8d3f2dc5d50dd6e5' );
define( 'MOVEAT_PULSE_LIVE_CHAT_DELAY_MS', 5000 );

function moveat_pulse_livechat_deferred_loader() {
	?>
	<script>
	(function () {
		var loaded = false;
		var events = [ 'scroll', 'pointerdown', 'mousemove', 'touchstart', 'keydown' ];

		function load() {
			if ( loaded ) return;
			loaded = true;
			events.forEach( function ( name ) {
				window.removeEventListener( name, load, { passive: true } );
			} );

			// Loader читает идентификатор чата из data-атрибута своего тега.
			var script = document.createElement( 'script' );
			script.src = 'https://cdn.pulse.is/livechat/loader.js';
			script.async = true;
			script.setAttribute( 'data-live-chat-id', <?php echo wp_json_encode( MOVEAT_PULSE_LIVE_CHAT_ID ); ?> );
			document.body.appendChild( script );
		}

		events.forEach( function ( name ) {
			window.addEventListener( name, load, { passive: true } );
		} );

		function startTimer() {
			setTimeout( load, <?php echo (int) MOVEAT_PULSE_LIVE_CHAT_DELAY_MS; ?> );
		}
		if ( document.readyState === 'complete' ) {
			startTimer();
		} else {
			window.addEventListener( 'load', startTimer, { once: true } );
		}
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'moveat_pulse_livechat_deferred_loader', 100 );
