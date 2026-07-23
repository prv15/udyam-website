<?php

declare(strict_types=1);

namespace App\Core;

class App
{
    protected static ?Container $container = null;

    public static function container(): Container
    {
        if (self::$container === null) {
            self::$container = new Container();
        }

        return self::$container;
    }
}