<?php

namespace App\Config;

class App
{
    public const NAME = 'Udyam CMS';

    public static function url(): string
    {
        return rtrim($_ENV['APP_URL'] ?? '', '/');
    }

    public static function basePath(): string
    {
        return rtrim($_ENV['APP_BASE_PATH'] ?? '', '/');
    }

    public static function timezone(): string
    {
        return $_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata';
    }

    public static function debug(): bool
    {
        return filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
    }
}