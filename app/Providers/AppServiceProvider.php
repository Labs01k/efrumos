<?php

namespace App\Providers;

use App\Contracts\Integration\BitrixDealGateway;
use App\Contracts\Integration\OneCOrderGateway;
use App\Contracts\OrderIntegrationNotifier;
use App\Services\Integration\Bitrix24\LoggingBitrixDealGateway;
use App\Services\Integration\OneC\SoapOneCOrderGateway;
use App\Services\Integration\OneCBitrixOrderIntegrationNotifier;
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

        // INTEGRATION_MOCK_MODE (services.integration.mock_mode, default
        // true) — the single flag both of these gateways read internally
        // (see their own mock_mode branches) to decide whether to mock
        // every 1С/Bitrix24 call (stock always "enough", writes always
        // "succeed") or behave as honestly as currently possible: real stock
        // check, and a clear IntegrationGatewayException on every write,
        // since neither system has a real write endpoint/credentials yet.
        // The binding itself doesn't change — there is no real
        // implementation to swap in yet, only these two classes' internal
        // behavior changes with the flag.
        $this->app->bind(OneCOrderGateway::class, SoapOneCOrderGateway::class);
        $this->app->bind(BitrixDealGateway::class, LoggingBitrixDealGateway::class);

        // Epic 1 / 1.3 — real notifier: forwards the payment status onto the
        // 1С document + Bitrix24 deal (markPaid/updateDealStatus), through
        // the same gateways bound above.
        $this->app->bind(OrderIntegrationNotifier::class, OneCBitrixOrderIntegrationNotifier::class);
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
