<?php

use App\Core\App;

if (!function_exists('app')) {

    function app(string $class): object
    {
        return App::container()->get($class);
    }
}