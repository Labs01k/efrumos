<?php

namespace App\Providers;

use App\Contracts\Integration\OneCOrderGateway;
use App\Contracts\OrderIntegrationNotifier;
use App\Services\Integration\OneC\SoapOneCOrderGateway;
use App\Services\Integration\OneCOrderIntegrationNotifier;
use App\Services\Payment\Victoriabank\VictoriaBankClient;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(VictoriaBankClient::class, fn () => VictoriaBankClient::fromConfig());

        // ONEC_MOCK_MODE (services.integration.onec_mock_mode) — the flag
        // SoapOneCOrderGateway reads internally to decide whether to mock
        // every 1С call (stock always "enough", writes always "succeed") or
        // behave as honestly as currently possible: real stock check, and a
        // clear IntegrationGatewayException on every write, since 1С's
        // CreatePayment doesn't work yet (see tasks-status.md). The binding
        // itself doesn't change — there is no real implementation to swap in
        // yet, only this class's internal behavior changes with the flag.
        $this->app->bind(OneCOrderGateway::class, SoapOneCOrderGateway::class);

        // Epic 1 / 1.3 — real notifier: forwards the payment status onto the
        // 1С document (markPaid), through the gateway bound above. Used to
        // also push a Bitrix24 deal status update — dropped 2026-09-11,
        // Bitrix24 integration cancelled by the client.
        $this->app->bind(OrderIntegrationNotifier::class, OneCOrderIntegrationNotifier::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('global_user', function(){
            return getAuthorizedUser();
        });

    }
}
