<?php

namespace Provydon\JsonToKeyvalue;

use Illuminate\Support\ServiceProvider;

class JsonToKeyvalueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // publish config or assets later if needed
    }

    public function register()
    {
        // require helpers so global helper is available
        require_once __DIR__ . '/Helpers/helpers.php';
    }
}
