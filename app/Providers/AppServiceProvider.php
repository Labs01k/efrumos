<?php

namespace App\Providers;

use App\Contracts\Integration\OneCOrderGateway;
use App\Services\Integration\OneC\SoapOneCOrderGateway;
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

        // checkStock() is real (same GetSKUArray SOAP operation the catalog
        // sync already uses); reserveOrder()/releaseReservation()/markPaid()
        // stay logging-only because the WSDL has no such operation.
        $this->app->bind(OneCOrderGateway::class, SoapOneCOrderGateway::class);
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
