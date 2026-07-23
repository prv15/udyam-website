<?php

declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {

    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }

}