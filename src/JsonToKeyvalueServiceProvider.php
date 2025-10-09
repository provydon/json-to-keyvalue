<?php

namespace Provydon\JsonToKeyvalue;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class JsonToKeyvalueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/json-to-keyvalue.php' => config_path('json-to-keyvalue.php'),
        ], 'json-to-keyvalue-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'json-to-keyvalue');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/json-to-keyvalue'),
        ], 'json-to-keyvalue-views');

        Blade::component('json-to-keyvalue::keyvalue', 'keyvalue-display');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/json-to-keyvalue.php', 'json-to-keyvalue'
        );
    }
}
