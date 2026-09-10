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
        // Epic 0 / 0.4 — who gets notified when 1С/Bitrix24 sync exhausts
        // its retries. Empty by default: no address is known yet, and the
        // job logs critically either way, so this degrades safely.
        'alert_email' => env('INTEGRATION_ALERT_EMAIL'),

        // Legacy combined flag — kept only as the shared fallback default
        // for the two flags below when they aren't set individually. Don't
        // read this directly anywhere new; use onec_mock_mode/bitrix_mock_mode.
        'mock_mode' => env('INTEGRATION_MOCK_MODE', true),

        // Split 2026-09-09: 1С now has a real, working order/payment WSDL
        // (ws_amo.1cws) — SoapOneCOrderGateway can run for real independently
        // of Bitrix24, which still has no webhook/credentials at all. Each
        // flag defaults to the legacy combined one, so an env that only sets
        // INTEGRATION_MOCK_MODE keeps its old all-or-nothing behavior; set
        // these individually to unmock one system without the other.
        'onec_mock_mode' => env('ONEC_MOCK_MODE', env('INTEGRATION_MOCK_MODE', true)),
        'bitrix_mock_mode' => env('BITRIX_MOCK_MODE', env('INTEGRATION_MOCK_MODE', true)),

        // Epic 1 / 1.5 — Bitrix24 employee who gets the post-payment task
        // (tasks.task.add RESPONSIBLE_ID). Not known yet — null until the
        // client says who; LoggingBitrixDealGateway logs a clear warning
        // and skips the task rather than guessing an id.
        'bitrix_responsible_id' => env('BITRIX24_RESPONSIBLE_ID'),
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

        // Owner (код контрагента для CreateClientPoint) — письмо 1С даёт 4
        // варианта: 27416 (Client B2C), 27703 (Client B2C, fil.
        // V.Alecsandri), 27119 (Eliteh Trade SRL), 28045 (Client B2C
        // STRAUS.md — похоже на счёт другого сайта той же компании). Взят
        // 27416 как наиболее вероятный (общий B2C, без привязки к филиалу
        // или другому сайту) — не подтверждено клиентом/1С явно, это
        // обоснованное предположение, не гарантированный факт. Проверить и
        // при необходимости поправить через ONEC_OWNER_CODE без правки кода.
        'owner_code' => env('ONEC_OWNER_CODE', 27416),

        // Логин торгового представителя для CreateOrder.AgentId. Письмо 1С
        // само говорит, что постоянного пока нет: «скорее всего понадобится
        // создать пользователя типа „Сайт“... для теста можно использовать
        // 243». Используем тестовое значение, пока не заведут постоянное.
        'agent_id' => env('ONEC_AGENT_ID', '243'),

        // OrderRoute для CreateOrder — живьём проверенный GetRoutes() отдаёт
        // код 107 с описанием ровно "WEB" (плюс 113 "WEB-Partner") — сильное
        // совпадение по названию для заказов с сайта, но 1С явно этот код в
        // письме не называл, так что это тоже предположение, не факт.
        'order_route' => env('ONEC_ORDER_ROUTE', 107),
    ],

];
