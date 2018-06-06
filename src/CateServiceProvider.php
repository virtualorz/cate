<?php

namespace Virtualorz\Cate;

use Illuminate\Support\ServiceProvider;

class CateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('cate', function () {
            return new Cate();
        });
    }
}
