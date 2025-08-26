<?php

namespace Webewox\Hyperpay;

use Illuminate\Support\ServiceProvider;

class CustomPackageServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('hyperpay', function()
        {
            return new Hyperpay();
        });
        $this->app->bind('stcpay', function()
        {
            return new Stcpay();
        });
        $this->app->bind('applepay', function()
        {
            return new Applepay();
        });
        $this->app->bind('madapay', function()
        {
            return new Madapay();
        });
    }
}