<?php

// ----------------------------------- Чат-бот Pulse (livechat)
// Виджет грузится в футере и асинхронно — он не нужен для первой отрисовки
// и не должен задерживать основной контент.
define( 'MOVEAT_PULSE_LIVE_CHAT_ID', '660dbb5a8d3f2dc5d50dd6e5' );

function moveat_enqueue_pulse_livechat() {
	wp_enqueue_script(
		'moveat-pulse-livechat',
		'https://cdn.pulse.is/livechat/loader.js',
		[],
		null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'moveat_enqueue_pulse_livechat' );

// Loader читает идентификатор чата из data-атрибута самого тега, поэтому
// добавляем его (и async) через фильтр — wp_enqueue_script этого не умеет.
function moveat_pulse_livechat_script_tag( $tag, $handle ) {
	if ( 'moveat-pulse-livechat' !== $handle ) {
		return $tag;
	}
	return str_replace(
		' src',
		' data-live-chat-id="' . esc_attr( MOVEAT_PULSE_LIVE_CHAT_ID ) . '" async src',
		$tag
	);
}
add_filter( 'script_loader_tag', 'moveat_pulse_livechat_script_tag', 10, 2 );
