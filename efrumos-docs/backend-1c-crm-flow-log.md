# Бэкенд: связь сайта с 1С и CRM (Platon/Progression) — лог для проверки

Дата: 2026-09-29. Справочный документ: как заказ реально проходит через
код, что уже проверено живьём и как это перепроверить самому в любой
момент — без переписки, только команды и файлы.

## 1. Схема потока

```
Оформление заказа (OrderController / FastOrderController / AppApi\OrderController)
        │
        ├──► CRM (Platon/Progression) — ВСЕГДА, для любого заказа
        │    SendOrderToAmoCrm::sendOrderToAmoCrm()
        │    app/Services/AmoOrder/SendOrderToAmoCrm.php:19
        │    Выключатель: AMOCRM_ENABLED=false в .env (локально должен
        │    быть false — иначе тестовые заказы улетают в реальный CRM)
        │
        └──► 1С — ТОЛЬКО если pay_method === 'card'
             SubmitOrderToIntegrationLayerJob::handle()
             app/Jobs/Integration/SubmitOrderToIntegrationLayerJob.php:44-65
             ├─ isServedByOneC() = false (Приднестровье) → не отправлять
             ├─ pay_method !== 'card' → не отправлять (ответ 1С 24.09,
             │  «наложенный платёж»: неоплаченные на сайте заказы — не в 1С)
             └─ иначе → OrderIntegrationService::submitOrder()
                  app/Services/Integration/OrderIntegrationService.php
                  ├─ checkStock() — по кешу (products_count), не живой запрос
                  ├─ SoapOneCOrderGateway::reserveOrder()
                  │  app/Services/Integration/OneC/SoapOneCOrderGateway.php:68
                  │  CreateClientPoint → CreateOrder → OneCOrderResult
                  │  (ObjectId=GUID, ObjectCode=номер заказа в 1С)
                  └─ integration_id_mappings: onec_document_id (GUID),
                     onec_order_number (номер), onec_status (НАШ статус
                     синка: pending/synced/failed — не путать с бизнес-
                     статусом 1С ниже)

После оплаты картой (банк подтвердил):
OrderPaymentStatusChanged → ForwardPaymentStatusToIntegrationLayer
app/Listeners/ForwardPaymentStatusToIntegrationLayer.php
├─ to !== Paid → не отправлять
├─ pay_method !== 'card' → не отправлять
├─ !isServedByOneC() → не отправлять
└─ OneCOrderIntegrationNotifier::notifyPaymentStatusChanged()
     → SoapOneCOrderGateway::markPaid() → CreatePayment

Статус из 1С обратно (когда 1С начнёт слать):
POST /webhooks/onec/order-status
app/Http/Controllers/Integration/OneCOrderStatusWebhookController.php
├─ токен из тела (token) или ?token=/X-OneC-Webhook-Token
├─ ищет заказ по xml_id = integration_id_mappings.onec_order_number
└─ пишет orders.onec_order_status + onec_order_status_synced_at
   (бизнес-статус 1С: Ожидание/НовыйОплачен/Подтверждена/Расфасована/
   ВПути/Доставлен/СделкаЗавершена/Аннулирована/…)
```

## 2. Кто что получает — таблица на все случаи

| Способ оплаты | CRM (Platon/Progression) | 1С (`CreateOrder`) | 1С (`CreatePayment`) |
|---|---|---|---|
| Карта | ✅ всегда | ✅ | ✅ после оплаты |
| Наличные (при получении) | ✅ всегда | ❌ никогда | ❌ никогда |
| Любой способ, район ПМР | ✅ всегда | ❌ никогда | ❌ никогда |

## 3. Что уже проверено живьём (с датами)

- **24.09** — гейт `pay_method==='card'`: реальный заказ #2946 (cash) →
  пропущен, лог `«оплата при получении, только amoCRM»`, 0 строк в
  `integration_id_mappings`. Реальный заказ #2944 (временно `card` для
  теста) → дошёл до `reserveOrder()`, создана строка `onec_status=synced`.
  Откатано после теста.
- **25.09** — `ObjectCode`/`xml_id`: вебхук по точному контракту 1С
  (`{"xml_id":"104943","status":"ВПути","token":"..."}`) — верный токен и
  известный `xml_id` → `{"status":"ok"}`, заказ находится, статус
  сохраняется; неверный токен/неизвестный `xml_id` → `{"status":"ignored"}`,
  без изменений в БД.
- **25.09** — `GetSKUArray` с GUID товара работает на `ws_ef.1cws`
  (`SiteType=ES` и `EF`), 0,3–0,6 с, отдаёт остатки по 5 складам. В
  `checkStock()` пока не подключён — используется кеш обмена каталога.
- **28–29.09** — API приложения (`/api/app/v1/orders`): создание заказа
  из приложения помечает `orders.source='app'`; в CRM уходит с тегом
  `app efrumos`; наличные не уходят в 1С по тому же гейту, что и заказы
  сайта.

## 4. Что НЕ проверено / не сделано

- **Реального трафика от 1С на вебхук статуса ещё не было** — только наши
  тестовые запросы. Пока 1С не подтвердит токен на своей стороне,
  `orders.onec_order_status` не обновится ни у одного настоящего заказа.
- **`AdditionalInfo` (сумма с доставкой от 1С)** — 1С обещала к 28.09, не
  прислала на 29.09. Пока сумму к оплате считает сайт.
- **Очередь `sync` на dev/prod, воркера нет.** `SubmitOrderToIntegrationLayerJob`
  выполняется прямо в запросе оформления заказа — если 1С недоступна,
  ошибка уходит в ответ покупателю на уже созданный заказ (заказ есть, в
  1С не попал). Проверено имитацией недоступного 1С — см.
  `acceptance-discrepancies-for-sk.md`, п.3.
- **Ежедневная проверка рассинхронизации** — `integration:check-desync`
  (см. `app/Console/Commands/CheckIntegrationDesync.php`) ищет заказы с
  `onec_status=failed` или зависшие `pending`. Настроена в
  `Kernel.php` на `daily()`, но зависит от `cron` на сервере — на dev cron
  есть (проверено 24.09), на prod не перепроверялся.

## 5. Как перепроверить самому в любой момент

**Живой тест гейта (не трогает реальные заказы):**
```
docker exec -w /usr/src/myapp <container> php artisan tinker --execute='
$service = app(App\Services\Integration\OrderIntegrationService::class);
$job = new App\Jobs\Integration\SubmitOrderToIntegrationLayerJob(<id заказа с cash>);
$job->handle($service);
echo DB::table("integration_id_mappings")->where("orders_id", <id>)->count();
' # ожидается 0
```

**Проверить, что реальный вебхук от 1С хоть раз пришёл:**
```
grep "OneCOrderStatusWebhookController: статус сохранён" storage/logs/laravel.log
```
Пусто — 1С ещё не начала слать (см. п.4). Есть записи — сверить
`orders_id`/`onec_order_status` с ожиданием.

**Проверить рассинхронизацию (заказы, застрявшие между сайтом и 1С):**
```
php artisan integration:check-desync
```
`No desync found.` — всё в порядке. Список заказов — см. `last_error` в
`integration_id_mappings` для причины.

**Проверить, что заказ реально дошёл до CRM:** в `SendOrderToAmoCrm` нет
журнала успеха/неудачи на стороне сайта (см. `app/Services/AmoOrder/
SendOrderToAmoCrm.php:145` — ответ CRM не проверяется и не логируется).
**Это слепое пятно** — если Platon/Progression отклонит заказ, сайт этого
не узнает и не залогирует. Единственный способ проверить сегодня — увидеть
заказ в самой CRM. Стоит доработать (добавить логирование ответа), не
делал без отдельного запроса.

## 6. Связанные документы

`1c-open-items.md` (все ответы 1С), `1c-order-status-webhook-spec.md`
(контракт вебхука), `app-api-contract.md` (API приложения),
`acceptance-discrepancies-for-sk.md` (расхождения с приёмкой),
`demo-readiness-checklist-2026-09-29.md` (чек-лист к показу).
