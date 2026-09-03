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

        // TEMPORARY — single flag covering every 1С/Bitrix24 call made
        // during order processing (SoapOneCOrderGateway, LoggingBitrixDeal-
        // Gateway). true (default everywhere, including production): stock
        // check always reports "enough", every write always "succeeds" —
        // the whole integration chain reaches synced regardless of real
        // data. false: real stock check, every write throws
        // IntegrationGatewayException — as honest as currently possible,
        // since neither system has a real write endpoint/credentials yet.
        'mock_mode' => env('INTEGRATION_MOCK_MODE', true),

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

        // Ours — generated locally, not yet re-sent/confirmed after rotation.
        // Outside the web root (storage/app is never publicly served).
        'merchant_private_key_path' => env('VICTORIABANK_MERCHANT_PRIVATE_KEY_PATH', storage_path('app/victoriabank-keys/merchant_private.pem')),
        'merchant_public_key_path' => env('VICTORIABANK_MERCHANT_PUBLIC_KEY_PATH', storage_path('app/victoriabank-keys/merchant_public.pem')),
        'bank_public_key_path' => env('VICTORIABANK_BANK_PUBLIC_KEY_PATH', storage_path('app/victoriabank-keys/bank_public.pem')),

        'currency' => 'MDL',
        'merchant_name' => 'Solvex Lux SRL',
        'merchant_url' => env('APP_URL'),
        'country' => 'md',
        'merch_gmt' => '+2',
    ],

    'onec' => [
        // SOAP_1C_API_TEST_URL / SOAP_1C_API_LIVE_URL already existed in the
        // production .env (unused until now). Production keeps APP_ENV=production
        // and picks the live endpoint automatically; any other env (local/staging)
        // picks the test one, falling back to the live URL if TEST isn't set.
        'wsdl_url' => env('APP_ENV') === 'production'
            ? env('SOAP_1C_API_LIVE_URL', 'http://agent.solvex.md/svx/ws/ws_ef.1cws?wsdl')
            : env('SOAP_1C_API_TEST_URL', env('SOAP_1C_API_LIVE_URL', 'http://agent.solvex.md/svx/ws/ws_ef.1cws?wsdl')),
    ],

];
