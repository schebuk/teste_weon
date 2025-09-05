<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CurrencyService;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CurrencyService::class, function ($app) {
            return new CurrencyService();
        });
    }

    public function boot()
    {
        //
    }
}