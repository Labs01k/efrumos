# API efrumos для бэкенда приложения (eFrumos-backend) — контракт v1

Дата: 2026-09-25, обновлено 2026-10-01. Реализовано в `efrumos` (ветка
`stage`), проверено локально живыми запросами. Контекст —
`app-integration-brief-analysis.md`.

**Токен для dev** (прописан в `.env` на `dev.efrumos.md` 2026-10-01, живой
запрос подтверждён; реальное значение в репозиторий не кладём — см. `.env`
на сервере):
```
APP_API_TOKEN=<реальное значение только в .env на сервере>
```
Для прода — отдельный токен, сгенерировать при переносе на прод (сейчас не
переносим, см. блокер архитектуры). Значение знает только `eFrumos-backend` —
см. предупреждение ниже про путь вызовов.

Принцип из брифа: сайт — единственный источник каталога, цен, остатков и
заказов; бэкенд приложения — тонкий клиент. Пользователь приложения
проходит OTP-вход в `eFrumos-backend`; сайт эту личность не проверяет, а
доверяет заголовку от авторизованного сервиса.

**⚠️ Это API только сервис-сервис, не для мобильного приложения напрямую.**
`APP_API_TOKEN` — один общий секрет на весь `eFrumos-backend`, не привязан к
конкретному пользователю. `X-App-Client-Id` сайт принимает на веру, не
проверяя, что запрос действительно от этого клиента, — подлинность
пользователя (OTP, сессия) проверяет `eFrumos-backend`, и только он подставляет
этот заголовок в запрос к сайту. **Токен не должен попасть в код или
конфиг мобильного приложения** (бинарник, обфускация — не защита): его
может знать только сервер `eFrumos-backend`. Утечка токена = возможность
читать/создавать заказы от имени любого `client_id`. Путь вызовов:
приложение → `eFrumos-backend` (свой Sanctum/OTP) → efrumos (этот API).

## Общее

Базовый URL: `https://<домен сайта>/api/app/v1`

| Заголовок | Обязателен | Что |
|---|---|---|
| `Authorization: Bearer <APP_API_TOKEN>` | всегда | Токен сервиса. Не задан на сайте — API отвечает `503`; неверный — `401` |
| `X-App-Lang: ro\|ru\|en` | нет | Язык названий, описаний и сообщений валидации. По умолчанию — язык сайта (ro) |
| `X-App-Client-Id: <client_id>` | для заказов | `client_id` лояльности (GUID из `User.client_id` в eFrumos-backend), до 64 символов |
| `Idempotency-Key: <строка ≤64>` | при создании заказа | Повтор запроса с тем же ключом возвращает уже созданный заказ |

Ответ всегда JSON: `{"success": true, "data": …}` или
`{"success": false, "message": "…"}` / `{"success": false, "errors": {поле: […]}}`
(валидация, `422`). Списки: `meta = {page, per_page, total, last_page}`.
Лимит — 120 запросов в минуту. Деньги — целые, валюта `MDL`.

## Каталог (только чтение)

Только активные, не удалённые товары с ценой и названием на выбранном языке.

- `GET /catalog/categories` — дерево категорий `[{id, alias, name, children[]}]`.
- `GET /catalog/products` — список. Параметры: `category` (id или alias,
  включая вложенные), `q` (название, артикул, штрихкод; ≥2 символов),
  `in_stock=1`, `on_sale=1`, `sort=popular|new|price_asc|price_desc`,
  `page`, `per_page` (≤50, по умолчанию 20). Категория не найдена — `404`.
- `GET /catalog/products/{id|alias}` — карточка: то же, что в списке, плюс
  `articul`, `barcode`, `category`, `short_description`, `description` (HTML из
  CMS), `images[{big, thumb}]`, `stock {available_qty, by_shops[]}`,
  `shades[]` (палитра оттенков линейки).

Карточка в списке:
`{id, code (код 1С), alias, name, brand, price{current, old, discount_percent, currency}, in_stock, image, is_popular, is_new, url}`.
`price.old` — обычная цена, если есть промо-цена (`current` — уже промо).
`stock.available_qty` — остаток основного склада, по нему 1С проверяет заказ.
`stock.by_shops` — наличие по магазинам (пусто, пока магазины не привязаны
к складам 1С).

## Заказы

### `POST /orders/quote` — расчёт без создания

Тело: `{delivery_method: "pickup"|"delivery", district_id?, items: [{product_id, qty}]}`.
Ответ: `lines[{product_id, name, image, qty, unit_price, line_total}]`,
`problems[{product_id, reason: not_found|out_of_stock|insufficient_stock, available?}]`,
`totals{items_total, discount, delivery, total_due, currency}`, `orderable`.
Одинаковые `product_id` в запросе складываются. Строки с проблемами в
`lines` не попадают.

### `POST /orders` — создание

Заголовки: `X-App-Client-Id`, `Idempotency-Key`. Тело:

```json
{
  "delivery_method": "delivery",
  "pay_method": "cash",
  "items": [{"product_id": 11031, "qty": 2}],
  "customer": {"name": "Ion", "last_name": "Popescu", "phone": "37360000000", "email": null},
  "delivery": {"district_id": 2988, "city": "Chisinau", "address": "str. Test 1"},
  "pickup_shop_id": null,
  "comment": null
}
```

`delivery` обязателен при `delivery_method=delivery`, `pickup_shop_id` — при
`pickup` (только магазины самовывоза). `pay_method`: `cash` (оплата при
получении) всегда; `card` — только когда на сайте включена онлайн-оплата
(`ONLINE_PAYMENT_ENABLED`), иначе `422`.

Ответ `201` (`200` и `"replay": true` при повторе с тем же ключом) —
карточка заказа (см. ниже) + `payment {method, url}`. Для `card` `url` — страница
оплаты в банке: открыть в браузере/WebView, результат приложение узнаёт по
`payment_status` заказа (решает серверный callback банка, не возврат
покупателя). Если хоть одна позиция не может быть заказана — `422` с
`data.problems`. Тот же ключ от другого клиента — `409`.

Заказ ведёт себя как заказ сайта: попадает в CMS, менеджерам
(Platon/Progression, с тегом приложения) и — для карты — в 1С. Заказы с
оплатой при получении в 1С не отправляются (ответ 1С 24.09).

### `GET /orders` и `GET /orders/{id}`

Только заказы клиента из `X-App-Client-Id` (чужой или несуществующий — `404`).
Список: `page`, `per_page` (≤50), новые первыми.

Заказ: `{id, created_at, delivery_method, pay_method, payment_status
(pending|paid|failed|cancelled), onec_status, onec_status_at,
totals{…}}`; в карточке дополнительно `items[]`, `customer`, `delivery`,
`comment`. `onec_status` — статус из 1С как пришёл вебхуком (значения
`OrderStatuses`: `Ожидание`, `НовыйОплачен`, `НовыйОжиданиеОплаты`,
`Подтверждена`, `Расфасована`, `ВПути`, `Доставлен`, `СделкаЗавершена`,
`Аннулирована`, `Оплачен`, `ЧастичноОплачен`, `ОжиданиеОплаты`); `null`, пока
1С не прислала или заказ в 1С не передавался.

## События: сайт → eFrumos-backend

При смене статуса заказа, созданного из приложения, сайт делает
`POST {APP_API_STATUS_WEBHOOK_URL}` с `Authorization: Bearer
{APP_API_STATUS_WEBHOOK_TOKEN}` (по образцу ваших вебхуков 1С):

```json
{
  "event_type": "order_status_changed",
  "event_id": "order-2952-onec-20260925225620750",
  "occurred_at": "2026-09-25T22:56:20+03:00",
  "Data": [{
    "order_id": 2952,
    "client_id": "<X-App-Client-Id заказа>",
    "changed": "onec",
    "payment_status": "paid",
    "onec_status": "Расфасована"
  }]
}
```

`changed` — `payment` (смена статуса оплаты) или `onec` (статус пришёл из 1С).
Статусы в `Data` — актуальные на момент отправки. `event_id` стабилен между
повторами одной доставки — используйте для идемпотентности. Отправка идёт
через очередь: 5 попыток (30 с, 2 мин, 10 мин, 30 мин), любой ответ не-2xx
считается неудачей. Адрес не задан — уведомления не отправляются.
Соответствие статусов 1С и push-сообщений — `app-integration-brief-analysis.md`, п. 3.4.

## Что НЕ реализовано в v1

- **Промокоды, скидки по акциям, подарки** — на сайте их считает корзина при
  добавлении товара; в API `discount` всегда 0.
- **Корзина на сервере** — состав корзины хранит приложение, на сайт
  отправляется `quote`/заказ целиком.
- **Бонусы при оформлении** — в `CreateOrder` 1С нет поля для бонусов; вопрос
  открыт (бриф, вопрос 1).
- **Профиль сайта** — заказ не привязан к пользователю сайта (`front_user`),
  только к `client_id` приложения; объединение — этап 6 брифа.
- **Письма покупателю/менеджерам** — не отправляются (менеджеры получают заказ
  через Platon/Progression).
- **Сумма и доставка от 1С** — стоимость доставки сейчас по тарифу сайта
  (50/100 MDL, бесплатно от 600). После получения `AdditionalInfo` от 1С
  (`{"orderSum","deliverySum","total"}`, ожидается 28.09) итог заменится
  суммой 1С; `deliverySum: null` — доставку платит клиент курьеру.

## Настройка окружения

`.env` сайта (деплой `.env` не синкает — на каждом окружении вручную):

```
APP_API_TOKEN=<длинный случайный токен, отдаётся eFrumos-backend>
APP_API_STATUS_WEBHOOK_URL=https://<eFrumos-backend>/api/webhooks/site/order-status
APP_API_STATUS_WEBHOOK_TOKEN=<токен, которым сайт подписывается у eFrumos-backend>
```

Миграция `2026_09_25_120000_add_app_columns_to_orders_table` добавляет в
`orders`: `source` (`site`/`app`), `app_client_id`, `app_request_id`
(уникальный, для идемпотентности).

## Что нужно на стороне eFrumos-backend

1. Клиент к этому API (Http + Bearer, заголовки `X-App-Client-Id`, `X-App-Lang`).
2. Приёмник `order_status_changed` (Bearer, `event_id` в `processed_webhooks`,
   обработка в очереди `webhooks`) и push по соответствию статусов.
3. Решения по бонусам и профилю (см. «Не реализовано»).
