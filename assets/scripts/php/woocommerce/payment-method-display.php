<?php
/**
 * Способ оплаты заказа — читаемое отображение в админке и в письмах.
 *
 * Зачем нужно:
 * WooCommerce печатает на экране заказа строку «Вид платежа: %s», подставляя
 * payment_method_title. Плагин monopay кладёт в этот title свой HTML с <img>,
 * а ядро прогоняет его через esc_html() — тег вылезает текстом:
 *
 *   Вид платежа: Pay with card, Apple Pay, Google Pay <img src="…/plata.svg" … /> (2609…)
 *
 * Здесь способ оплаты определяется по слагу шлюза, а не по тексту от плагина:
 * слаг стабилен и не зависит от того, что плагин напишет в title.
 *
 * Бренд банка сознательно нигде не упоминается («Банковская карта»): смена
 * эквайрера сводится к правке одной строки реестра ниже, без перерисовки
 * иконок и правки подписей по теме.
 */

namespace Moveat\Woo\PaymentMethod;

defined( 'ABSPATH' ) || exit;

/** GET-параметр фильтра в списке заказов. */
const FILTER_PARAM = 'moveat_pay_method';

/** ID колонки «Оплата» в списке заказов. */
const COLUMN_ID = 'moveat_payment_method';

/**
 * Высота иконки по месту вывода. Ширина всегда пропорциональная — задаётся
 * только предел, чтобы широкий логотип (paypal) не растянул строку.
 */
const ICON_HEIGHT = array(
	'admin'  => 20,
	'column' => 18,
);

/**
 * Реестр способов оплаты: слаг шлюза → подпись и иконка.
 *
 * Единственное место, где живут названия и картинки. Появится новый шлюз —
 * добавляется строка; сменится банк — правится только 'label'/'icon' у карты.
 *
 * Пути иконок — относительно assets/images/ (лежат в разных каталогах).
 */
function registry() {
	return array(
		// monopay (Plata by mono). Слаг подтверждён в mono-status-reconcile.php.
		'mono_gateway' => array(
			'label' => 'Банковская карта',
			'icon'  => 'icons/colored/credit-card.png',
		),
		// WooCommerce PayPal Payments.
		'ppcp-gateway' => array(
			'label' => 'PayPal',
			'icon'  => 'logotypes/paypal.png',
		),
		// Легаси PayPal Standard.
		'paypal'       => array(
			'label' => 'PayPal',
			'icon'  => 'logotypes/paypal.png',
		),
		// Псевдо-метод темы для заказов на 0 (free-order.php).
		'moveat_free'  => array(
			'label' => 'Бесплатный товар',
			'icon'  => 'icons/colored/free.png',
		),
		/*
			cod — «Оплата при доставке» из коробки WooCommerce. Доставки в проекте
			нет, все товары цифровые: этим слагом фронт лишь создаёт заказ
			(checkout-process.js), а реальный шлюз подставляется позже, в pay-order.
			То есть cod в заказе означает ровно «оплату ещё не начинали».
		*/
		'cod'          => array(
			'label' => 'Способ оплаты не выбран',
			'icon'  => 'icons/colored/question.png',
		),
	);
}

/** Запись реестра по слагу или null. */
function entry( $slug ) {
	$registry = registry();

	return isset( $registry[ $slug ] ) ? $registry[ $slug ] : null;
}

/** Заказ ли это (и не что-то другое, прилетевшее в хук). */
function is_order( $order ) {
	return $order && is_a( $order, 'WC_Order' );
}

/**
 * Человекочитаемое название способа оплаты.
 *
 * Важно: сырой title читаем в контексте 'edit' — в 'view' сработал бы
 * фильтр filter_title() ниже, и получилась бы бесконечная рекурсия.
 */
function label( $order ) {
	if ( ! is_order( $order ) ) {
		return '—';
	}

	$slug  = $order->get_payment_method();
	$entry = entry( $slug );

	if ( $entry ) {
		return $entry['label'];
	}

	// Неизвестный шлюз: показываем его собственный title, очищенный от разметки.
	$title = trim( wp_strip_all_tags( (string) $order->get_payment_method_title( 'edit' ) ) );
	if ( '' !== $title ) {
		return $title;
	}

	return $slug ? $slug : '—';
}

/** URL иконки способа оплаты или '' — если файла нет. */
function icon_url( $slug ) {
	$entry = entry( $slug );

	if ( ! $entry || empty( $entry['icon'] ) ) {
		return '';
	}

	$relative = 'assets/images/' . $entry['icon'];

	if ( ! file_exists( get_template_directory() . '/' . $relative ) ) {
		return '';
	}

	return get_template_directory_uri() . '/' . $relative;
}

/**
 * Идентификатор платежа (invoiceId mono / transaction id PayPal) или ''.
 *
 * Основной источник — штатный transaction_id заказа. Для mono переиспользуем
 * готовый хелпер: он дополнительно проверяет запасные мета-ключи.
 */
function payment_id( $order ) {
	if ( ! is_order( $order ) ) {
		return '';
	}

	if ( function_exists( '\\Moveat\\Woo\\Mono\\get_invoice_id' ) ) {
		return (string) \Moveat\Woo\Mono\get_invoice_id( $order );
	}

	return (string) $order->get_transaction_id();
}

/**
 * <img> иконки с жёстким ограничением размеров, либо '' если иконки нет.
 *
 * Исходники — PNG в несколько сотен пикселей, без предела они распирают и
 * строку заказа, и ячейку таблицы. Стили инлайном, а не классом: те же нужны
 * в письмах, где внешний CSS не живёт.
 */
function icon_html( $slug, $label, $height = 16 ) {
	$url = icon_url( $slug );

	if ( '' === $url ) {
		return '';
	}

	/*
		Ширину считаем из реальных пропорций файла и ставим атрибутом.
		CSS тут недостаточно: Outlook на Windows рендерит письма движком Word и
		max-width игнорирует — квадратная иконка 512px растянулась бы во всю
		ширину письма. Атрибуты width/height он уважает.
	*/
	$width = $height;
	$size  = @getimagesize( get_template_directory() . '/assets/images/' . entry( $slug )['icon'] );
	if ( $size && ! empty( $size[1] ) ) {
		$width = (int) round( $size[0] * $height / $size[1] );
	}

	$style = sprintf(
		'max-height:%dpx;max-width:%dpx;width:auto;height:auto;vertical-align:middle;border:0;display:inline-block;',
		$height,
		$width
	);

	return sprintf(
		'<img src="%s" alt="%s" title="%s" width="%d" height="%d" style="%s" />',
		esc_url( $url ),
		esc_attr( $label ),
		esc_attr( $label ),
		$width,
		$height,
		esc_attr( $style )
	);
}

/**
 * Готовый блок «способ оплаты» под конкретное место вывода.
 *
 * admin  — страница заказа: иконка, подпись, идентификатор платежа в скобках.
 * column — колонка списка заказов: только иконка (подпись уходит в title).
 * email  — письмо: только подпись, без картинки (см. ниже).
 */
function badge_html( $order, $context = 'admin' ) {
	if ( ! is_order( $order ) ) {
		return '';
	}

	$slug  = $order->get_payment_method();
	$label = label( $order );

	/*
		В письмах — только текст. Почтовые клиенты в массе блокируют внешние
		картинки до разрешения получателя, так что иконка там всё равно
		ненадёжна, а подпись самодостаточна.
	*/
	if ( 'email' === $context ) {
		return esc_html( $label );
	}

	$height = isset( ICON_HEIGHT[ $context ] ) ? ICON_HEIGHT[ $context ] : 16;
	$icon   = icon_html( $slug, $label, $height );

	if ( 'column' === $context ) {
		// Иконки нет — показываем текст, иначе ячейка выглядела бы пустой.
		return '' !== $icon ? $icon : '<span style="color:#787c82;">' . esc_html( $label ) . '</span>';
	}

	// admin — фрагмент без обёртки: он встраивается в абзац шапки заказа.
	// Без подписи «Способ оплаты:» — иконка с названием говорят сами за себя,
	// а с ней у cod выходило бы «Способ оплаты: Способ оплаты не выбран».
	$id   = payment_id( $order );
	$html = $icon . ' <span style="vertical-align:middle;">' . esc_html( $label ) . '</span>';

	if ( '' !== $id ) {
		// user-select:all — идентификатор копируют для сверки с эквайрингом.
		$html .= ' (id платежа: <code style="user-select:all;">' . esc_html( $id ) . '</code>)';
	}

	return $html;
}

/**
 * Ссылка на заказ в админке.
 *
 * Штатный $order->get_edit_order_url() здесь не годится: внутри он вызывает
 * get_edit_post_link(), а тот проверяет права ТЕКУЩЕГО пользователя. Письмо
 * администраторам генерируется в контексте покупателя (часто гостя), прав
 * edit_shop_order у него нет — вернулась бы пустая строка и ссылка исчезла бы.
 * Права проверит сама админка при переходе.
 */
function admin_order_url( $order ) {
	if ( ! is_order( $order ) ) {
		return '';
	}

	$id = $order->get_id();

	if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\OrderUtil' )
		&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
		return admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $id );
	}

	return admin_url( 'post.php?post=' . $id . '&action=edit' );
}

/**
 * Подменяет payment_method_title заказа при чтении.
 *
 * В БД ничего не меняется: WooCommerce применяет woocommerce_{object}_get_{prop}
 * только в контексте 'view', а save() читает свойства в 'edit'. Откат —
 * удаление этого фильтра.
 */
function filter_title( $title, $order ) {
	if ( ! is_order( $order ) ) {
		return $title;
	}

	return label( $order );
}
add_filter( 'woocommerce_order_get_payment_method_title', __NAMESPACE__ . '\\filter_title', 10, 2 );

/**
 * Подменяет название ШЛЮЗА в админке.
 *
 * Строку «Вид платежа: …» в шапке заказа ядро собирает не из заказа, а из
 * объекта шлюза (class-wc-meta-box-order-data.php):
 *
 *   esc_html( $payment_gateways[ $payment_method ]->get_title() )
 *
 * Поэтому фильтра на заказе там недостаточно — сюда и приезжает HTML monopay
 * с <img>, который ядро экранирует и печатает тегом.
 *
 * Только в админке: на фронте покупатель видит название шлюза в блоке выбора
 * оплаты, и логотип платёжной системы там уместен — его не трогаем.
 */
function filter_gateway_title( $title, $gateway_id ) {
	if ( ! is_admin() ) {
		return $title;
	}

	$entry = entry( $gateway_id );

	return $entry ? $entry['label'] : $title;
}
add_filter( 'woocommerce_gateway_title', __NAMESPACE__ . '\\filter_gateway_title', 10, 2 );

/**
 * Платёжная строка в шапке заказа — вместо стандартной.
 *
 * Стандартный абзац (класс order_number) убрать «мягко» нельзя: ядро печатает
 * в нём одной строкой способ оплаты, дату оплаты и IP покупателя, склеивая их
 * через implode() без отдельных обёрток. Поэтому прячем абзац целиком и
 * воспроизводим все три части — только теперь с иконкой.
 *
 * Хук woocommerce_admin_order_data_after_payment_info появился в WooCommerce 7.9
 * и выводится сразу под шапкой, на всю ширину. Тексты даты и IP берём с
 * текстдоменом woocommerce — перевод подхватится вместе с ядром.
 */
function order_header_block( $order ) {
	if ( ! is_order( $order ) ) {
		return;
	}

	$parts = array( badge_html( $order, 'admin' ) );

	$date_paid = $order->get_date_paid();
	if ( $date_paid ) {
		$parts[] = sprintf(
			/* translators: 1: date 2: time */
			__( 'Paid on %1$s at %2$s', 'woocommerce' ),
			wc_format_datetime( $date_paid ),
			wc_format_datetime( $date_paid, get_option( 'time_format' ) )
		);
	}

	$ip = $order->get_customer_ip_address();
	if ( $ip ) {
		$parts[] = sprintf(
			/* translators: %s: IP address */
			__( 'Customer IP: %s', 'woocommerce' ),
			'<span class="woocommerce-Order-customerIP">' . esc_html( $ip ) . '</span>'
		);
	}

	/*
		Стандартный абзац прячем отсюда, а не из admin_head: если на сайте
		окажется WooCommerce старше 7.9 и этот хук не выполнится, стиль просто
		не выведется — данные ядра останутся на месте, а не пропадут с экрана.
	*/
	echo '<style>.woocommerce-order-data__meta.order_number{display:none!important;}</style>';

	// Не через wp_kses_post: он вырезает часть инлайн-стилей (user-select),
	// а все динамические части внутри уже экранированы.
	echo '<p class="woocommerce-order-data__meta moveat-order-data__meta" style="margin:0 0 12px;">'
		. implode( '. ', $parts )
		. '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'woocommerce_admin_order_data_after_payment_info', __NAMESPACE__ . '\\order_header_block' );

/* ------------------------------------------------------------------ */
/* Колонка «Оплата» в списке заказов                                    */
/* ------------------------------------------------------------------ */

/** Вставляем колонку перед «Итого». */
function add_column( $columns ) {
	$result = array();

	foreach ( $columns as $key => $name ) {
		if ( 'order_total' === $key ) {
			$result[ COLUMN_ID ] = 'Оплата';
		}
		$result[ $key ] = $name;
	}

	// Колонки order_total не нашлось — добавляем в конец, чтобы не потерять.
	if ( ! isset( $result[ COLUMN_ID ] ) ) {
		$result[ COLUMN_ID ] = 'Оплата';
	}

	return $result;
}
add_filter( 'manage_woocommerce_page_wc-orders_columns', __NAMESPACE__ . '\\add_column' );
add_filter( 'manage_edit-shop_order_columns', __NAMESPACE__ . '\\add_column' );

/** Содержимое колонки в HPOS: сюда приходит объект заказа. */
function render_column_hpos( $column, $order ) {
	if ( COLUMN_ID !== $column ) {
		return;
	}

	echo badge_html( $order, 'column' ); // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', __NAMESPACE__ . '\\render_column_hpos', 10, 2 );

/** Содержимое колонки в legacy-таблице: сюда приходит ID записи. */
function render_column_legacy( $column, $post_id ) {
	if ( COLUMN_ID !== $column ) {
		return;
	}

	echo badge_html( wc_get_order( $post_id ), 'column' ); // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'manage_shop_order_posts_custom_column', __NAMESPACE__ . '\\render_column_legacy', 10, 2 );

/** Узкая колонка: в ней только иконка. */
function column_styles() {
	if ( ! is_admin() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array( 'woocommerce_page_wc-orders', 'edit-shop_order' ), true ) ) {
		return;
	}

	echo '<style>.wp-list-table .column-' . esc_attr( COLUMN_ID ) . '{width:60px;text-align:center;}</style>';
}
add_action( 'admin_head', __NAMESPACE__ . '\\column_styles' );

/* ------------------------------------------------------------------ */
/* Фильтр по способу оплаты                                            */
/* ------------------------------------------------------------------ */

/** Выбранный слаг из запроса — только если он есть в реестре. */
function selected_filter() {
	if ( empty( $_GET[ FILTER_PARAM ] ) ) {
		return '';
	}

	$slug = sanitize_text_field( wp_unslash( $_GET[ FILTER_PARAM ] ) );

	return isset( registry()[ $slug ] ) ? $slug : '';
}

/**
 * Варианты для селекта: слаг → подпись, по одной строке на подпись.
 *
 * У PayPal два слага — актуальный ppcp-gateway и легаси paypal. Показывать оба
 * нельзя: в списке было бы два одинаковых «PayPal», причём по одному из них не
 * находилось бы ничего. Схлопываем их в одну опцию, а фильтруем по обоим
 * слагам сразу (см. filter_slugs).
 */
function filter_options() {
	$options = array();

	foreach ( registry() as $slug => $entry ) {
		if ( in_array( $entry['label'], $options, true ) ) {
			continue;
		}

		$options[ $slug ] = $entry['label'];
	}

	return $options;
}

/** Все слаги, которые делят подпись с выбранным (ppcp-gateway + paypal). */
function filter_slugs( $slug ) {
	$entry = entry( $slug );

	if ( ! $entry ) {
		return array();
	}

	$slugs = array();

	foreach ( registry() as $key => $item ) {
		if ( $item['label'] === $entry['label'] ) {
			$slugs[] = $key;
		}
	}

	return $slugs;
}

/** Селект над таблицей заказов. */
function render_filter_select() {
	$selected = selected_filter();

	echo '<select name="' . esc_attr( FILTER_PARAM ) . '">';
	echo '<option value="">Все способы оплаты</option>';

	foreach ( filter_options() as $slug => $label ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $slug ),
			selected( $selected, $slug, false ),
			esc_html( $label )
		);
	}

	echo '</select>';
}

/** HPOS: селект в панели фильтров. */
function restrict_orders_hpos() {
	render_filter_select();
}
add_action( 'woocommerce_order_list_table_restrict_manage_orders', __NAMESPACE__ . '\\restrict_orders_hpos' );

/** Legacy: селект в панели фильтров таблицы записей. */
function restrict_orders_legacy() {
	global $typenow;

	if ( 'shop_order' !== $typenow ) {
		return;
	}

	render_filter_select();
}
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\\restrict_orders_legacy' );

/** HPOS: способ оплаты — штатное поле запроса заказов. */
function filter_query_hpos( $args ) {
	$slug = selected_filter();

	if ( '' !== $slug ) {
		// Массив ядро само разворачивает в IN (OrdersTableQuery::where).
		$slugs                  = filter_slugs( $slug );
		$args['payment_method'] = 1 === count( $slugs ) ? $slugs[0] : $slugs;
	}

	return $args;
}
add_filter( 'woocommerce_order_list_table_prepare_items_query_args', __NAMESPACE__ . '\\filter_query_hpos' );

/** Legacy: слаг лежит в мете _payment_method. */
function filter_query_legacy( $vars ) {
	global $typenow;

	if ( 'shop_order' !== $typenow ) {
		return $vars;
	}

	$slug = selected_filter();

	if ( '' !== $slug ) {
		$meta_query = isset( $vars['meta_query'] ) && is_array( $vars['meta_query'] ) ? $vars['meta_query'] : array();

		$meta_query[] = array(
			'key'     => '_payment_method',
			'value'   => filter_slugs( $slug ),
			'compare' => 'IN',
		);

		$vars['meta_query'] = $meta_query;
	}

	return $vars;
}
add_filter( 'request', __NAMESPACE__ . '\\filter_query_legacy' );

/* ------------------------------------------------------------------ */
/* Готовые фрагменты для писем администраторам                          */
/* ------------------------------------------------------------------ */

/**
 * Строка со способом оплаты под номером и датой заказа.
 * Стили подогнаны под соседнюю строку с датой в шаблонах admin-*.
 */
function email_payment_line( $order ) {
	if ( ! is_order( $order ) ) {
		return '';
	}

	$text = badge_html( $order, 'email' );

	/*
		Без иконки одна подпись под датой заказа читается непонятно, поэтому
		добавляем пояснение. Кроме случая, когда подпись уже сама им начинается
		(cod — «Способ оплаты не выбран»), иначе вышло бы удвоение.
	*/
	if ( 0 !== strpos( $text, 'Способ оплаты' ) ) {
		$text = 'Способ оплаты: ' . $text;
	}

	return '<p style="margin:5px 0;color:#666;font-size:14px;text-align:center;padding:4px 0;">'
		. $text
		. '</p>';
}

/**
 * Строка таблицы со ссылкой на заказ в админке — в подвал письма.
 * Возвращает готовый <tr>, чтобы в шаблоне была одна строка вставки.
 */
function email_admin_link_row( $order ) {
	$url = admin_order_url( $order );

	if ( '' === $url ) {
		return '';
	}

	return '<tr><td style="padding:10px 20px 0;text-align:center;">'
		. '<a href="' . esc_url( $url ) . '" '
		. 'style="display:inline-block;padding:10px 22px;background:#ff7f13;color:#ffffff;'
		. 'text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">'
		. 'Открыть заказ в админке</a></td></tr>';
}
