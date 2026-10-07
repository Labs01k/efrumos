<?php

return [

    'amocrm' => [
        // App\Services\AmoOrder\SendOrderToAmoCrm — misleadingly named, it
        // actually POSTs to platon.progression.md with a real hardcoded
        // token, not AmoCRM. True by default everywhere (including
        // production) so this doesn't change prod behavior; set
        // AMOCRM_ENABLED=false locally so test orders don't hit the real
        // external CRM.
        'enabled' => env('AMOCRM_ENABLED', true),
    ],

    'integration' => [
        // Epic 0 / 0.4 — who gets notified when 1С sync exhausts its
        // retries. Empty by default: no address is known yet, and the job
        // logs critically either way, so this degrades safely.
        'alert_email' => env('INTEGRATION_ALERT_EMAIL'),

        // Legacy name from when this also covered Bitrix24 (cancelled by
        // the client 2026-09-11 — see git history for BITRIX_MOCK_MODE/
        // bitrix_responsible_id if that ever needs resurrecting). Kept as
        // the fallback default so an existing env with only
        // INTEGRATION_MOCK_MODE set keeps working unchanged.
        'mock_mode' => env('INTEGRATION_MOCK_MODE', true),
        'onec_mock_mode' => env('ONEC_MOCK_MODE', env('INTEGRATION_MOCK_MODE', true)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/facebook/callback',
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/login/google/callback',
    ],


    'facebook_pixel' => [
        'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
        'facebook_pixel_access_token' => env('FACEBOOK_PIXEL_ACCESS_TOKEN'),
        'facebook_test_event_code' => env('FACEBOOK_TEST_EVENT_CODE')
    ],

    'victoriabank' => [
        // e-Gateway CGI protocol (RSA-2048/SHA-256 P_SIGN). TRTYPE=0
        // (authorize) -> callback -> TRTYPE=21 (capture); TRTYPE=24 is
        // refund/reversal. Test terminal, endpoints and the bank's public
        // key all come from VictoriaBank's own onboarding email + guide.
        'endpoint_url' => env(
            'VICTORIABANK_ENDPOINT_URL',
            env('APP_ENV') === 'production'
                ? 'https://vb059.vb.md/cgi-bin/cgi_link'
                : 'https://ecomt.victoriabank.md/cgi-bin/cgi_link'
        ),

        // Test terminal from VictoriaBank's onboarding email (Solvex Lux SRL / efrumos.md).
        'terminal_id' => env('VICTORIABANK_TERMINAL_ID', '49807132'),
        'merchant_id' => env('VICTORIABANK_MERCHANT_ID', '498000049807132'),

        // Ours — generated locally 2026-09-02, merchant_public.pem sent to
        // the bank per their onboarding email (2026-09-02, real TID 49807132/
        // MID 498000049807132 for Solvex Lux SRL — see efrumos-docs/part1).
        // Outside the web root (storage/app is never publicly served).
        'merchant_private_key_path' => env('VICTORIABANK_MERCHANT_PRIVATE_KEY_PATH', storage_path('app/victoriabank-keys/merchant_private.pem')),
        'merchant_public_key_path' => env('VICTORIABANK_MERCHANT_PUBLIC_KEY_PATH', storage_path('app/victoriabank-keys/merchant_public.pem')),
        'bank_public_key_path' => env('VICTORIABANK_BANK_PUBLIC_KEY_PATH', storage_path('app/victoriabank-keys/bank_public.pem')),

        'currency' => 'MDL',
        'merchant_name' => 'Solvex Lux SRL',
        'merchant_url' => env('APP_URL'),
        'country' => 'md',
        'merch_gmt' => '+2',

        // true — не делать авто-капчур (TRTYPE=21) после успешной
        // авторизации, оставить заказ в статусе «авторизован, не списан».
        // Нужно только для тестового сценария банка №2 (0 -> 24, отмена
        // авторизации без капчура). В обычной работе всегда false.
        'skip_autocapture' => env('VICTORIABANK_SKIP_AUTOCAPTURE', false),
    ],

    'onec' => [
        // SOAP_1C_API_TEST_URL / SOAP_1C_API_LIVE_URL already existed in the
        // production .env (unused until now). Production keeps APP_ENV=production
        // and picks the live endpoint automatically; any other env (local/staging)
        // picks the test one, falling back to the live URL if TEST isn't set.
        'wsdl_url' => env('APP_ENV') === 'production'
            ? env('SOAP_1C_API_LIVE_URL', 'http://agent.solvex.md/svx/ws/ws_ef.1cws?wsdl')
            : env('SOAP_1C_API_TEST_URL', env('SOAP_1C_API_LIVE_URL', 'http://agent.solvex.md/svx/ws/ws_ef.1cws?wsdl')),

        // Отдельный WSDL для заказов/оплат (CreateClientPoint/CreateOrder/
        // CreatePayment) — тот же веб-сервис, которым уже пользуются
        // amoCRM/Bitrix24 (отсюда "ws_amo" в имени), адаптирован письмом от
        // 1С 2026-09-09 под приём заказов напрямую с сайта (isWebSite=true).
        // Живьём проверен только тестовый адрес; продовый (SOAP_1C_ORDER_API_LIVE_URL)
        // нужно подтвердить у 1С — см. efrumos-docs/open-decisions.md п.7.
        'order_wsdl_url' => env('APP_ENV') === 'production'
            ? env('SOAP_1C_ORDER_API_LIVE_URL')
            : env('SOAP_1C_ORDER_API_TEST_URL', 'http://agent.solvex.md/test_db/ws/ws_amo.1cws?wsdl'),

        // Owner (код контрагента для CreateClientPoint) — раньше была одна
        // константа (27416), пока не спросили 1С напрямую. Обновление
        // 2026-09-11: 1С прокомментировал каждый код из письма, потом
        // менеджер прислал более точные формулировки (см.
        // efrumos-docs/1c-ws-amo-qa-2026-09-11.md, раздел 1):
        //   27416 — заказы с адресной доставкой. Подтверждено как наш.
        //   27703 — самовывоз (клиент сам забирает). Подтверждено как наш.
        //   28045 — контрагент Straus.md, с которым не сотрудничаем.
        //   27119 — контрагент Pandashop, к efrumos.md отношения не имеет.
        // Реально наши — только 27416/27703. 27119/28045 в письме 1С — это
        // просто соседние коды из общего справочника контрагентов в их
        // системе (комментарий в XSD-схеме), не варианты для нас; путать их
        // с "какой-то нашей курьерской службой" не стоит — так уже ошиблись
        // один раз (см. ниже).
        //
        // На сайте живьём есть только 2 варианта — "самовывоз" и "доставка";
        // способ "доставка" НЕ различает перевозчика (нет данных, кто везёт
        // — свои силы или сторонняя служба), так что для него всегда 27416.
        //
        // owner_code_courier_service (28045) — НЕ используется по умолчанию.
        // Раньше был сопоставлен с delivery_method IN ('nova_courier',
        // 'nova_terminal') на предположении «это Nova Poshta» — предположение
        // ошибочно (28045 это Straus.md, см. выше), а сама Nova Poshta вообще
        // не в задачах текущего этапа: nova_courier/nova_terminal — дремлющий
        // код из самого первого коммита проекта (опция закомментирована в
        // checkout-form-items.blade.php), не наша разработка. С 2026-09-11
        // ветка закрыта флагом custom.front.nova_poshta_enabled (по
        // умолчанию false) в OrderController — эти значения delivery_method
        // не проходят валидацию, значение ниже используется только если флаг
        // включат явно, когда/если Nova Poshta попадёт в задачи и появится
        // подтверждённый код.
        //
        // Ни одно значение 1С явно не подтвердил как факт для прод-логики
        // (сказали «для теста ок, на проде см. выше» про 27416) — формальное
        // подтверждение нужно спрашивать у заказчика напрямую, 1С сам
        // отправил к «ключевому пользователю».
        'owner_code_delivery' => env('ONEC_OWNER_CODE_DELIVERY', 27416),
        'owner_code_pickup' => env('ONEC_OWNER_CODE_PICKUP', 27703),
        'owner_code_courier_service' => env('ONEC_OWNER_CODE_COURIER_SERVICE', 28045),

        // Логин торгового представителя для CreateOrder.AgentId. Ответ 1С
        // (SK, сентябрь 2026): постоянный AgentId — 24, одинаково для теста и
        // прода. Раньше использовался тестовый 243 из первого письма.
        'agent_id' => env('ONEC_AGENT_ID', '24'),

        // OrderRoute для CreateOrder. Ответ 1С (SK, сентябрь 2026): 174,
        // одинаково для теста и прода; код 107 («WEB» из GetRoutes) 1С сайту
        // не назначала — мы нашли его сами, использовать не нужно.
        'order_route' => env('ONEC_ORDER_ROUTE', 174),

        // Районы, заказы из которых в 1С не передаются: Приднестровье — эту
        // базу 1С там не обслуживает («у них своя база», ответ 1С). На сайте
        // такие заказы принимаются как обычно, в 1С не уходят ни заказ, ни
        // платёж. delivery_states.id: Tighina 2189, Stînga Nistrului 2191,
        // Rîbnița 4259, Tiraspol 4270. Не путать с delivery_states.is_transnistria:
        // флаг стоит и у Rezina/Dubăsari, обычных районов, которые 1С обслуживает.
        'unserved_state_ids' => [2189, 2191, 4259, 4270],

        // Токен для приёма вебхука статуса заказа от 1С
        // (App\Http\Controllers\Integration\OneCOrderStatusWebhookController).
        // Контракт тела — тот, что 1С шлёт в amoCRM (xml_id, status, token…,
        // efrumos-docs/1c-order-status-webhook-spec.md); токен генерируем мы
        // и выдаём 1С — она подставляет его в поле token тела. Принимаем
        // также ?token= и заголовок X-OneC-Webhook-Token.
        // На каждом окружении — свой токен, деплой .env не синкает.
        'status_webhook_token' => env('ONEC_STATUS_WEBHOOK_TOKEN'),
    ],

    // API для приложения лояльности (eFrumos-backend) — efrumos-docs/app-api-contract.md.
    // Сервис-к-сервису: приложение ходит на /api/app/v1/* с Bearer-токеном;
    // пользователя приложения определяет само приложение (X-App-Client-Id).
    // Пустой token — API выключено (503), а не «открыто всем».
    'app_api' => [
        'token' => env('APP_API_TOKEN'),
        // Обратный канал: куда сообщать приложению о смене статуса заказа
        // (POST, Bearer status_webhook_token). Пусто — уведомления не шлём.
        'status_webhook_url' => env('APP_API_STATUS_WEBHOOK_URL'),
        'status_webhook_token' => env('APP_API_STATUS_WEBHOOK_TOKEN'),
    ],

];
