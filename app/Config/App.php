<?php

declare(strict_types=1);

namespace App\Config;

class App
{
    public const NAME = 'Udyam Ventures';
    public const VERSION = '1.0.0';
    public const ENV = 'local';

    // Automatically detect project folder
    public static function basePath(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function baseUrl(): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        return rtrim(dirname($script), '/');
    }
}