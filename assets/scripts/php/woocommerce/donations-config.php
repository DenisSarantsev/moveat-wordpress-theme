<?php

defined( 'ABSPATH' ) || exit;

/*
	Минимальная сумма доната (USD) — единственное место настройки.
*/
function moveat_get_min_donation_amount(): float {
	return 1.0;
}

/*
	Форматирует сумму для вывода в шаблоне (без лишних нулей: 5, 0.2, 0.25).
*/
function moveat_format_donation_amount( float $amount ): string {
	$formatted = number_format( $amount, 2, '.', '' );

	return rtrim( rtrim( $formatted, '0' ), '.' );
}
