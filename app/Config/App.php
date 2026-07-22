<?php

namespace App\Config;

class App
{

    public static function name(): string
    {
        return $_ENV['APP_NAME'];
    }

    public static function url(): string
    {
        return rtrim($_ENV['APP_URL'], '/');
    }

    public static function debug(): bool
    {
        return filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN);
    }

}