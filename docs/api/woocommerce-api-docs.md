# WooCommerce API — документация

## Общая информация

Этот API-слой в теме работает как фасад над WooCommerce endpoint-ами и частично над кастомными endpoint-ами проекта.

- Точка входа слоя: `assets/scripts/js/api/woocommerce/index.js`
- Базовый Store API: `/wp-json/wc/store/v1`
- Кастомные endpoint-ы заказа: `/wp-json/moveat/v1/*`
- HTTP-клиент: `http-client.js` (добавляет `X-WP-Nonce`, отправляет JSON, нормализует ошибки в `WooApiError`)

Общий поток:
1. UI (страницы товара/корзины/checkout) вызывает методы API-слоя.
2. API-слой отправляет запрос в WooCommerce Store API или в кастомный endpoint.
3. Ответ корзины нормализуется (`normalizeCart`) к единой структуре `items/totals/count/coupons`.
4. UI работает уже с единым форматом и не зависит от деталей конкретного endpoint.

---

## Endpoint-ы корзины

### `GET /wp-json/wc/store/v1/cart`
- Что делает: возвращает текущее состояние корзины.
- Используется в: `cartApi.getCart()`.

### `POST /wp-json/wc/store/v1/cart/add-item`
- Что делает: добавляет товар в корзину.
- Тело: `{ id, quantity }`.
- Используется в: `cartApi.addItem(productId, quantity)`.

### `POST /wp-json/wc/store/v1/cart/remove-item`
- Что делает: удаляет позицию из корзины.
- Тело: `{ key }` (ключ позиции в корзине).
- Используется в: `cartApi.removeItem(cartItemKey)`.

### `POST /wp-json/wc/store/v1/cart/update-item`
- Что делает: изменяет количество позиции в корзине.
- Тело: `{ key, quantity }`.
- Используется в: `cartApi.setQuantity(cartItemKey, quantity)`.

---

## Endpoint-ы промокодов

### `POST /wp-json/wc/store/v1/cart/apply-coupon`
- Что делает: применяет промокод к текущей корзине.
- Тело: `{ code }`.
- Используется в: `couponsApi.applyCoupon(code)`.

### `POST /wp-json/wc/store/v1/cart/remove-coupon`
- Что делает: удаляет промокод из корзины.
- Тело: `{ code }`.
- Используется в: `couponsApi.removeCoupon(code)`.

---

## Endpoint-ы checkout

### `GET /wp-json/wc/store/v1/checkout`
- Что делает: возвращает текущее состояние checkout.
- Используется в: `checkoutApi.getCheckout()`.

### `POST /wp-json/wc/store/v1/checkout`
- Что делает: обновляет данные оформления заказа (покупатель, доставка, способ оплаты).
- Тело: зависит от шага (customer/shipping/payment).
- Используется в:
  - `checkoutApi.setCustomer(payload)`
  - `checkoutApi.setShipping(payload)`
  - `checkoutApi.setPaymentMethod(paymentMethod, paymentData)`

---

## Endpoint-ы заказа (кастомные проекта)

### `POST /wp-json/moveat/v1/orders`
- Что делает: создает заказ на сервере.
- Используется в: `orderApi.createOrder(payload)`.

### `POST /wp-json/moveat/v1/orders/pay`
- Что делает: запускает оплату созданного заказа.
- Используется в: `orderApi.pay(payload)`.

---

## One-click сценарий

Метод `oneClickApi.buyNow(productId, quantity)`:
1. Вызывает добавление в корзину через `POST /wp-json/wc/store/v1/cart/add-item`.
2. После успешного ответа делает редирект на страницу checkout (`/checkout/` или `config.checkoutUrl`).

Кратко: one-click в текущем слое = **добавление в корзину + переход к оформлению**.

---

## Обработка ошибок

- Все HTTP-ошибки проходят через `WooApiError`.
- В ошибке доступны:
  - `message` — текст ошибки;
  - `status` — HTTP-код;
  - `payload` — ответ сервера (если есть).

Это позволяет одинаково обрабатывать ошибки во всех UI-модулях.
